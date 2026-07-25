<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete · Hamko Bazar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif']}}}}</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{background:#0a0b0d;font-family:'Inter',system-ui,sans-serif}.anim-pop{animation:pop .6s cubic-bezier(.34,1.56,.64,1)}@keyframes pop{0%{opacity:0;transform:scale(.93) translateY(12px)}100%{opacity:1;transform:scale(1) translateY(0)}}.anim-bounce{animation:bounce .7s cubic-bezier(.34,1.56,.64,1) .2s both}@keyframes bounce{0%{transform:scale(0)}100%{transform:scale(1)}}canvas{position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:1000}</style>
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:#0a0b0d;font-family:'Inter',system-ui,sans-serif">
<canvas id="c"></canvas>
<div style="width:100%;max-width:550px;background:#111215;border:1px solid #22242a;border-radius:20px;box-shadow:0 25px 50px -12px rgba(0,0,0,.5);animation:pop .6s cubic-bezier(.34,1.56,.64,1)">
    <div style="text-align:center;padding:52px 40px 32px">
        <div style="width:72px;height:72px;background:linear-gradient(135deg,#10b981,#059669);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:30px;color:#fff;box-shadow:0 8px 24px rgba(16,185,129,.25);animation:bounce .7s cubic-bezier(.34,1.56,.64,1) .2s both">✓</div>
        <h1 style="color:#f0f1f3;font-size:24px;font-weight:700;margin-bottom:8px">All Set! 🎉</h1>
        <p style="color:#9ca3af;font-size:16px">Your store is ready to launch</p>
    </div>
    <div style="padding:0 40px 40px;text-align:center">
        <p style="color:#9ca3af;font-size:14px;line-height:1.6;margin-bottom:4px"><strong style="color:#e5e7eb">Hamko Bazar</strong> has been successfully installed.</p>
        <p style="color:#4b5563;font-size:14px;margin-bottom:24px">You can now start building your online store.</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:24px;text-align:left">
            <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#181a1f;border:1px solid #22242a;border-radius:10px"><span style="color:#34d399;font-size:12px;flex-shrink:0">✓</span><span style="color:#9ca3af;font-size:12px">Theme System</span></div>
            <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#181a1f;border:1px solid #22242a;border-radius:10px"><span style="color:#34d399;font-size:12px;flex-shrink:0">✓</span><span style="color:#9ca3af;font-size:12px">Payment Gateways</span></div>
            <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#181a1f;border:1px solid #22242a;border-radius:10px"><span style="color:#34d399;font-size:12px;flex-shrink:0">✓</span><span style="color:#9ca3af;font-size:12px">SEO Optimized</span></div>
            <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#181a1f;border:1px solid #22242a;border-radius:10px"><span style="color:#34d399;font-size:12px;flex-shrink:0">✓</span><span style="color:#9ca3af;font-size:12px">Live Chat &amp; AI</span></div>
            <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#181a1f;border:1px solid #22242a;border-radius:10px"><span style="color:#34d399;font-size:12px;flex-shrink:0">✓</span><span style="color:#9ca3af;font-size:12px">Product Management</span></div>
            <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#181a1f;border:1px solid #22242a;border-radius:10px"><span style="color:#34d399;font-size:12px;flex-shrink:0">✓</span><span style="color:#9ca3af;font-size:12px">Analytics &amp; Reports</span></div>
        </div>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            <a href="{{ route('super-admin.login') }}" style="display:inline-flex;align-items:center;gap:8px;padding:14px 32px;background:#6366f1;color:#fff;border-radius:12px;text-decoration:none;font-size:14px;font-weight:500;box-shadow:0 4px 16px rgba(99,102,241,.3)">⚡ Go to Admin</a>
            <a href="{{ route('home') }}" style="display:inline-flex;align-items:center;gap:8px;padding:14px 32px;background:#1e2025;color:#9ca3af;border:1px solid #22242a;border-radius:12px;text-decoration:none;font-size:14px;font-weight:500">◉ View Store</a>
        </div>
        <div style="display:flex;gap:10px;background:rgba(251,191,36,.05);border:1px solid rgba(251,191,36,.1);border-radius:12px;padding:12px 16px;margin-top:20px;text-align:left;font-size:12px;color:#d97706;line-height:1.5"><span style="flex-shrink:0">⚠</span><span>The installation route is now locked automatically. Manage access from the admin panel settings.</span></div>
    </div>
</div>
<script>
(function(){const c=document.getElementById('c'),ctx=c.getContext('2d');c.width=window.innerWidth;c.height=window.innerHeight;const p=[],clrs=['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'];for(let i=140;i--;)p.push({x:Math.random()*c.width,y:Math.random()*c.height-c.height,w:Math.random()*7+3,h:Math.random()*4+2,cl:clrs[Math.floor(Math.random()*clrs.length)],r:Math.random()*360,rs:Math.random()*10-5,s:Math.random()*2+2,d:Math.random()*2-1});(function loop(){ctx.clearRect(0,0,c.width,c.height);let a=0;p.forEach(q=>{q.y+=q.s;q.x+=q.d;q.r+=q.rs;if(q.y<c.height+20)a++;ctx.save();ctx.translate(q.x,q.y);ctx.rotate(q.r*Math.PI/180);ctx.fillStyle=q.cl;ctx.fillRect(-q.w/2,-q.h/2,q.w,q.h);ctx.restore()});if(a)requestAnimationFrame(loop)})()})()
</script>
</body>
</html>