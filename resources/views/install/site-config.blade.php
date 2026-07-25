<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configure · Install Hamko Bazar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif']}}}}</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{background:#0a0b0d;font-family:'Inter',system-ui,sans-serif}.anim-up{animation:up .45s ease-out}@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}.step-dot{width:8px;height:8px;border-radius:50%;background:#2a2c32;flex-shrink:0}.step-dot.active{background:#6366f1;box-shadow:0 0 10px rgba(99,102,241,.4)}.step-dot.done{background:#4b5563}.step-line{width:22px;height:1px;background:#2a2c32;flex-shrink:0}.step-line.fill{background:#4b5563}input:focus,select:focus{border-color:#6366f1!important;background:#1a1c24!important;box-shadow:0 0 0 3px rgba(99,102,241,.12)!important}</style>
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:#0a0b0d;font-family:'Inter',system-ui,sans-serif">
<div style="width:100%;max-width:600px;background:#111215;border:1px solid #22242a;border-radius:20px;box-shadow:0 25px 50px -12px rgba(0,0,0,.5);animation:up .45s ease-out">
    <div style="text-align:center;padding:48px 40px 0">
        <h1 style="color:#f0f1f3;font-size:22px;font-weight:600;margin-bottom:4px">Site Configuration</h1>
        <p style="color:#9ca3af;font-size:16px">Set up your website and super admin account</p>
    </div>
    <div style="padding:28px 40px 40px">
        <div style="display:flex;align-items:center;justify-content:center;gap:4px;margin-bottom:20px">
            <div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot active"></div><div class="step-line"></div><div class="step-dot"></div><div class="step-line"></div><div class="step-dot"></div>
        </div>
        <p style="text-align:center;font-size:15px;color:#9ca3af;margin-bottom:24px">Step <strong style="color:#e5e7eb">4</strong> of <strong style="color:#e5e7eb">6</strong> · Configure</p>
        @if(session('error'))<div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);color:#fca5a5;border-radius:12px;padding:12px 16px;font-size:14px;margin-bottom:16px">{{ session('error') }}</div>@endif
        <form action="{{ route('install.save-site-config') }}" method="POST">
            @csrf
            <div style="color:#6366f1;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #22242a">Site Information</div>
            <div style="margin-bottom:16px">
                <label style="display:block;color:#9ca3af;font-size:14px;font-weight:500;margin-bottom:6px">Site Name</label>
                <div style="position:relative"><span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;font-size:14px;pointer-events:none">◉</span><input type="text" name="site_name" value="{{ old('site_name') }}" required placeholder="Hamko Bazar" style="width:100%;padding:12px 14px 12px 38px;background:#181a1f;border:1px solid #22242a;border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;color:#e5e7eb;outline:none"></div>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;color:#9ca3af;font-size:14px;font-weight:500;margin-bottom:6px">Site URL</label>
                <div style="position:relative"><span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;font-size:14px;pointer-events:none">↗</span><input type="url" name="site_url" value="{{ old('site_url', request()->getSchemeAndHttpHost()) }}" required style="width:100%;padding:12px 14px 12px 38px;background:#181a1f;border:1px solid #22242a;border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;color:#e5e7eb;outline:none"></div>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;color:#9ca3af;font-size:14px;font-weight:500;margin-bottom:6px">Timezone</label>
                <div style="position:relative"><span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;font-size:14px;pointer-events:none">◷</span>
                    <select name="timezone" required style="width:100%;padding:12px 14px 12px 38px;background:#181a1f;border:1px solid #22242a;border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;color:#e5e7eb;outline:none;appearance:none;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2210%22 height=%226%22%3E%3Cpath fill=%22%236b7280%22 d=%22M0 0l5 6 5-6z%22/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 12px center">
                        <option value="Asia/Dhaka" {{ old('timezone')=='Asia/Dhaka'?'selected':'' }}>Asia/Dhaka (GMT+6)</option>
                        <option value="UTC" {{ old('timezone')=='UTC'?'selected':'' }}>UTC</option>
                        <option value="Asia/Kolkata" {{ old('timezone')=='Asia/Kolkata'?'selected':'' }}>Asia/Kolkata</option>
                        <option value="Asia/Dubai" {{ old('timezone')=='Asia/Dubai'?'selected':'' }}>Asia/Dubai</option>
                    </select>
                </div>
            </div>
            <div style="color:#6366f1;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #22242a;margin-top:24px">Super Admin Account</div>
            <p style="color:#6b7280;font-size:13px;margin-bottom:16px;line-height:1.4">Login at <code style="background:#1a1c24;color:#9ca3af;padding:2px 8px;border-radius:6px;font-size:12px">/super-admin/login</code> after installation</p>
            <div style="display:flex;gap:12px">
                <div style="flex:1;margin-bottom:16px">
                    <label style="display:block;color:#9ca3af;font-size:14px;font-weight:500;margin-bottom:6px">Name</label>
                    <div style="position:relative"><span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;font-size:14px;pointer-events:none">◉</span><input type="text" name="admin_name" value="{{ old('admin_name') }}" required placeholder="Name" style="width:100%;padding:12px 14px 12px 38px;background:#181a1f;border:1px solid #22242a;border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;color:#e5e7eb;outline:none"></div>
                </div>
                <div style="flex:1;margin-bottom:16px">
                    <label style="display:block;color:#9ca3af;font-size:14px;font-weight:500;margin-bottom:6px">Email</label>
                    <div style="position:relative"><span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;font-size:14px;pointer-events:none">@</span><input type="email" name="admin_email" value="{{ old('admin_email') }}" required placeholder="admin@example.com" style="width:100%;padding:12px 14px 12px 38px;background:#181a1f;border:1px solid #22242a;border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;color:#e5e7eb;outline:none"></div>
                </div>
            </div>
            <div style="display:flex;gap:12px">
                <div style="flex:1;margin-bottom:16px">
                    <label style="display:block;color:#9ca3af;font-size:14px;font-weight:500;margin-bottom:6px">Password</label>
                    <div style="position:relative"><span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;font-size:14px;pointer-events:none">●</span><input type="password" name="admin_password" required placeholder="Min 8 chars" style="width:100%;padding:12px 14px 12px 38px;background:#181a1f;border:1px solid #22242a;border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;color:#e5e7eb;outline:none"></div>
                </div>
                <div style="flex:1;margin-bottom:16px">
                    <label style="display:block;color:#9ca3af;font-size:14px;font-weight:500;margin-bottom:6px">Confirm</label>
                    <div style="position:relative"><span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;font-size:14px;pointer-events:none">●</span><input type="password" name="admin_password_confirmation" required placeholder="Repeat" style="width:100%;padding:12px 14px 12px 38px;background:#181a1f;border:1px solid #22242a;border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;color:#e5e7eb;outline:none"></div>
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:24px;padding-top:20px;border-top:1px solid #22242a">
                <a href="{{ route('install.database') }}" style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#1e2025;color:#9ca3af;border:1px solid #22242a;border-radius:12px;text-decoration:none;font-size:14px;font-weight:500">← Back</a>
                <button type="submit" style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#6366f1;color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:500;cursor:pointer;box-shadow:0 4px 16px rgba(99,102,241,.3)">Save & Continue →</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>