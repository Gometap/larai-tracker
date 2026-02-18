<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Larai Tracker | Execution Logs</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }

        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f4ff', 100: '#e0e9ff', 200: '#c0d2ff', 300: '#a0bcff', 400: '#7094ff', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f8fafc; color: #0f172a; transition: background-color 0.3s ease, color 0.3s ease; }
        .dark body { background-color: #020617; color: #f8fafc; background-image: radial-gradient(at 0% 0%, hsla(215,98%,61%,0.07) 0px, transparent 50%), radial-gradient(at 100% 0%, hsla(263,70%,50%,0.07) 0px, transparent 50%); }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(0, 0, 0, 0.05); box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); transition: all 0.3s ease; }
        .dark .glass { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.05); box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); }
        .reveal { opacity: 0; transform: translateY(20px); transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
        .pagination-link { @apply glass px-4 py-2 rounded-xl text-xs font-bold hover:bg-black/5 dark:hover:bg-white/5 transition-all text-slate-600 dark:text-slate-400; }
        .pagination-link-active { @apply bg-brand-600 !text-white !opacity-100 shadow-lg shadow-brand-500/20 border-brand-500/50; }
    </style>
</head>
<body class="min-h-screen font-sans selection:bg-brand-500 selection:text-white">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 px-6 py-4 glass mb-8 border-b border-black/5 dark:border-white/5">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('larai.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-gradient-to-tr from-brand-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-brand-500/20 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-tight">Larai<span class="text-brand-500">Tracker</span></span>
                        <span class="block text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold">Execution Logs</span>
                    </div>
                </a>
            </div>
            <div class="flex items-center gap-6 text-sm font-medium">
                <button onclick="toggleTheme()" class="w-10 h-10 glass rounded-xl flex items-center justify-center hover:bg-black/5 dark:hover:bg-white/5 transition-all text-slate-500 dark:text-slate-400">
                    <svg id="theme-icon-dark" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1m-16 0h-1m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.071 16.071l.707.707M7.929 7.929l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                    <svg id="theme-icon-light" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
                <div class="h-6 w-px bg-black/10 dark:bg-white/10 mx-2"></div>
                <a href="{{ route('larai.dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-white transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back to Overview</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 pb-20">
        <!-- Header -->
        <header class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6 reveal">
            <div>
                <h2 class="text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-widest text-xs mb-2">History Management</h2>
                <h1 class="text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white">Log <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-500 to-purple-500 dark:from-brand-400 dark:to-purple-400">Explorer</span></h1>
            </div>
            
            <!-- Management Bar -->
            <form action="{{ route('larai.logs') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <!-- Search -->
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search model, provider..." 
                        class="glass dark:bg-white/5 px-10 py-2.5 rounded-xl border-black/10 dark:border-white/10 outline-none w-64 text-sm focus:ring-2 focus:ring-brand-500/50 transition-all">
                    <svg class="w-4 h-4 absolute left-4 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <!-- Provider Filter -->
                <select name="provider" onchange="this.form.submit()" class="glass dark:bg-white/5 px-4 py-2.5 rounded-xl border-black/10 dark:border-white/10 outline-none text-sm font-semibold">
                    <option value="all">All Providers</option>
                    @foreach($providers as $p)
                        <option value="{{ $p }}" {{ request('provider') == $p ? 'selected' : '' }}>{{ strtoupper($p) }}</option>
                    @endforeach
                </select>

                <!-- Export -->
                <div class="relative group z-40">
                    <button type="button" class="glass px-5 py-2.5 rounded-xl border-black/10 dark:border-white/10 text-sm font-semibold hover:bg-black/5 dark:hover:bg-white/5 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Export</span>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 glass rounded-2xl border border-black/5 dark:border-white/5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                        <a href="{{ route('larai.export', ['format' => 'json']) }}" class="block px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-black/5 dark:hover:bg-white/5">EXPORT AS JSON</a>
                        <a href="{{ route('larai.export', ['format' => 'csv']) }}" class="block px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-black/5 dark:hover:bg-white/5 border-t border-black/5 dark:border-white/5">EXPORT AS CSV</a>
                        <a href="{{ route('larai.export', ['format' => 'txt']) }}" class="block px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-black/5 dark:hover:bg-white/5 border-t border-black/5 dark:border-white/5">EXPORT AS TXT</a>
                    </div>
                </div>
            </form>
        </header>

        <!-- Logs Table -->
        <section class="reveal">
            <div class="glass rounded-[2.5rem] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="bg-black/[0.02] dark:bg-white/[0.02]">
                                <th class="px-8 py-5 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px]">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1">
                                        Reference
                                        <svg class="w-3 h-3 {{ request('sort') == 'id' ? 'text-brand-500' : 'text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    </a>
                                </th>
                                <th class="px-8 py-5 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px]">Identity & Engine</th>
                                <th class="px-8 py-5 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px]">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_tokens', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1">
                                        Computation
                                        <svg class="w-3 h-3 {{ request('sort') == 'total_tokens' ? 'text-brand-500' : 'text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    </a>
                                </th>
                                <th class="px-8 py-5 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px]">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'cost_usd', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1">
                                        Resource Burn
                                        <svg class="w-3 h-3 {{ request('sort') == 'cost_usd' ? 'text-brand-500' : 'text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    </a>
                                </th>
                                <th class="px-8 py-5 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px] text-right">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center justify-end gap-1">
                                        Timestamp
                                        <svg class="w-3 h-3 {{ request('sort', 'created_at') == 'created_at' ? 'text-brand-500' : 'text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.03] dark:divide-white/[0.03]">
                            @forelse($logs as $log)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-8 py-5">
                                    <span class="font-mono text-brand-600 dark:text-brand-400 font-bold">#{{ $log->id }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-black/5 dark:bg-white/5 flex items-center justify-center font-bold text-[10px] text-slate-500 dark:text-slate-400">
                                            {{ substr($log->model, 0, 2) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $log->model }}</span>
                                            <span class="text-[10px] text-slate-500 font-medium tracking-tight uppercase">{{ $log->provider }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-slate-800 dark:text-slate-200 font-bold">{{ number_format($log->total_tokens) }} <span class="text-[10px] text-slate-500">TKNS</span></span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-600">I:{{ number_format($log->prompt_tokens) }} / O:{{ number_format($log->completion_tokens) }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold border border-emerald-500/20 tabular-nums">
                                        ${{ number_format($log->cost_usd, 5) }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <span class="text-slate-500 text-xs font-medium block">{{ $log->created_at->format('Y-m-d H:i') }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-600">{{ $log->created_at->diffForHumans() }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-20 text-center text-slate-500 font-bold tracking-widest uppercase text-xs">No matching execution records</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($logs->hasPages())
                <div class="px-8 py-6 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                    <p class="text-xs text-slate-500 font-medium">
                        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} executions
                    </p>
                    <div class="flex gap-2">
                        @if ($logs->onFirstPage())
                            <span class="pagination-link opacity-50 cursor-not-allowed">Previous</span>
                        @else
                            <a href="{{ $logs->previousPageUrl() }}" class="pagination-link">Previous</a>
                        @endif

                        @if ($logs->hasMorePages())
                            <a href="{{ $logs->nextPageUrl() }}" class="pagination-link">Next</a>
                        @else
                            <span class="pagination-link opacity-50 cursor-not-allowed">Next</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </section>
    </main>

    <script>
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
            window.location.reload();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const reveals = document.querySelectorAll('.reveal');
            reveals.forEach((el, i) => {
                setTimeout(() => el.classList.add('active'), 100 * i);
            });
        });
    </script>
</body>
</html>
