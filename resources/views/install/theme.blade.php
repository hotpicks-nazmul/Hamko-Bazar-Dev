<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme · Install Hamko Bazar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif']}}}}</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{background:#0a0b0d;font-family:'Inter',system-ui,sans-serif}.anim-up{animation:up .45s ease-out}@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}.step-dot{width:8px;height:8px;border-radius:50%;background:#2a2c32;flex-shrink:0}.step-dot.active{background:#6366f1;box-shadow:0 0 10px rgba(99,102,241,.4)}.step-dot.done{background:#4b5563}.step-line{width:22px;height:1px;background:#2a2c32;flex-shrink:0}.step-line.fill{background:#4b5563}.t-card{transition:all .15s}.t-card:hover{border-color:#3b3d44;transform:translateY(-1px)}.t-card.sel{border-color:#6366f1;box-shadow:0 0 0 1px rgba(99,102,241,.2)}</style>
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:#0a0b0d;font-family:'Inter',system-ui,sans-serif">
<div style="width:100%;max-width:660px;background:#111215;border:1px solid #22242a;border-radius:20px;box-shadow:0 25px 50px -12px rgba(0,0,0,.5);animation:up .45s ease-out">
    <div style="text-align:center;padding:48px 40px 0">
        <h1 style="color:#f0f1f3;font-size:22px;font-weight:600;margin-bottom:4px">Choose a Theme</h1>
        <p style="color:#9ca3af;font-size:16px">Pick a style for your store</p>
    </div>
    <div style="padding:28px 40px 40px">
        <div style="display:flex;align-items:center;justify-content:center;gap:4px;margin-bottom:20px">
            <div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot active"></div><div class="step-line"></div><div class="step-dot"></div>
        </div>
        <p style="text-align:center;font-size:15px;color:#9ca3af;margin-bottom:24px">Step <strong style="color:#e5e7eb">5</strong> of <strong style="color:#e5e7eb">6</strong> · Theme</p>
        @if(session('error'))<div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);color:#fca5a5;border-radius:12px;padding:12px 16px;font-size:14px;margin-bottom:16px">{{ session('error') }}</div>@endif
        <form action="{{ route('install.save-theme') }}" method="POST" id="f">
            @csrf<input type="hidden" name="theme" id="s" value="">
            @php $d=[['slug'=>'food','n'=>'Food','d'=>'Restaurants & grocery','c'=>'Food','t'=>['Menu','Order','Delivery'],'i'=>'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400&h=250&fit=crop'],['slug'=>'tech','n'=>'Technology','d'=>'Electronics & gadgets','c'=>'Tech','t'=>['Compare','Specs','Reviews'],'i'=>'https://images.unsplash.com/photo-1518770660439-4636190af475?w=400&h=250&fit=crop'],['slug'=>'edu','n'=>'Education','d'=>'Courses & books','c'=>'Edu','t'=>['Courses','Progress','Certificates'],'i'=>'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=400&h=250&fit=crop'],['slug'=>'general','n'=>'General','d'=>'Versatile for any product','c'=>'General','t'=>['Grid','Quick View','Wishlist'],'i'=>'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400&h=250&fit=crop'],['slug'=>'fashion','n'=>'Fashion','d'=>'Clothing & accessories','c'=>'Fashion','t'=>['Sizes','Swatches','Trending'],'i'=>'https://images.unsplash.com/photo-1445205170230-053b83016050?w=400&h=250&fit=crop'],['slug'=>'virtual','n'=>'Digital','d'=>'Downloads & software','c'=>'Digital','t'=>['Download','Licenses','Support'],'i'=>'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=400&h=250&fit=crop']];$themes=!empty($themes)?$themes:collect($d)->keyBy('slug');@endphp
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                @foreach($themes as $k=>$t)
                <div class="t-card" data-t="{{ $k }}" onclick="sel('{{ $k }}')" style="border:1px solid #22242a;border-radius:12px;overflow:hidden;cursor:pointer;position:relative">
                    <div style="height:80px;background-size:cover;background-position:center;background-image:url('{{ $t['i']??$t['preview']??$t['img']??'' }}')">
                        <span style="position:absolute;top:6px;left:6px;width:18px;height:18px;border-radius:50%;background:#6366f1;display:none;align-items:center;justify-content:center;color:#fff;font-size:9px" class="chk">✓</span>
                        <span style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,.55);color:rgba(255,255,255,.8);font-size:8px;font-weight:600;text-transform:uppercase;padding:2px 7px;border-radius:8px">{{ $t['c']??'General' }}</span>
                    </div>
                    <div style="padding:10px 12px">
                        <h5 style="color:#d1d5db;font-size:13px;font-weight:600;margin-bottom:2px">{{ $t['n']??$t['name'] }}</h5>
                        <p style="color:#4b5563;font-size:10px;margin-bottom:6px;line-height:1.3">{{ $t['d']??$t['description']??'' }}</p>
                        <div style="display:flex;flex-wrap:wrap;gap:3px">@foreach(($t['t']??$t['tags']??$t['features']??[]) as $x)<span style="background:rgba(255,255,255,.03);color:#6b7280;font-size:9px;padding:2px 7px;border-radius:8px">{{ $x }}</span>@endforeach</div>
                    </div>
                </div>
                @endforeach
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:24px;padding-top:20px;border-top:1px solid #22242a">
                <a href="{{ route('install.site-config') }}" style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#1e2025;color:#9ca3af;border:1px solid #22242a;border-radius:12px;text-decoration:none;font-size:14px;font-weight:500">← Back</a>
                <button type="submit" id="b" disabled style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#6366f1;color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:500;cursor:pointer;box-shadow:0 4px 16px rgba(99,102,241,.3);opacity:.4">Continue →</button>
            </div>
        </form>
    </div>
</div>
<script>
function sel(k){document.querySelectorAll('.t-card').forEach(c=>{c.classList.remove('sel');c.querySelector('.chk').style.display='none'});let el=document.querySelector(`.t-card[data-t="${k}"]`);el.classList.add('sel');el.querySelector('.chk').style.display='flex';document.getElementById('s').value=k;document.getElementById('b').disabled=false;document.getElementById('b').style.opacity='1'}
</script>
</body>
</html>