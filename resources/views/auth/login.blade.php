<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول - {{ config('app.name', 'المرآب') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-md px-8 py-10">

            {{-- Header --}}
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ \App\Models\Setting::get('institution_name', 'المرآب') }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">نظام إدارة المرآب</p>
            </div>

            {{-- Error messages --}}
            @if ($errors->any())
                <div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ $errors->first() }}</p>
                </div>
            @endif

            {{-- Session errors (e.g. failed login) --}}
            @if (session('status'))
                <div class="mb-5 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-600">{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-5">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        البريد الإلكتروني
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition"
                        placeholder="admin@example.com"
                    >
                </div>

                {{-- Password --}}
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        كلمة المرور
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition"
                    >
                </div>

                {{-- Remember me --}}
                <div class="flex items-center mb-6">
                    <input type="checkbox" id="remember" name="remember"
                           class="w-4 h-4 text-slate-600 border-gray-300 rounded">
                    <label for="remember" class="mr-2 text-sm text-gray-600">تذكرني</label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full bg-slate-800 hover:bg-slate-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors text-sm">
                    تسجيل الدخول
                </button>
            </form>

        </div>
    </div>

</body>
</html>
