<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Failsafe CSS in case Tailwind is not compiled or loaded */
        :root {
            --primary: #1E1E1E;
            --blue: #3B82F6;
            --gray-text: #6B7280;
        }
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Figtree', sans-serif;
            background-color: #fff;
        }
        .main-container {
            display: flex;
            height: 100vh;
            width: 100%;
        }
        .left-panel {
            flex: 7;
            position: relative;
            background-color: #000;
            display: flex;
        }
        .right-panel {
            flex: 5;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background-color: #fff;
            overflow-y: auto;
        }
        .bg-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.7;
        }
        .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            z-index: 1;
        }
        .left-content {
            position: relative;
            z-index: 2;
            padding: 60px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            box-sizing: border-box;
        }
        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logo-img {
            height: 50px;
            width: auto;
            border-radius: 12px;
        }
        .form-box {
            max-width: 400px;
            width: 100%;
        }
        .input-group {
            margin-bottom: 24px;
        }
        .input-group label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #374151;
        }
        .input-field {
            width: 100%;
            padding: 14px 16px;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 16px;
            font-size: 16px;
            box-sizing: border-box;
            transition: all 0.2s;
        }
        .input-field:focus {
            outline: none;
            border-color: #3B82F6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .btn-login {
            width: 100%;
            background: #1E1E1E;
            color: white;
            padding: 16px;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: #000;
        }
        .password-container {
            position: relative;
        }
        .toggle-pass {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9CA3AF;
        }
        @media (max-width: 1024px) {
            .left-panel {
                display: none;
            }
            .right-panel {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        
        <!-- Left Section -->
        <div class="left-panel">
            <img src="{{ asset('images/dreamville_foto.jpg') }}" alt="Background" class="bg-img">
            <div class="overlay"></div>
            <div class="left-content">
                <div class="logo-container">
                    <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 10px; border-radius: 15px;">
                        <img src="{{ asset('images/dreamville.webp') }}" class="logo-img" alt="Logo">
                    </div>
                    <span style="font-size: 24px; font-weight: 700; letter-spacing: -0.5px;">DreamVille</span>
                </div>

                <div>
                    <h1 style="font-size: 56px; line-height: 1.1; margin-bottom: 20px; font-weight: 800;">
                        Find your sweet<br><span style="color: #60A5FA;">home</span>
                    </h1>
                    <p style="font-size: 18px; color: #D1D5DB; line-height: 1.6; max-width: 450px;">
                        Schedule visit in just a few clicks. Sistem manajemen reservasi modern untuk pengalaman terbaik.
                    </p>
                    <div style="display: flex; gap: 8px; margin-top: 40px;">
                        <div style="width: 40px; height: 6px; background: white; border-radius: 10px;"></div>
                        <div style="width: 10px; height: 6px; background: rgba(255,255,255,0.3); border-radius: 10px;"></div>
                        <div style="width: 10px; height: 6px; background: rgba(255,255,255,0.3); border-radius: 10px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section -->
        <div class="right-panel">
            <div class="form-box">
                
                <!-- Mobile Logo -->
                <div class="lg-hidden-custom" style="text-align: center; margin-bottom: 40px; display: none;">
                    <img src="{{ asset('images/dreamville.webp') }}" style="height: 60px; width: auto; margin-bottom: 10px;" alt="Logo">
                    <h2 style="font-size: 24px; font-weight: 800; color: #111827; margin: 0;">DreamVille</h2>
                </div>

                <div style="margin-bottom: 40px;">
                    <h2 style="font-size: 36px; font-weight: 800; color: #111827; margin-bottom: 10px; letter-spacing: -1px;">Welcome Back!</h2>
                    <p style="color: #6B7280; font-size: 18px; font-weight: 500; margin: 0;">Please sign in to your account</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-6" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Username -->
                    <div class="input-group">
                        <label for="username">Your Username</label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                            class="input-field" placeholder="Enter your username">
                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="password-container">
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                class="input-field" placeholder="••••••••">
                            <button type="button" onclick="togglePasswordVisibility()" class="toggle-pass">
                                <svg id="pass-icon" style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember & Forgot -->
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px;">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input id="remember_me" type="checkbox" name="remember" style="width: 18px; height: 18px; cursor: pointer; border-radius: 4px; border: 1px solid #D1D5DB;">
                            <span style="font-size: 14px; font-weight: 600; color: #4B5563; margin-left: 8px;">Remember Me</span>
                        </label>
                        
                        @if (Route::has('password.request'))
                            <a style="font-size: 14px; font-weight: 700; color: #9CA3AF; text-decoration: none;" href="{{ route('password.request') }}">
                                Forgot Password?
                            </a>
                        @endif
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn-login">Login</button>
                </form>

                <div style="margin-top: 60px; color: #9CA3AF; font-size: 13px; font-weight: 500;">
                    &copy; {{ date('Y') }} DreamVille Management System. All rights reserved.
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passInput = document.getElementById('password');
            const passIcon = document.getElementById('pass-icon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                passIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.888 9.888L5.122 5.122m7.878 7.878l4.766 4.766m2.805-2.805A9.944 9.944 0 0021.543 12c-1.274-4.057-5.064-7-9.542-7-1.295 0-2.52.257-3.636.721m12.181 12.181l-3.636-3.636" />';
            } else {
                passInput.type = 'password';
                passIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }

        // Show mobile logo if screen is small
        function checkMobile() {
            const mobileLogo = document.querySelector('.lg-hidden-custom');
            if (window.innerWidth <= 1024) {
                mobileLogo.style.display = 'block';
            } else {
                mobileLogo.style.display = 'none';
            }
        }
        window.addEventListener('resize', checkMobile);
        checkMobile();
    </script>
</body>
</html>
