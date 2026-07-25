<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install · Hamko Bazar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: { surface: '#111215', bgdark: '#0a0b0d', bord: '#22242a', muted: '#4b5563', soft: '#9ca3af', light: '#d1d5db', bright: '#e5e7eb', white85: '#f0f1f3' }
                }
            }
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #0a0b0d; font-family: 'Inter', system-ui, sans-serif; }
        .anim-up { animation: up .45s ease-out; }
        @keyframes up { from { opacity: 0; transform: translateY(14px) } to { opacity: 1; transform: translateY(0) } }
        .anim-bounce { animation: bounce .7s cubic-bezier(.34,1.56,.64,1) .2s both; }
        @keyframes bounce { 0% { transform: scale(0) } 100% { transform: scale(1) } }
        .step-dot { width: 8px; height: 8px; border-radius: 50%; background: #2a2c32; flex-shrink: 0; }
        .step-dot.active { background: #6366f1; box-shadow: 0 0 10px rgba(99,102,241,.4); }
        .step-dot.done { background: #4b5563; }
        .step-line { width: 22px; height: 1px; background: #2a2c32; flex-shrink: 0; }
        .step-line.fill { background: #4b5563; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center" style="padding:20px;background:#0a0b0d">
    <div class="w-full max-w-[720px]" style="background:#111215;border:1px solid #22242a;border-radius:20px;box-shadow:0 25px 50px -12px rgba(0,0,0,.5);animation:up .45s ease-out">
        <div class="text-center" style="padding:54px 48px 0">
            <div class="w-15 h-15 bg-gradient-to-br from-indigo-500 to-purple-500" style="width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px;color:#fff;box-shadow:0 8px 20px rgba(99,102,241,.25);animation:bounce .7s cubic-bezier(.34,1.56,.64,1) .2s both">H</div>
            <h1 style="color:#f0f1f3;font-size:22px;font-weight:600;letter-spacing:-.02em;margin-bottom:4px">Welcome to Hamko Bazar</h1>
            <p style="color:#9ca3af;font-size:16px">Multi-purpose e-commerce platform</p>
        </div>
        <div style="padding:32px 48px 40px">
            <div class="flex items-center justify-center" style="gap:4px;margin-bottom:24px">
                <div class="step-dot active"></div><div class="step-line fill"></div>
                <div class="step-dot"></div><div class="step-line"></div>
                <div class="step-dot"></div><div class="step-line"></div>
                <div class="step-dot"></div><div class="step-line"></div>
                <div class="step-dot"></div><div class="step-line"></div>
                <div class="step-dot"></div>
            </div>
            <p class="text-center" style="font-size:15px;color:#9ca3af;margin-bottom:24px">Step <strong style="color:#e5e7eb">1</strong> of <strong style="color:#e5e7eb">6</strong> · Welcome</p>
            <h2 style="color:#f0f1f3;font-size:18px;font-weight:600;text-align:center;margin-bottom:4px">Ready to launch your store?</h2>
            <p style="color:#9ca3af;font-size:15px;text-align:center;margin-bottom:24px">Follow the steps below to get started</p>
            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:32px">
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#181a1f;border:1px solid #22242a;border-radius:12px;color:#9ca3af;font-size:15px"><span style="color:#34d399;font-weight:700">✓</span> Check server requirements & permissions</div>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#181a1f;border:1px solid #22242a;border-radius:12px;color:#9ca3af;font-size:15px"><span style="color:#34d399;font-weight:700">✓</span> Configure MySQL database</div>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#181a1f;border:1px solid #22242a;border-radius:12px;color:#9ca3af;font-size:15px"><span style="color:#34d399;font-weight:700">✓</span> Set up site info & admin account</div>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#181a1f;border:1px solid #22242a;border-radius:12px;color:#9ca3af;font-size:15px"><span style="color:#34d399;font-weight:700">✓</span> Choose your preferred theme</div>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#181a1f;border:1px solid #22242a;border-radius:12px;color:#9ca3af;font-size:15px"><span style="color:#34d399;font-weight:700">✓</span> Configure payment gateways</div>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#181a1f;border:1px solid #22242a;border-radius:12px;color:#9ca3af;font-size:15px"><span style="color:#34d399;font-weight:700">✓</span> Go live and start selling!</div>
            </div>
            <div class="text-center">
                <a href="{{ route('install.requirements') }}" style="display:inline-flex;align-items:center;gap:8px;padding:14px 32px;background:#6366f1;color:#fff;font-size:15px;font-weight:500;border-radius:12px;text-decoration:none;transition:all .15s;box-shadow:0 4px 16px rgba(99,102,241,.3)" onmouseover="this.style.background='#7579f5'" onmouseout="this.style.background='#6366f1'">Start Installation →</a>
            </div>
        </div>
    </div>
</body>
</html>