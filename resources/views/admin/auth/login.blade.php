<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - {{ config('app.name') }}</title>
    
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
                    <i class="bi bi-shop mr-2"></i>{{ config('app.name') }}
                </h1>
            </a>
            <p class="text-gray-500 mt-2">Admin Panel</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Admin Login</h2>
            
            @if(session('status'))
                <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4">
                    {{ session('status') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('admin.login.post') }}">
                @csrf
                
                @if($errors->any())
                    <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                
                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-halal-green focus:border-transparent"
                            placeholder="admin@example.com" required>
                    </div>
                </div>
                
                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-halal-green focus:border-transparent"
                            placeholder="••••••••" required>
                    </div>
                </div>
                
                <!-- Remember Me -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-halal-green border-gray-300 rounded focus:ring-halal-green">
                        <span class="ml-2 text-gray-600 text-sm">Remember me</span>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="w-full bg-halal-green text-white py-3 rounded-lg font-medium hover:bg-halal-dark transition-colors flex items-center justify-center">
                    <i class="bi bi-box-arrow-in-right mr-2"></i>
                    Sign In
                </button>
            </form>
            
            <!-- Login Links -->
            <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                <p class="text-gray-500 text-sm">
                    Different role?
                    <a href="{{ route('super-admin.login') }}" class="text-halal-green hover:underline">Super Admin</a>
                    |
                    <a href="{{ route('staff.login') }}" class="text-halal-green hover:underline">Staff Login</a>
                </p>
            </div>
        </div>
        
        <!-- Back to Store -->
        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-gray-500 hover:text-halal-green transition-colors">
                <i class="bi bi-arrow-left mr-1"></i>
                Back to Store
            </a>
        </div>
    </div>
</body>
</html>
