<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requirements · Install Hamko Bazar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif']}}}}</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body{background:#0a0b0d;font-family:'Inter',system-ui,sans-serif}
        .anim-up{animation:up .45s ease-out}
        @keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
        .step-dot{width:8px;height:8px;border-radius:50%;background:#2a2c32;flex-shrink:0}
        .step-dot.active{background:#6366f1;box-shadow:0 0 10px rgba(99,102,241,.4)}
        .step-dot.done{background:#4b5563}
        .step-line{width:22px;height:1px;background:#2a2c32;flex-shrink:0}
        .step-line.fill{background:#4b5563}
    </style>
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:#0a0b0d;font-family:'Inter',system-ui,sans-serif">
@php $all=true;foreach($requirements['extensions'] as $e)if(!$e)$all=false;foreach($requirements['permissions'] as $p)if(!$p)$all=false;if(!$requirements['php']['status'])$all=false; @endphp
<div style="width:100%;max-width:720px;background:#111215;border:1px solid #22242a;border-radius:20px;box-shadow:0 25px 50px -12px rgba(0,0,0,.5);animation:up .45s ease-out">
    <div style="text-align:center;padding:48px 48px 0">
        <h1 style="color:#f0f1f3;font-size:22px;font-weight:600;margin-bottom:4px">Server Requirements</h1>
        <p style="color:#9ca3af;font-size:16px">Checking your server configuration</p>
    </div>
    <div style="padding:28px 48px 40px">
        <div style="display:flex;align-items:center;justify-content:center;gap:4px;margin-bottom:20px">
            <div class="step-dot done"></div><div class="step-line fill"></div>
            <div class="step-dot active"></div><div class="step-line"></div>
            <div class="step-dot"></div><div class="step-line"></div>
            <div class="step-dot"></div><div class="step-line"></div>
            <div class="step-dot"></div><div class="step-line"></div>
            <div class="step-dot"></div>
        </div>
        <p style="text-align:center;font-size:15px;color:#9ca3af;margin-bottom:24px">Step <strong style="color:#e5e7eb">2</strong> of <strong style="color:#e5e7eb">6</strong> · Requirements</p>

        <div style="background:#181a1f;border:1px solid #22242a;border-radius:12px;padding:16px;margin-bottom:12px">
            <h4 style="color:#9ca3af;font-size:14px;font-weight:600;margin-bottom:8px">PHP Version</h4>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.03)">
                <span style="color:#d1d5db;font-size:14px">PHP Version <span style="color:#4b5563;font-weight:400;margin-left:4px">(Req: {{ $requirements['php']['version'] }}+)</span></span>
                @if($requirements['php']['status'])<span style="background:rgba(16,185,129,.1);color:#6ee7b7;font-size:12px;font-weight:500;padding:2px 10px;border-radius:20px">{{ $requirements['php']['current'] }}</span>@else<span style="background:rgba(239,68,68,.1);color:#fca5a5;font-size:12px;font-weight:500;padding:2px 10px;border-radius:20px">{{ $requirements['php']['current'] }}</span>@endif
            </div>
        </div>

        <div style="background:#181a1f;border:1px solid #22242a;border-radius:12px;padding:16px;margin-bottom:12px">
            <h4 style="color:#9ca3af;font-size:14px;font-weight:600;margin-bottom:8px">PHP Extensions</h4>
            @foreach($requirements['extensions'] as $ext=>$s)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.03);last:border-0">
                <span style="color:#d1d5db;font-size:14px">{{ strtoupper($ext) }}</span>
                @if($s)<span style="background:rgba(16,185,129,.1);color:#6ee7b7;font-size:12px;font-weight:500;padding:2px 10px;border-radius:20px">Enabled</span>@else<span style="background:rgba(239,68,68,.1);color:#fca5a5;font-size:12px;font-weight:500;padding:2px 10px;border-radius:20px">Missing</span>@endif
            </div>
            @endforeach
        </div>

        <div style="background:#181a1f;border:1px solid #22242a;border-radius:12px;padding:16px;margin-bottom:12px">
            <h4 style="color:#9ca3af;font-size:14px;font-weight:600;margin-bottom:8px">File Permissions</h4>
            @foreach($requirements['permissions'] as $path=>$w)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.03)">
                <span style="color:#d1d5db;font-size:14px">{{ $path }}</span>
                @if($w)<span style="background:rgba(16,185,129,.1);color:#6ee7b7;font-size:12px;font-weight:500;padding:2px 10px;border-radius:20px">Writable</span>@else<span style="background:rgba(239,68,68,.1);color:#fca5a5;font-size:12px;font-weight:500;padding:2px 10px;border-radius:20px">Not Writable</span>@endif
            </div>
            @endforeach
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:24px;padding-top:20px;border-top:1px solid #22242a">
            <a href="{{ route('install.welcome') }}" style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#1e2025;color:#9ca3af;border:1px solid #22242a;border-radius:12px;text-decoration:none;font-size:14px;font-weight:500">← Back</a>
            @if($all)<a href="{{ route('install.database') }}" style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#6366f1;color:#fff;border-radius:12px;text-decoration:none;font-size:14px;font-weight:500;box-shadow:0 4px 16px rgba(99,102,241,.3)">Continue →</a>@else<button style="padding:12px 28px;background:rgba(99,102,241,.5);color:rgba(255,255,255,.5);border:none;border-radius:12px;font-size:14px;font-weight:500;cursor:not-allowed">Fix Issues First</button>@endif
        </div>
    </div>
</div>
</body>
</html>