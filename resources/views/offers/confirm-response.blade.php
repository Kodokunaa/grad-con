<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Confirm offer response · GradConn</title>
    <style>body{font:16px system-ui;background:#111827;color:#f8fafc;display:grid;place-items:center;min-height:95vh}main{background:#1f2937;padding:32px;border-radius:16px;max-width:440px;box-shadow:0 20px 60px #0006}button{background:#ea580c;color:white;border:0;border-radius:8px;padding:12px 22px;cursor:pointer}a{margin-left:16px;color:#cbd5e1}</style>
</head>
<body>
<main>
    <h1>{{ ucfirst($action) }} this offer?</h1>
    <p>This will record your response and notify the employer.</p>
    <form method="POST" action="{{ $updateUrl }}">
        @csrf
        @method('PATCH')
        <button type="submit">Confirm {{ $action }}</button>
        <a href="{{ route('alumni.job_offers') }}">Cancel</a>
    </form>
</main>
</body>
</html>
