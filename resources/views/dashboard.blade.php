<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Larai Tracker | AI Performance Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            50: '#f0f4ff',
                            100: '#e0e9ff',
                            200: '#c0d2ff',
                            300: '#a0bcff',
                            400: '#7094ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            background-color: #f8fafc; 
            color: #0f172a;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .dark body {
            background-color: #020617; 
            color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, hsla(215,98%,61%,0.07) 0px, transparent 50%),
                radial-gradient(at 100% 0%, hsla(263,70%,50%,0.07) 0px, transparent 50%);
        }
        .glass { 
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            transition: all 0.3s ease;
        }
        .dark .glass { 
            background: rgba(15, 23, 42, 0.6); 
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .animate-subtle { animation: subtle-float 6s ease-in-out infinite; }
        @keyframes subtle-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .reveal { opacity: 0; transform: translateY(20px); transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body class="min-h-screen font-sans selection:bg-brand-500 selection:text-white">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 px-6 py-4 glass mb-8 border-b border-black/5 dark:border-white/5">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-tr from-brand-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-brand-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight">Larai<span class="text-brand-500">Tracker</span></span>
                    <span class="block text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold">Analytics Tool</span>
                </div>
            </div>
            <div class="flex items-center gap-6 text-sm font-medium">
                 <a href="https://github.com/gometap/larai-tracker" class="text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-white transition-colors cursor-pointer">Github</a>
                 <a href="https://github.com/gometap/larai-tracker/blob/main/CHANGELOG.md" class="text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-white transition-colors cursor-pointer">Changelog</a>
                
                <!-- Theme Toggle -->
                <button onclick="toggleTheme()" class="w-10 h-10 glass rounded-xl flex items-center justify-center hover:bg-black/5 dark:hover:bg-white/5 transition-all text-slate-500 dark:text-slate-400">
                    <svg id="theme-icon-dark" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1m-16 0h-1m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.071 16.071l.707.707M7.929 7.929l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                    <svg id="theme-icon-light" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>

                <div class="h-6 w-px bg-black/10 dark:bg-white/10 mx-2"></div>
                <div class="flex items-center gap-2 glass px-4 py-2 rounded-full border-black/10 dark:border-white/10">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-slate-600 dark:text-slate-300">Live Services</span>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 pb-20">
        <!-- Dashboard Header -->
        <header class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6 reveal" id="header-reveal">
            <div>
                <h2 class="text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-widest text-xs mb-2">Overview</h2>
                <h1 class="text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white">AI Resource <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-500 to-purple-500 dark:from-brand-400 dark:to-purple-400">Hub</span></h1>
            </div>
            <div class="flex gap-3">
                <div class="relative group">
                    <button class="glass px-5 py-2.5 rounded-xl border-black/10 dark:border-white/10 text-sm font-semibold hover:bg-black/5 dark:hover:bg-white/5 transition-all flex items-center gap-2">
                        <span>Export Data</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 glass rounded-2xl border border-black/5 dark:border-white/5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                        <a href="{{ route('larai.export', 'json') }}" class="block px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">EXPORT AS JSON</a>
                        <a href="{{ route('larai.export', 'csv') }}" class="block px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-black/5 dark:hover:bg-white/5 transition-colors border-t border-black/5 dark:border-white/5">EXPORT AS CSV</a>
                        <a href="{{ route('larai.export', 'txt') }}" class="block px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-black/5 dark:hover:bg-white/5 transition-colors border-t border-black/5 dark:border-white/5">EXPORT AS TXT</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Stats Grid -->
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Total Cost Card -->
            <div class="glass p-6 rounded-[2rem] relative overflow-hidden group hover:border-brand-500/30 transition-all duration-500 reveal" style="transition-delay: 100ms">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-brand-500/10 rounded-full blur-2xl group-hover:bg-brand-500/20 transition-all"></div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-brand-500/10 rounded-2xl flex items-center justify-center text-brand-500 dark:text-brand-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-500 bg-emerald-500/10 px-2 py-1 rounded-lg uppercase tracking-wider">Overall</span>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Total Investment</h3>
                <p class="text-3xl font-extrabold text-slate-900 dark:text-white tabular-nums">${{ number_format($stats['total_cost'], 4) }}</p>
                <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                    <span class="text-emerald-500 font-bold">↑ 12%</span>
                    <span>vs last month</span>
                </div>
            </div>

            <!-- Today's Cost -->
            <div class="glass p-6 rounded-[2rem] relative overflow-hidden group hover:border-purple-500/30 transition-all duration-500 reveal" style="transition-delay: 200ms">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition-all"></div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-500/10 rounded-2xl flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="text-[10px] font-bold text-brand-600 dark:text-brand-400 bg-brand-500/10 px-2 py-1 rounded-lg uppercase tracking-wider">Today</span>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Daily Burn Rate</h3>
                <p class="text-3xl font-extrabold text-slate-900 dark:text-white tabular-nums">${{ number_format($stats['today_cost'], 4) }}</p>
                <div class="mt-4 flex items-center gap-2 text-xs text-brand-600 dark:text-brand-400">
                    <span class="font-bold">Real-time</span>
                    <span class="text-slate-500">updating automatically</span>
                </div>
            </div>

            <!-- Tokens Used -->
            <div class="glass p-6 rounded-[2rem] relative overflow-hidden group hover:border-blue-500/30 transition-all duration-500 reveal" style="transition-delay: 300ms">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-all"></div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-500/10 rounded-2xl flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    </div>
                    <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-500/10 px-2 py-1 rounded-lg uppercase tracking-wider">Computation</span>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Total Tokens Parsed</h3>
                <p class="text-3xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($stats['total_tokens']) }}</p>
                <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                    <span class="text-blue-600 dark:text-blue-400 font-bold">~{{ number_format($stats['total_tokens'] / 0.75, 0) }}</span>
                    <span>estimated words</span>
                </div>
            </div>

            <!-- Active Models -->
            <div class="glass p-6 rounded-[2rem] relative overflow-hidden group hover:border-pink-500/30 transition-all duration-500 reveal" style="transition-delay: 400ms">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-pink-500/10 rounded-full blur-2xl group-hover:bg-pink-500/20 transition-all"></div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-pink-500/10 rounded-2xl flex items-center justify-center text-pink-600 dark:text-pink-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                    <span class="text-[10px] font-bold text-pink-600 dark:text-pink-400 bg-pink-500/10 px-2 py-1 rounded-lg uppercase tracking-wider">Models</span>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Architecture Depth</h3>
                <p class="text-3xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ $stats['costs_by_model']->count() }}</p>
                <div class="mt-4 flex items-center gap-2 text-xs text-pink-600 dark:text-pink-400">
                    <span class="font-bold">Scale</span>
                    <span class="text-slate-500">multimodal environment</span>
                </div>
            </div>
        </section>

        <!-- Insights -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <!-- Main Cost Chart -->
            <div class="lg:col-span-2 glass rounded-[2.5rem] p-8 reveal" style="transition-delay: 500ms">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-bold">Cost Distribution</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Timeline of API investment across your stack</p>
                    </div>
                    <select class="glass bg-transparent px-4 py-2 rounded-xl text-xs font-bold border-black/10 dark:border-white/10 outline-none text-slate-700 dark:text-slate-300">
                        <option>Last 30 Days</option>
                        <option>Last 7 Days</option>
                        <option>Custom Range</option>
                    </select>
                </div>
                <div class="h-[300px]">
                    <canvas id="costChart"></canvas>
                </div>
            </div>

            <!-- Model Usage -->
            <div class="glass rounded-[2.5rem] p-8 reveal" style="transition-delay: 600ms">
                 <h3 class="text-xl font-bold mb-8">Model Mix</h3>
                 <div class="h-[250px] flex items-center justify-center relative">
                    <canvas id="modelChart"></canvas>
                 </div>
                 <div class="mt-8 space-y-3">
                    @foreach($stats['costs_by_model'] as $model)
                    <div class="flex items-center justify-between text-xs transition-colors hover:bg-black/5 dark:hover:bg-white/5 p-2 rounded-lg">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full" style="background-color: {{ ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'][$loop->index % 5] }}"></span>
                            <span class="font-mono text-slate-600 dark:text-slate-400">{{ $model->model }}</span>
                        </div>
                        <span class="font-bold text-slate-900 dark:text-white">${{ number_format($model->cost, 4) }}</span>
                    </div>
                    @endforeach
                 </div>
            </div>
        </section>

        <!-- Logs Table -->
        <section class="reveal" style="transition-delay: 700ms">
            <div class="glass rounded-[2.5rem] overflow-hidden">
                <div class="px-8 py-6 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
                    <h3 class="text-xl font-bold">Live Execution Stream</h3>
                    <div class="flex items-center gap-2 text-[10px] text-slate-500 font-bold uppercase tracking-widest">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                        Listening for events
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="bg-black/[0.02] dark:bg-white/[0.02]">
                                <th class="px-8 py-4 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px]">Reference</th>
                                <th class="px-8 py-4 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px]">Identity</th>
                                <th class="px-8 py-4 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px]">Computation</th>
                                <th class="px-8 py-4 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px]">Resource Burn</th>
                                <th class="px-8 py-4 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px] text-right">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.03] dark:divide-white/[0.03]">
                            @forelse($stats['recent_logs'] as $log)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex flex-col">
                                        <span class="font-mono text-brand-600 dark:text-brand-400 font-bold">#{{ $log->id }}</span>
                                        <span class="text-[10px] text-slate-500 font-medium tracking-tight">{{ strtoupper($log->provider) }} ENGINE</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-black/5 dark:bg-white/5 flex items-center justify-center font-bold text-xs text-slate-500 dark:text-slate-400 text-slate-400">
                                            {{ substr($log->model, 0, 2) }}
                                        </div>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $log->model }}</span>
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
                                    <span class="text-slate-500 text-xs font-medium">{{ $log->created_at->format('H:i:s') }}</span>
                                    <span class="block text-[10px] text-slate-400 dark:text-slate-600">{{ $log->created_at->diffForHumans() }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-20 text-center text-slate-500 font-bold tracking-widest uppercase text-xs">No records detected in the current stream</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6 bg-black/[0.01] dark:bg-white/[0.01] text-center border-t border-black/5 dark:border-white/5">
                    <a href="{{ route('larai.logs') }}" class="text-brand-600 dark:text-brand-500 font-bold text-xs hover:text-brand-500 transition-colors">Load Full History Explorer →</a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="max-w-7xl mx-auto px-6 py-12 border-t border-black/5 dark:border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="text-slate-500 text-xs font-medium">
            &copy; {{ date('Y') }} Gometap Group. Built for Laravel AI Ecosystem.
        </div>
        <div class="flex gap-6">
             <a href="https://github.com/gometap/larai-tracker" class="text-slate-500 hover:text-brand-600 dark:hover:text-white transition-colors text-xs font-bold uppercase tracking-widest">Github</a>
             <a href="https://github.com/gometap/larai-tracker/blob/main/CHANGELOG.md" class="text-slate-500 hover:text-brand-600 dark:hover:text-white transition-colors text-xs font-bold uppercase tracking-widest">Changelog</a>
        </div>
    </footer>

    <script>
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
            window.location.reload(); // Reload to refresh chart colors easily
        }

        // Reveal Animations
        document.addEventListener('DOMContentLoaded', () => {
            const reveals = document.querySelectorAll('.reveal');
            reveals.forEach((el, i) => {
                setTimeout(() => el.classList.add('active'), 100 * i);
            });
        });

        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
        const textColor = isDark ? '#94a3b8' : '#64748b';

        // Charts Configuration
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? 'rgba(15, 23, 42, 0.9)' : 'rgba(255, 255, 255, 0.9)',
                    titleColor: isDark ? '#fff' : '#0f172a',
                    bodyColor: '#3b82f6',
                    borderColor: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 12
                }
            },
            scales: {
                y: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: textColor, font: { size: 10, weight: '600' } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: textColor, font: { size: 10, weight: '600' } }
                }
            }
        };

        // Cost Over Time Chart
        const costCtx = document.getElementById('costChart').getContext('2d');
        const costGradient = costCtx.createLinearGradient(0, 0, 0, 300);
        costGradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
        costGradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        new Chart(costCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($stats['costs_over_time']->pluck('date')) !!},
                datasets: [{
                    label: 'Invested',
                    data: {!! json_encode($stats['costs_over_time']->pluck('cost')) !!},
                    borderColor: '#3b82f6',
                    borderWidth: 3,
                    pointBackgroundColor: isDark ? '#020617' : '#fff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                    backgroundColor: costGradient,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: chartOptions
        });

        // Model Distribution Chart
        const modelCtx = document.getElementById('modelChart').getContext('2d');
        new Chart(modelCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($stats['costs_by_model']->pluck('model')) !!},
                datasets: [{
                    data: {!! json_encode($stats['costs_by_model']->pluck('cost')) !!},
                    backgroundColor: ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'],
                    hoverOffset: 20,
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
</body>
</html>
