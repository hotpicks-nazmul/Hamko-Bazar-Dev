<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Login - {{ config('app.name') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'halal-green': { DEFAULT: '#2D5A27', dark: '#1A3A16', light: '#4A7C43' },
                        'halal-gold': '#D4AF37',
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .code-input { 
            width: 3rem; height: 3.5rem; text-align: center; font-size: 1.5rem; font-weight: 600;
            border: 2px solid #e2e8f0; border-radius: 0.5rem; outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .code-input:focus {
            border-color: #2D5A27; box-shadow: 0 0 0 3px rgba(45,90,39,0.15);
        }
        .timer { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-halal-green rounded-2xl shadow-lg mb-4">
                <i class="bi bi-shield-lock-fill text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Two-Factor Verification</h1>
            <p class="text-gray-500 mt-1">Enter the code sent to your email</p>
        </div>

        <!-- Messages -->
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm flex items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm flex items-center gap-2">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <!-- Code Card -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-6">
                <i class="bi bi-envelope-fill text-halal-green text-4xl"></i>
                <p class="text-gray-600 mt-2 text-sm">
                    We sent a 6-digit code to<br>
                    <strong class="text-gray-800">{{ $email }}</strong>
                </p>
            </div>

            <form method="POST" action="{{ route('admin.verify-2fa.post') }}" id="verifyForm">
                @csrf
                
                <div class="flex justify-center gap-3 mb-6" id="codeInputs">
                    <input type="text" name="code" id="codeHidden" maxlength="6" class="sr-only" autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]*" required>
                    @for($i = 0; $i < 6; $i++)
                    <input type="text" maxlength="1" class="code-input" data-index="{{ $i }}" inputmode="numeric" pattern="[0-9]*" autofocus="{{ $i === 0 ? 'autofocus' : '' }}">
                    @endfor
                </div>

                <button type="submit" class="w-full bg-halal-green hover:bg-halal-green-dark text-white font-semibold py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2" id="verifyBtn">
                    <i class="bi bi-shield-check"></i>
                    Verify & Login
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">Didn't receive the code?</p>
                <form method="POST" action="{{ route('admin.verify-2fa.resend') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="text-halal-green hover:text-halal-green-dark font-medium text-sm transition-colors">
                        <i class="bi bi-arrow-clockwise mr-1"></i> Resend Code
                    </button>
                </form>
            </div>
        </div>

        <!-- Back to Login -->
        <div class="text-center mt-6">
            <a href="{{ route('admin.login') }}" class="text-gray-500 hover:text-gray-700 text-sm transition-colors">
                <i class="bi bi-arrow-left mr-1"></i> Back to Login
            </a>
        </div>
    </div>

    <script>
        // Auto-focus next input on digit entry
        const inputs = document.querySelectorAll('.code-input');
        const hiddenInput = document.getElementById('codeHidden');

        inputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 1);
                if (this.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateHiddenCode();
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                paste.split('').forEach((char, i) => {
                    if (inputs[i]) {
                        inputs[i].value = char;
                    }
                });
                if (paste.length === 6) {
                    inputs[5].focus();
                } else if (inputs[paste.length]) {
                    inputs[paste.length].focus();
                }
                updateHiddenCode();
            });
        });

        function updateHiddenCode() {
            let code = '';
            inputs.forEach(input => code += input.value);
            hiddenInput.value = code;
        }

        // Auto-submit when all 6 digits entered
        document.getElementById('verifyForm').addEventListener('input', function() {
            let code = '';
            inputs.forEach(input => code += input.value);
            if (code.length === 6) {
                document.getElementById('verifyBtn').disabled = true;
                document.getElementById('verifyBtn').innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Verifying...';
                this.submit();
            }
        });
    </script>
</body>
</html>
