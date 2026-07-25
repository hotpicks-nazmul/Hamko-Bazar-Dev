<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff Login - {{ config('app.name') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        halal: {
                            green: '#2D5A27',
                            light: '#4A7C43',
                            dark: '#1E3D1A',
                            gold: '#D4AF37',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center font-poppins">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block">
                <h1 class="text-3xl font-bold text-halal-green">
                    <i class="bi bi-person-badge me-2"></i>{{ config('app.name') }}
                </h1>
            </a>
            <p class="text-gray-500 mt-2">Staff Panel</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-center mb-6">Staff Login</h2>
            
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('staff.login.post') }}">
                @csrf
                
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="bi bi-envelope text-gray-400"></i>
                        </span>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-halal-green focus:border-halal-green outline-none transition"
                               placeholder="staff@example.com" 
                               required>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="bi bi-lock text-gray-400"></i>
                        </span>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-halal-green focus:border-halal-green outline-none transition"
                               placeholder="••••••••" 
                               required>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-halal-green hover:bg-halal-dark text-white font-semibold py-3 rounded-lg transition duration-200">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Login as Staff
                </button>
            </form>

            <!-- Login Links -->
            <div class="mt-6 text-center">
                <p class="text-gray-500 text-sm">
                    Different role?
                    <a href="{{ route('super-admin.login') }}" class="text-halal-green hover:underline">Super Admin</a>
                    |
                    <a href="{{ route('admin.login') }}" class="text-halal-green hover:underline">Admin</a>
                </p>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-gray-500 hover:text-halal-green">
                <i class="bi bi-arrow-left me-1"></i> Back to Website
            </a>
        </div>
    </div>
</body>
</html>
