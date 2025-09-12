<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

    @yield('style')
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="flex flex-row justify-between items-center">
                <a class="navbar-brand p-4" href="{{ url('/') }}">
                    <img src="{{ asset('images/main-logo.png') }}" width="75">
                </a>
                <button id="dropdownUserAvatarButton" data-dropdown-toggle="dropdownAvatar" class="flex items-center md:me-0" type="button">
                    <div>
                        <img class="w-10 h-10 rounded-full object-cover" src="{{ asset('images/main-logo.png') }}" alt="user photo">
                    </div>
                    <div class="px-4 py-3 text-gray-900 dark:text-white">
                        <div>{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</div>
                    </div>
                </button>

                <!-- Dropdown menu -->
                <div id="dropdownAvatar" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700 dark:divide-gray-600">
                    <div class="px-4 py-3 text-gray-900 dark:text-white">
                        <div class="font-medium truncate">{{ Auth::user()->email }}</div>
                    </div>
                    <div class="py-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="px-4 py-3 text-gray-900 dark:text-white">Logout</button>
                        </form>
                    </div>
                </div>

            </div>
        </nav>

        <main class="py-4">
            @include('layouts.sidebar')
            @yield('content')
        </main>
    </div>
    @yield('javascript')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
    function logout() {
        axios.post("api/logout", {}, {
        });
    };
    </script>
</body>
</html>
