{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'HelpOfAi Studio') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/css/logo.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-950 text-slate-100 font-sans antialiased selection:bg-indigo-500/30 selection:text-indigo-200 overflow-x-hidden">
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <!-- Ambient Background Glows matching core design -->
        <div class="absolute -top-40 -left-40 w-[36rem] h-[36rem] bg-purple-600/20 rounded-full blur-[140px] animate-pulse"></div>
        <div class="absolute top-1/4 -right-40 w-[34rem] h-[34rem] bg-indigo-600/15 rounded-full blur-[140px]"></div>
        <div class="absolute top-2/3 -left-20 w-[30rem] h-[30rem] bg-cyan-600/15 rounded-full blur-[140px]"></div>
        <div class="absolute -bottom-40 right-1/4 w-[40rem] h-[40rem] bg-purple-900/20 rounded-full blur-[160px]"></div>

        <!-- Mouse Cursor Interactive Glow Spotlight -->
        <div 
            id="hoa-cursor-spotlight" 
            class="fixed top-0 left-0 -mt-64 -ml-64 w-[32rem] h-[32rem] rounded-full pointer-events-none transition-opacity duration-500 ease-out opacity-0 z-0 will-change-transform"
            style="background: radial-gradient(circle at center, rgba(129, 140, 248, 0.12) 0%, rgba(168, 85, 247, 0.06) 35%, rgba(6, 182, 212, 0.02) 65%, transparent 80%); filter: blur(40px);"
        ></div>
    </div>

    <div class="min-h-screen flex flex-col">
        {{ $slot ?? $content ?? '' }}
        @yield('content')
    </div>

    @livewireScripts
</body>
</html>