{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - 500 Error View
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/
--}}
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — Server Error | HelpOfAi Studio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 font-sans selection:bg-indigo-500/30">
    <div class="max-w-md w-full text-center space-y-6 p-8 rounded-3xl bg-slate-900/80 border border-white/10 shadow-2xl backdrop-blur-xl">
        <div class="w-16 h-16 rounded-2xl bg-rose-600/20 border border-rose-500/30 mx-auto flex items-center justify-center text-2xl font-black text-rose-400">
            500
        </div>
        <div class="space-y-2">
            <h1 class="text-xl font-black text-white">Internal Server Error</h1>
            <p class="text-xs text-slate-400">A temporary server exception occurred. Please verify your database connection or clear view cache.</p>
        </div>
        <div class="pt-4 flex items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="inline-block px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all">
                Reload Studio
            </a>
            <a href="{{ url('/hoa-rescue.php') }}" class="inline-block px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold border border-white/10 transition-all">
                🚨 Emergency Rescue
            </a>
        </div>
    </div>
</body>
</html>
