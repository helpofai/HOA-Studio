{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Public Navigation Footer Component
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

<!-- Footer -->
<footer class="border-t border-white/5 bg-slate-950/80 py-12 text-xs text-slate-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <x-glass.logo size="sm" text="HOA" />
            <span class="font-bold text-white">HelpOfAi (HOA) Studio</span>
            <span>&bull;</span>
            <span>Copyright &copy; {{ date('Y') }} Rajib Adhikary. All Rights Reserved.</span>
        </div>
        <div class="flex items-center gap-6">
            <a href="https://helpofai.com" target="_blank" class="hover:text-white transition-colors">HelpOfAi.com</a>
            <a href="{{ request()->is('/') ? '#features' : url('/#features') }}" class="hover:text-white transition-colors">Architecture</a>
            <a href="{{ request()->is('/') ? '#engines' : url('/#engines') }}" class="hover:text-white transition-colors">Engines</a>
            <a href="{{ route('blog.index') }}" class="hover:text-white transition-colors {{ request()->routeIs('blog.*') ? 'text-violet-400 font-semibold' : '' }}">Blog</a>
            <a href="{{ route('editor') }}" class="hover:text-white transition-colors">Universal Editor</a>
        </div>
    </div>
</footer>
