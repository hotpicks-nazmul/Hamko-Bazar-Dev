<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database · Install Hamko Bazar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif']}}}}</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{background:#0a0b0d;font-family:'Inter',system-ui,sans-serif}.anim-up{animation:up .45s ease-out}@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}.step-dot{width:8px;height:8px;border-radius:50%;background:#2a2c32;flex-shrink:0}.step-dot.active{background:#6366f1;box-shadow:0 0 10px rgba(99,102,241,.4)}.step-dot.done{background:#4b5563}.step-line{width:22px;height:1px;background:#2a2c32;flex-shrink:0}.step-line.fill{background:#4b5563}input:focus{border-color:#6366f1!important;background:#1a1c24!important;box-shadow:0 0 0 3px rgba(99,102,241,.12)!important}</style>
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:#0a0b0d;font-family:'Inter',system-ui,sans-serif">
<div style="width:100%;max-width:600px;background:#111215;border:1px solid #22242a;border-radius:20px;box-shadow:0 25px 50px -12px rgba(0,0,0,.5);animation:up .45s ease-out">
    <div style="text-align:center;padding:48px 40px 0">
        <h1 style="color:#f0f1f3;font-size:22px;font-weight:600;margin-bottom:4px">Database</h1>
        <p style="color:#9ca3af;font-size:16px">Enter MySQL credentials</p>
    </div>
    <div style="padding:28px 40px 40px">
        <div style="display:flex;align-items:center;justify-content:center;gap:4px;margin-bottom:20px">
            <div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot active"></div><div class="step-line"></div><div class="step-dot"></div><div class="step-line"></div><div class="step-dot"></div><div class="step-line"></div><div class="step-dot"></div>
        </div>
        <p style="text-align:center;font-size:15px;color:#9ca3af;margin-bottom:24px">Step <strong style="color:#e5e7eb">3</strong> of <strong style="color:#e5e7eb">6</strong> · Database</p>
        @if(session('error'))<div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);color:#fca5a5;border-radius:12px;padding:12px 16px;font-size:14px;margin-bottom:16px">{{ session('error') }}</div>@endif
        @if(session('success'))<div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.15);color:#6ee7b7;border-radius:12px;padding:12px 16px;font-size:14px;margin-bottom:16px">{{ session('success') }}</div>@endif
        <div style="display:flex;gap:12px;background:rgba(99,102,241,.05);border:1px solid rgba(99,102,241,.1);border-radius:12px;padding:12px 16px;margin-bottom:20px;font-size:14px;color:#a5b4fc;line-height:1.5"><span style="color:#818cf8;flex-shrink:0">ⓘ</span><span>Your database will be created automatically if it doesn't exist.</span></div>
        <form action="{{ route('install.setup-database') }}" method="POST">
            @csrf
            <div style="display:flex;gap:12px">
                <div style="flex:1;margin-bottom:16px">
                    <label style="display:block;color:#9ca3af;font-size:14px;font-weight:500;margin-bottom:6px">Host</label>
                    <div style="position:relative"><span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;font-size:14px;pointer-events:none">@</span><input type="text" name="db_host" value="{{ old('db_host', 'localhost') }}" required style="width:100%;padding:12px 14px 12px 38px;background:#181a1f;border:1px solid #22242a;border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;color:#e5e7eb;outline:none"></div>
                </div>
                <div style="width:100px;margin-bottom:16px">
                    <label style="display:block;color:#9ca3af;font-size:14px;font-weight:500;margin-bottom:6px">Port</label>
                    <div style="position:relative"><span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;font-size:14px;pointer-events:none">#</span><input type="number" name="db_port" value="{{ old('db_port', '3306') }}" required style="width:100%;padding:12px 14px 12px 38px;background:#181a1f;border:1px solid #22242a;border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;color:#e5e7eb;outline:none"></div>
                </div>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;color:#9ca3af;font-size:14px;font-weight:500;margin-bottom:6px">Database Name</label>
                <div style="position:relative"><span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;font-size:14px;pointer-events:none">◇</span><input type="text" name="db_name" value="{{ old('db_name') }}" required placeholder="e.g. hamko_bazar" style="width:100%;padding:12px 14px 12px 38px;background:#181a1f;border:1px solid #22242a;border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;color:#e5e7eb;outline:none"></div>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;color:#9ca3af;font-size:14px;font-weight:500;margin-bottom:6px">Username</label>
                <div style="position:relative"><span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;font-size:14px;pointer-events:none">◉</span><input type="text" name="db_username" value="{{ old('db_username') }}" required placeholder="e.g. hamko_user" style="width:100%;padding:12px 14px 12px 38px;background:#181a1f;border:1px solid #22242a;border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;color:#e5e7eb;outline:none"></div>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;color:#9ca3af;font-size:14px;font-weight:500;margin-bottom:6px">Password</label>
                <div style="position:relative"><span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;font-size:14px;pointer-events:none">●</span><input type="password" name="db_password" value="{{ old('db_password') }}" placeholder="Enter password" style="width:100%;padding:12px 14px 12px 38px;background:#181a1f;border:1px solid #22242a;border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;color:#e5e7eb;outline:none"></div>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:24px;padding-top:20px;border-top:1px solid #22242a">
                <a href="{{ route('install.requirements') }}" style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#1e2025;color:#9ca3af;border:1px solid #22242a;border-radius:12px;text-decoration:none;font-size:14px;font-weight:500">← Back</a>
                <button type="submit" style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#6366f1;color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:500;cursor:pointer;box-shadow:0 4px 16px rgba(99,102,241,.3)">Test & Connect →</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>