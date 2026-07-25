<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment · Install Hamko Bazar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif']}}}}</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{background:#0a0b0d;font-family:'Inter',system-ui,sans-serif}.anim-up{animation:up .45s ease-out}@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}.step-dot{width:8px;height:8px;border-radius:50%;background:#2a2c32;flex-shrink:0}.step-dot.active{background:#6366f1;box-shadow:0 0 10px rgba(99,102,241,.4)}.step-dot.done{background:#4b5563}.step-line{width:22px;height:1px;background:#2a2c32;flex-shrink:0}.step-line.fill{background:#4b5563}.gw{transition:all .12s}input:focus{border-color:#6366f1!important;background:#1a1c24!important;box-shadow:0 0 0 3px rgba(99,102,241,.12)!important}.toggle{position:relative;width:34px;height:18px;display:inline-block}.toggle input{opacity:0;width:0;height:0}.toggle .sl{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#2a2c32;border-radius:18px;transition:.2s}.toggle .sl::before{content:'';position:absolute;height:12px;width:12px;left:3px;bottom:3px;background:#4b5563;border-radius:50%;transition:.2s}.toggle input:checked+.sl{background:#6366f1}.toggle input:checked+.sl::before{background:#fff;transform:translateX(16px)}</style>
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:#0a0b0d;font-family:'Inter',system-ui,sans-serif">
<div style="width:100%;max-width:620px;background:#111215;border:1px solid #22242a;border-radius:20px;box-shadow:0 25px 50px -12px rgba(0,0,0,.5);animation:up .45s ease-out">
    <div style="text-align:center;padding:48px 40px 0">
        <h1 style="color:#f0f1f3;font-size:22px;font-weight:600;margin-bottom:4px">Payment Gateways</h1>
        <p style="color:#9ca3af;font-size:16px">Configure payment methods</p>
    </div>
    <div style="padding:28px 40px 40px">
        <div style="display:flex;align-items:center;justify-content:center;gap:4px;margin-bottom:20px">
            <div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot done"></div><div class="step-line fill"></div><div class="step-dot active"></div>
        </div>
        <p style="text-align:center;font-size:15px;color:#9ca3af;margin-bottom:24px">Step <strong style="color:#e5e7eb">6</strong> of <strong style="color:#e5e7eb">6</strong> · Payment</p>
        @if(session('error'))<div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);color:#fca5a5;border-radius:12px;padding:12px 16px;font-size:14px;margin-bottom:16px">{{ session('error') }}</div>@endif
        <div style="display:flex;gap:12px;background:rgba(99,102,241,.05);border:1px solid rgba(99,102,241,.1);border-radius:12px;padding:12px 16px;margin-bottom:20px;font-size:14px;color:#a5b4fc;line-height:1.5"><span style="color:#818cf8;flex-shrink:0">ⓘ</span><span>Configure now or skip and set up later from the admin panel.</span></div>
        <form action="{{ route('install.save-payment') }}" method="POST">
            @csrf
            @php $gws=!empty($gateways)?$gateways:['bkash'=>['name'=>'bKash','desc'=>'Leading mobile financial service in Bangladesh','f'=>['app_key','app_secret','username','password']],'nagad'=>['name'=>'Nagad','desc'=>'Digital financial service by Bangladesh Post Office','f'=>['merchant_id','merchant_number','public_key','private_key']],'rocket'=>['name'=>'Rocket','desc'=>'Mobile banking by Dutch-Bangla Bank','f'=>['merchant_id','merchant_number','password']],'sslcommerz'=>['name'=>'SSLCommerz','desc'=>'Online payment gateway for merchants','f'=>['store_id','store_password']],'cod'=>['name'=>'Cash on Delivery','desc'=>'Pay when you receive your order','f'=>[]]]; @endphp
            @foreach($gws as $k=>$g)
            <div class="gw" id="gw-{{ $k }}" style="border:1px solid #22242a;border-radius:12px;padding:16px;margin-bottom:10px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.03);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">
                            @if($k=='bkash')<span style="color:#e11d48">☏</span>@elseif($k=='nagad')<span style="color:#d97706">☏</span>@elseif($k=='rocket')<span style="color:#7c3aed">☏</span>@elseif($k=='sslcommerz')<span style="color:#2563eb">◈</span>@elseif($k=='cod')<span style="color:#059669">$</span>@else<span>◎</span>@endif
                        </div>
                        <div><div style="color:#e5e7eb;font-size:14px;font-weight:500">{{ $g['name'] }}</div><div style="color:#4b5563;font-size:12px;margin-top:1px">{{ $g['desc']??$g['description']??'' }}</div></div>
                    </div>
                    <label class="toggle"><input type="checkbox" name="{{ $k }}_enabled" value="1" onchange="tog('{{ $k }}')"><span class="sl"></span></label>
                </div>
                @if(!empty($g['f']??$g['fields']??[]))
                @php $fs=$g['f']??$g['fields']??[]; @endphp
                <div class="gw-f" id="gwf-{{ $k }}" style="display:none;margin-top:12px;padding-top:12px;border-top:1px solid #22242a">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                        <label class="toggle" style="width:26px;height:14px"><input type="checkbox" name="{{ $k }}_test_mode" value="1" checked><span class="sl" style="height:14px"></span></label>
                        <span style="color:#4b5563;font-size:12px">Test mode</span>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                        @foreach($fs as $f)
                        <div style="position:relative"><span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#4b5563;font-size:11px;pointer-events:none">●</span><input type="text" name="{{ $k }}_credentials[{{ $f }}]" placeholder="{{ ucfirst(str_replace('_',' ',$f)) }}" style="width:100%;padding:10px 12px 10px 30px;background:#181a1f;border:1px solid #22242a;border-radius:8px;font-size:13px;font-family:'Inter',sans-serif;color:#e5e7eb;outline:none"></div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endforeach
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;padding-top:18px;border-top:1px solid #22242a">
                <a href="{{ route('install.theme') }}" style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#1e2025;color:#9ca3af;border:1px solid #22242a;border-radius:12px;text-decoration:none;font-size:14px;font-weight:500">← Back</a>
                <div style="display:flex;gap:8px;align-items:center">
                    <button type="submit" name="skip" value="1" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:transparent;color:#4b5563;border:1px solid #22242a;border-radius:12px;font-size:14px;font-weight:500;cursor:pointer">Skip</button>
                    <button type="submit" style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#6366f1;color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:500;cursor:pointer;box-shadow:0 4px 16px rgba(99,102,241,.3)">Complete ✓</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
function tog(s){let c=document.getElementById('gw-'+s),f=document.getElementById('gwf-'+s),cb=c.querySelector('input[type="checkbox"]');if(cb.checked){f.style.display='block';c.style.borderColor='rgba(99,102,241,.2)'}else{f.style.display='none';c.style.borderColor='#22242a'}}
</body>
</html>