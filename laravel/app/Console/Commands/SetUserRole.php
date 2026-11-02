<?php

namespace App\Console\Commands;

use App\Models\Owner;
use Illuminate\Console\Command;

/** Первого админа назначить через сайт нельзя — для этого команда: php artisan user:role you@gmail.com admin */
class SetUserRole extends Command
{
    protected $signature = 'user:role {email : Почта пользователя} {role : user, moderator или admin}';

    protected $description = 'Назначить пользователю роль';

    public function handle(): int
    {
        $role = $this->argument('role');
        if (!in_array($role, Owner::ROLES, true)) {
            $this->error('Роль должна быть одной из: ' . implode(', ', Owner::ROLES));
            return self::FAILURE;
        }

        $user = Owner::where('email', $this->argument('email'))->first();
        if (!$user) {
            $this->error('Пользователь с такой почтой не найден. Сначала войдите на сайт с этой почтой.');
            return self::FAILURE;
        }

        $user->forceFill(['role' => $role])->save();
        $this->info("{$user->email}: роль «{$role}»");
        return self::SUCCESS;
    }
}
