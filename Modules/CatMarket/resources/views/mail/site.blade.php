<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:24px;background:#f6f6f4;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#1d1d1f">
<div style="max-width:520px;margin:0 auto;background:#fff;border-radius:12px;padding:28px">
    <p style="margin:0 0 16px;font-weight:700;font-size:15px">{{ $site }}</p>
    <h1 style="margin:0 0 12px;font-size:20px;line-height:1.3">{{ $heading }}</h1>
    @foreach($lines as $line)
        @if($line === '')
            <div style="height:8px"></div>
        @else
            <p style="margin:0 0 10px;font-size:15px;line-height:1.5;color:#3a3a3c">{{ $line }}</p>
        @endif
    @endforeach
    @if($buttonUrl)
        <p style="margin:18px 0 24px"><a href="{{ $buttonUrl }}" style="display:inline-block;background:#1d1d1f;color:#fff;text-decoration:none;padding:12px 20px;border-radius:999px;font-size:15px">{{ $buttonLabel ?: 'Open '.$site }}</a></p>
    @endif
    @if($footer)
        <p style="margin:0;font-size:12px;color:#8e8e93">{{ $footer }}</p>
    @endif
</div>
</body>
</html>
