<!doctype html>
<html lang="ru">
<body style="margin:0;padding:24px 12px;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#152242;">
    <div style="max-width:520px;margin:0 auto;">
        <div style="background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e8edf3;">
            <div style="padding:22px 24px 6px;">
                @if ($approved)
                    <div style="font-size:13px;color:#16a34a;font-weight:bold;">✅ Объявление опубликовано</div>
                @elseif ($needsChanges)
                    <div style="font-size:13px;color:#d97706;font-weight:bold;">✏️ Объявление нужно доработать</div>
                @else
                    <div style="font-size:13px;color:#dc2626;font-weight:bold;">⚠️ Объявление не прошло проверку</div>
                @endif
                <p style="margin:10px 0 0;font-size:15px;color:#475569;line-height:1.5;">
                    {{ $recipientName ? 'Здравствуйте, ' . $recipientName . '!' : 'Здравствуйте!' }}
                    @if ($approved)
                        Модератор проверил ваше объявление — теперь его видят все в поиске.
                    @elseif ($needsChanges)
                        Модератор просит кое-что поправить в объявлении. Исправьте и сохраните — оно снова уйдёт на проверку.
                    @else
                        Модератор вернул ваше объявление. Исправьте его и сохраните — оно снова уйдёт на проверку.
                    @endif
                </p>
            </div>

            <div style="margin:16px 24px;border:1px solid #e8edf3;border-radius:12px;padding:14px 16px;">
                <div style="font-size:16px;font-weight:bold;color:#0468ff;">{{ $title }}</div>
                <div style="font-size:15px;font-weight:bold;color:#152242;margin-top:4px;">{{ $price }}</div>
                @if (!$approved && $reason)
                    <div style="margin-top:12px;padding:10px 12px;background:#fef2f2;border-radius:8px;font-size:14px;color:#991b1b;line-height:1.5;">
                        <b>{{ $needsChanges ? 'Что поправить:' : 'Причина:' }}</b> {{ $reason }}
                    </div>
                @endif
            </div>

            <div style="padding:4px 24px 24px;text-align:center;">
                <a href="{{ $openUrl }}" style="display:inline-block;background:#0468ff;color:#ffffff;text-decoration:none;font-weight:bold;font-size:15px;padding:12px 26px;border-radius:10px;">
                    {{ $approved ? 'Мои объявления' : 'Исправить объявление' }}
                </a>
            </div>
        </div>
    </div>
</body>
</html>
