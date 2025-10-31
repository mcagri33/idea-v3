<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subjectText }}</title>
</head>
<body>
    <h2>Merhaba {{ $user->name }},</h2>
    
    <p>{!! nl2br(e($messageText)) !!}</p>
    
    <hr>
    
    <p><small>{{ config('app.name') }} - Otomatik Mail Sistemi</small></p>
</body>
</html>