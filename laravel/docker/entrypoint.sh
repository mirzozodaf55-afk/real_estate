#!/bin/sh
# Запуск контейнера API на Render: сертификат базы, миграции (или сброс демо), планировщик, Apache.
set -e
cd /var/www/html

# Aiven пускает к MySQL только по TLS: текст сертификата (CA certificate) лежит в переменной DB_CA_CERT
if [ -n "$DB_CA_CERT" ]; then
    printf '%s\n' "$DB_CA_CERT" > /var/www/html/storage/db-ca.pem
    export MYSQL_ATTR_SSL_CA=/var/www/html/storage/db-ca.pem
fi

# artisan — от www-data, иначе файлы (фото, кеш) окажутся root-овыми и Apache не сможет их менять
artisan() {
    runuser -u www-data -- php artisan "$@"
}

# база может проснуться не сразу — несколько попыток (migrate:status не годится: на пустой базе он тоже ошибка)
db_ping() {
    runuser -u www-data -- php -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php";
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); Illuminate\Support\Facades\DB::select("select 1");'
}
tries=0
until db_ping > /dev/null 2>&1; do
    tries=$((tries + 1))
    if [ "$tries" -ge 10 ]; then
        echo "Нет связи с базой данных — проверьте DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD и DB_CA_CERT" >&2
        db_ping
        exit 1
    fi
    sleep 3
done

if [ "$DEMO_MODE" = "true" ]; then
    # на бесплатном Render нет постоянного диска: загруженные файлы пропадают при каждом перезапуске,
    # поэтому и база каждый раз возвращается к демо-данным — фото и записи о них не расходятся
    artisan demo:reset --clean-files
else
    artisan migrate --force
fi

# планировщик: ночной сброс демо, очистка просроченных токенов
runuser -u www-data -- php artisan schedule:work > /dev/null 2>&1 &

exec apache2-foreground
