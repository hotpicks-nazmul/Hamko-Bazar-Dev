@extends('themes.general.layouts.app')

@section('title', 'Login with Phone')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Login with Phone
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-medium text-halal-green hover:text-halal-dark">
                    Register
                </a>
                <span class="mx-2">|</span>
                <a href="{{ route('login') }}" class="font-medium text-halal-green hover:text-halal-dark">
                    Login with Email
                </a>
            </p>
        </div>

        @if(session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Step 1: Enter Phone -->
        <div id="step1" class="space-y-6">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="login_phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <div class="flex mt-1">
                        <div id="login_dropdown" class="relative" style="min-width:105px">
                            <button type="button" id="login_trigger" class="inline-flex items-center gap-1.5 px-2 py-2 border border-r-0 border-gray-300 rounded-l-md bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 focus:outline-none focus:ring-1 focus:ring-halal-green w-full" onclick="toggleDropdown()">
                                <img id="login_flag" src="https://flagcdn.com/16x12/bd.png" alt="" class="w-4 h-3 inline-block">
                                <span id="login_label">+880</span>
                                <svg class="w-3 h-3 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="login_menu" class="hidden absolute top-full left-0 mt-0.5 w-72 bg-white border border-gray-300 rounded-md shadow-lg z-50 max-h-60 overflow-y-auto" style="min-width:280px">
                            </div>
                            <input type="hidden" id="login_prefix" value="+880">
                        </div>
                        <input id="login_phone" name="phone" type="tel" required maxlength="15" oninput="this.value=this.value.replace(/\D/g,'')"
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-r-md focus:outline-none focus:ring-halal-green focus:border-halal-green sm:text-sm"
                            placeholder="Phone Number" value="{{ old('phone') }}">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Auto-detected — change the prefix if needed</p>
                </div>
            </div>

            <button type="button" onclick="sendLoginOtp()"
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-halal-green hover:text-amber-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-halal-green">
                <i class="bi bi-send mr-2"></i> Send OTP
            </button>

            <div id="login_otp_error" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"></div>
        </div>

        <!-- Step 2: Verify OTP -->
        <div id="step2" class="space-y-6 hidden">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="bi bi-check-circle-fill text-green-500 mr-2"></i>
                    <span class="text-green-800 font-medium">OTP sent to <span id="display_phone"></span></span>
                </div>
                <p class="text-sm text-gray-600 mt-1">Enter the 6-digit verification code sent to your phone.</p>
            </div>

            <div>
                <label for="login_otp" class="block text-sm font-medium text-gray-700">Verification Code</label>
                <div class="flex gap-2 mt-1 justify-center">
                    @for($i = 0; $i < 6; $i++)
                    <input type="text" maxlength="1" oninput="moveNext(this, {{ $i }})" onkeydown="movePrev(this, event)"
                        class="otp-input w-12 h-12 text-center text-xl font-bold border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-halal-green focus:border-halal-green"
                        id="otp_{{ $i }}" data-index="{{ $i }}">
                    @endfor
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button" onclick="resendLoginOtp()"
                    class="text-sm font-medium text-halal-green hover:text-halal-dark">
                    <i class="bi bi-arrow-clockwise mr-1"></i> Resend OTP
                </button>
                <span id="otp_timer" class="text-sm text-gray-500">00:60</span>
            </div>

            <button type="button" onclick="verifyLoginOtp()"
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-halal-green hover:text-amber-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-halal-green">
                <i class="bi bi-check-lg mr-2"></i> Verify & Login
            </button>

            <div id="login_verify_error" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"></div>
        </div>
    </div>
</div>

<style>
.otp-input::-webkit-outer-spin-button,
.otp-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.otp-input[type=text] {
    -moz-appearance: textfield;
}
</style>

<script>
let loginPhone = '';
let otpTimer = null;
let otpSeconds = 60;

var COUNTRIES = [
  {v:'+880', c:'bd', l:'BD', n:'Bangladesh'},
  {v:'+1',   c:'us', l:'US', n:'United States / Canada'},
  {v:'+44',  c:'gb', l:'UK', n:'United Kingdom'},
  {v:'+61',  c:'au', l:'AU', n:'Australia'},
  {v:'+91',  c:'in', l:'IN', n:'India'},
  {v:'+92',  c:'pk', l:'PK', n:'Pakistan'},
  {v:'+966', c:'sa', l:'SA', n:'Saudi Arabia'},
  {v:'+971', c:'ae', l:'AE', n:'United Arab Emirates'},
  {v:'+974', c:'qa', l:'QA', n:'Qatar'},
  {v:'+965', c:'kw', l:'KW', n:'Kuwait'},
  {v:'+968', c:'om', l:'OM', n:'Oman'},
  {v:'+973', c:'bh', l:'BH', n:'Bahrain'},
  {v:'+962', c:'jo', l:'JO', n:'Jordan'},
  {v:'+961', c:'lb', l:'LB', n:'Lebanon'},
  {v:'+20',  c:'eg', l:'EG', n:'Egypt'},
  {v:'+234', c:'ng', l:'NG', n:'Nigeria'},
  {v:'+27',  c:'za', l:'ZA', n:'South Africa'},
  {v:'+33',  c:'fr', l:'FR', n:'France'},
  {v:'+49',  c:'de', l:'DE', n:'Germany'},
  {v:'+39',  c:'it', l:'IT', n:'Italy'},
  {v:'+34',  c:'es', l:'ES', n:'Spain'},
  {v:'+31',  c:'nl', l:'NL', n:'Netherlands'},
  {v:'+41',  c:'ch', l:'CH', n:'Switzerland'},
  {v:'+46',  c:'se', l:'SE', n:'Sweden'},
  {v:'+47',  c:'no', l:'NO', n:'Norway'},
  {v:'+45',  c:'dk', l:'DK', n:'Denmark'},
  {v:'+358', c:'fi', l:'FI', n:'Finland'},
  {v:'+82',  c:'kr', l:'KR', n:'South Korea'},
  {v:'+81',  c:'jp', l:'JP', n:'Japan'},
  {v:'+86',  c:'cn', l:'CN', n:'China'},
  {v:'+852', c:'hk', l:'HK', n:'Hong Kong'},
  {v:'+65',  c:'sg', l:'SG', n:'Singapore'},
  {v:'+60',  c:'my', l:'MY', n:'Malaysia'},
  {v:'+66',  c:'th', l:'TH', n:'Thailand'},
  {v:'+63',  c:'ph', l:'PH', n:'Philippines'},
  {v:'+62',  c:'id', l:'ID', n:'Indonesia'},
  {v:'+84',  c:'vn', l:'VN', n:'Vietnam'},
  {v:'+977', c:'np', l:'NP', n:'Nepal'},
  {v:'+94',  c:'lk', l:'LK', n:'Sri Lanka'},
  {v:'+95',  c:'mm', l:'MM', n:'Myanmar'},
  {v:'+93',  c:'af', l:'AF', n:'Afghanistan'},
  {v:'+98',  c:'ir', l:'IR', n:'Iran'},
  {v:'+964', c:'iq', l:'IQ', n:'Iraq'},
  {v:'+967', c:'ye', l:'YE', n:'Yemen'},
  {v:'+7',   c:'ru', l:'RU', n:'Russia'},
  {v:'+55',  c:'br', l:'BR', n:'Brazil'},
  {v:'+52',  c:'mx', l:'MX', n:'Mexico'},
  {v:'+90',  c:'tr', l:'TR', n:'Turkey'},
  {v:'+972', c:'il', l:'IL', n:'Israel'},
  {v:'+64',  c:'nz', l:'NZ', n:'New Zealand'},
];
var CC2VAL = {};
COUNTRIES.forEach(function(x){ CC2VAL[x.l] = x.v; });

// DOM refs
var phoneInput = document.getElementById('login_phone');
var ccSelect = document.getElementById('login_prefix');
var ccMenu   = document.getElementById('login_menu');
var ccFlag   = document.getElementById('login_flag');
var ccLabel  = document.getElementById('login_label');
var ccOpen   = false;

function buildMenu(selectVal) {
  ccMenu.innerHTML = '';
  COUNTRIES.forEach(function(c) {
    var item = document.createElement('button');
    item.type = 'button';
    item.className = 'flex items-center gap-2 w-full px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 text-left';
    if (c.v === selectVal) item.classList.add('bg-gray-100', 'font-medium');
    item.innerHTML = '<img src="https://flagcdn.com/16x12/'+c.c+'.png" alt="" class="w-4 h-3 inline-block"> <span class="font-medium">'+c.v+'</span> <span class="text-gray-400">('+c.l+')</span> <span class="text-gray-500 ml-auto truncate">'+c.n+'</span>';
    item.addEventListener('click', function(){ selectCountry(c.v); });
    ccMenu.appendChild(item);
  });
}

function selectCountry(val) {
  ccSelect.value = val;
  var found = COUNTRIES.find(function(x){ return x.v === val; }) || COUNTRIES[0];
  ccFlag.src = 'https://flagcdn.com/16x12/'+found.c+'.png';
  ccLabel.textContent = val;
  closeDropdown();
}

function toggleDropdown() {
  if (ccOpen) { closeDropdown(); } else { openDropdown(); }
}
function openDropdown() {
  buildMenu(ccSelect.value);
  ccMenu.classList.remove('hidden');
  ccOpen = true;
}
function closeDropdown() {
  ccMenu.classList.add('hidden');
  ccOpen = false;
}

document.addEventListener('click', function(e) {
  if (ccOpen && !document.getElementById('login_dropdown').contains(e.target)) {
    closeDropdown();
  }
});

function detectCountry() {
  fetch('https://ipapi.co/json/', { signal: AbortSignal.timeout(4000) })
    .then(function(r){ return r.json(); })
    .then(function(d) {
      var val = d.country_calling_code || CC2VAL[d.country_code];
      if (val && COUNTRIES.some(function(x){ return x.v === val; })) selectCountry(val);
    })
    .catch(function() {
      fetch('https://ip-api.com/json/', { signal: AbortSignal.timeout(4000) })
        .then(function(r){ return r.json(); })
        .then(function(d) {
          var val = CC2VAL[d.countryCode];
          if (val && COUNTRIES.some(function(x){ return x.v === val; })) selectCountry(val);
        })
        .catch(function(){});
    });
}
detectCountry();

function sendLoginOtp() {
    const phone = phoneInput.value.trim();
    const errorEl = document.getElementById('login_otp_error');

    if (!phone || phone.length < 5) {
        showError(errorEl, 'Please enter a valid phone number.');
        return;
    }

    hideError(errorEl);
    const fullPhone = ccSelect.value + phone;

    fetch('{{ route("login.phone.send-otp") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ phone: fullPhone })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loginPhone = fullPhone;
            document.getElementById('display_phone').textContent = ccSelect.value + ' ' + phone;
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.remove('hidden');
            startOtpTimer();
            showToast('success', 'OTP sent successfully!');
        } else {
            showError(errorEl, data.message || 'Failed to send OTP.');
        }
    })
    .catch(err => {
        showError(errorEl, 'An error occurred. Please try again.');
    });
}

function resendLoginOtp() {
    if (otpTimer) return;

    fetch('{{ route("login.phone.send-otp") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ phone: loginPhone })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            startOtpTimer();
            showToast('success', 'OTP resent successfully!');
        } else {
            showToast('error', data.message || 'Failed to resend OTP.');
            clearOtpInputs();
        }
    })
    .catch(() => showToast('error', 'An error occurred.'));
}

function verifyLoginOtp() {
    const otp = getOtpValue();
    const errorEl = document.getElementById('login_verify_error');

    if (otp.length !== 6) {
        showError(errorEl, 'Please enter the complete 6-digit OTP.');
        return;
    }

    hideError(errorEl);

    fetch('{{ route("login.phone.verify-otp") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ phone: loginPhone, otp: otp })
    })
    .then(res => {
        if (res.redirected) {
            window.location.href = res.url;
            return;
        }
        return res.json().then(data => ({ status: res.status, data }));
    })
    .then(result => {
        if (!result) return;
        if (result.data.success) {
            showToast('success', 'Login successful!');
            setTimeout(() => { window.location.href = result.data.redirect || '/'; }, 500);
        } else {
            showError(errorEl, result.data.message || 'Verification failed.');
        }
    })
    .catch(err => {
        showError(errorEl, 'Verification failed. Please try again.');
    });
}

function getOtpValue() {
    let otp = '';
    for (let i = 0; i < 6; i++) {
        otp += document.getElementById('otp_' + i).value || '';
    }
    return otp;
}

function clearOtpInputs() {
    for (let i = 0; i < 6; i++) {
        document.getElementById('otp_' + i).value = '';
    }
    document.getElementById('otp_0').focus();
}

function moveNext(input, index) {
    if (input.value.length === 1 && index < 5) {
        document.getElementById('otp_' + (index + 1)).focus();
    }
}

function movePrev(input, event) {
    if (event.key === 'Backspace' && !input.value && index > 0) {
        document.getElementById('otp_' + (index - 1)).focus();
    }
}

function startOtpTimer() {
    otpSeconds = 60;
    document.getElementById('otp_timer').textContent = '00:' + otpSeconds;
    if (otpTimer) clearInterval(otpTimer);
    otpTimer = setInterval(() => {
        otpSeconds--;
        document.getElementById('otp_timer').textContent = '00:' + String(otpSeconds).padStart(2, '0');
        if (otpSeconds <= 0) {
            clearInterval(otpTimer);
            otpTimer = null;
            document.getElementById('otp_timer').textContent = 'Expired';
        }
    }, 1000);
}

function showError(el, msg) {
    el.textContent = msg;
    el.classList.remove('hidden');
}

function hideError(el) {
    el.textContent = '';
    el.classList.add('hidden');
}

function showToast(type, message) {
    const colors = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-blue-500' };
    const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', info: 'bi-info-circle-fill' };
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 flex items-center gap-3 px-5 py-3 rounded-lg shadow-lg text-white ${colors[type] || 'bg-gray-700'} transition-all duration-500 slide-in`;
    toast.innerHTML = `<i class="bi ${icons[type] || 'bi-bell'}"></i><span>${message}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}

// Add slide-in animation
const style = document.createElement('style');
style.textContent = `
.slide-in {
    animation: slideIn 0.3s ease-out;
    transform: translateX(0);
    opacity: 1;
}
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
`;
document.head.appendChild(style);
</script>
@endsection
