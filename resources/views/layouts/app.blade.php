<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FreshCart | Grocery Online')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @include('partials.styles')
</head>
<body class="bg-gray-50 text-gray-800" x-data="{ sidebarOpen: true, cartOpen: false, loginModal: false }">

    <header class="bg-white border-b sticky top-0 z-50 h-16 flex items-center px-4 justify-between shadow-sm">
        <div class="flex items-center gap-4">
            @include('partials.menu-icon')
            @include('partials.logo')
            @include('partials.location')
        </div>

        @include('partials.search')

        <div class="flex items-center gap-3">
            @include('partials.language')
            @include('partials.login-btn')
        </div>
    </header>

    <div class="flex">
        @include('partials.sidebar-menu')

        <main class="flex-1 min-h-screen">
            @yield('content')
        </main>

        @include('partials.cart-slider')
    </div>

    @include('partials.login-modal')

    @include('partials.scripts')
    
    @stack('scripts')
</body>
</html>