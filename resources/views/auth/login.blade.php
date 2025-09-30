{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md">
        <div class="bg-white shadow-lg rounded-lg p-8">
            {{-- Logo / Judul --}}
            <div class="text-center mb-6">
                <img src="{{ asset('assets/img/avatars/maskot.png') }}" alt="Logo" class="mx-auto w-16 h-16">
                <h2 class="text-2xl font-bold mt-4">Login</h2>
                <p class="text-gray-500">Silakan masuk ke akun Anda</p>
            </div>

            {{-- Status Session --}}
            <x-auth-session-status class="mb-4" :status="session('status')" />

            {{-- Form Login --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Username --}}
                <div class="mb-4">
                    <x-input-label for="name" :value="__('Username')" />
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center mb-4">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                    <label for="remember_me" class="ml-2 text-sm text-gray-600">{{ __('Ingat saya') }}</label>
                </div>

                {{-- Tombol --}}
                <div class="flex items-center justify-between">
                    @if (Route::has('password.request'))
                        <a class="text-sm text-indigo-600 hover:underline" href="{{ route('password.request') }}">
                            {{ __('Lupa password?') }}
                        </a>
                    @endif

                    <x-primary-button>
                        {{ __('Login') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
