<!doctype html>
<html lang="ru">
<body style="margin:0;padding:24px 12px;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#152242;">
    <div style="max-width:520px;margin:0 auto;">
        <div style="background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e8edf3;">
            <div style="padding:22px 24px 6px;">
                <div style="font-size:13px;color:#ea580c;font-weight:bold;">🔔 Новое объявление по вашему поиску</div>
                <p style="margin:10px 0 0;font-size:15px;color:#475569;line-height:1.5;">
                    {{ $recipientName ? 'Здравствуйте, ' . $recipientName . '!' : 'Здравствуйте!' }}
                    Появилось объявление, которое подходит под ваш сохранённый поиск
                    <b style="color:#152242;">«{{ $searchLabel }}»</b>.
                </p>
            </div>

            <div style="margin:16px 24px;border:1px solid #e8edf3;border-radius:12px;overflow:hidden;">
                @if ($imageUrl)
                    <img src="{{ $imageUrl }}" alt="" width="470" style="display:block;width:100%;max-height:260px;object-fit:cover;border:0;">
                @endif
                <div style="padding:14px 16px;">
                    <div style="font-size:22px;font-weight:bold;color:#152242;">{{ $price }}</div>
                    <div style="font-size:16px;font-weight:bold;color:#0468ff;margin-top:6px;">{{ $title }}</div>
                    @if ($facts)
                        <div style="font-size:14px;color:#64748b;margin-top:4px;">{{ $facts }}</div>
                    @endif
                    @if ($description)
                        <div style="font-size:14px;color:#475569;margin-top:10px;line-height:1.5;">{{ $description }}</div>
                    @endif
                </div>
            </div>

            <div style="padding:4px 24px 24px;text-align:center;">
                <a href="{{ $openUrl }}" style="display:inline-block;background:#0468ff;color:#ffffff;text-decoration:none;font-weight:bold;font-size:15px;padding:12px 26px;border-radius:10px;">
                    Посмотреть объявление
                </a>
            </div>
        </div>

        <p style="font-size:12px;color:#94a3b8;text-align:center;line-height:1.5;margin:16px 8px 0;">
            Вы получили это письмо, потому что нажали «Уведомить меня» на сайте.<br>
            <a href="{{ $manageUrl }}" style="color:#64748b;">Управлять подписками или отписаться</a>
        </p>
    </div>
</body>
</html>
