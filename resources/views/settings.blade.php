<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Larai Tracker | Configuration</title>
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
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
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
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(0, 0, 0, 0.05); box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); transition: all 0.3s ease; position: relative; }
        .dark .glass { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.05); box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); }
        .reveal { opacity: 0; transform: translateY(20px); transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body class="min-h-screen font-sans selection:bg-brand-500 selection:text-white">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 px-6 py-4 glass mb-8 border-b border-black/5 dark:border-white/5">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('larai.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl overflow-hidden flex items-center justify-center shadow-lg shadow-brand-500/20 group-hover:scale-105 transition-transform">
                        <img src="https://doq9otz3zrcmp.cloudfront.net/blogs/1_1771417079_rJ7ATPHw.png" alt="Larai Tracker" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-tight">Larai<span class="text-brand-500">Tracker</span></span>
                        <span class="block text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold">Configuration</span>
                    </div>
                </a>
            </div>
            <!-- Mobile hamburger -->
            <button id="mobileMenuBtn" class="md:hidden w-10 h-10 glass rounded-xl flex items-center justify-center hover:bg-black/5 dark:hover:bg-white/5 transition-all text-slate-500 dark:text-slate-400" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <div class="hidden md:flex items-center gap-3 text-sm font-medium">
                <button onclick="toggleTheme()" class="w-10 h-10 glass rounded-xl flex items-center justify-center hover:bg-black/5 dark:hover:bg-white/5 transition-all text-slate-500 dark:text-slate-400">
                    <svg id="theme-icon-dark" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1m-16 0h-1m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.071 16.071l.707.707M7.929 7.929l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                    <svg id="theme-icon-light" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
                <div class="h-6 w-px bg-black/10 dark:bg-white/10"></div>
                <a href="{{ route('larai.auth.logout') }}" class="w-10 h-10 glass rounded-xl flex items-center justify-center hover:bg-red-500/10 transition-all text-slate-500 dark:text-slate-400 hover:text-red-500" title="Sign Out">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </a>
            </div>
        </div>
        <!-- Mobile dropdown -->
        <div id="mobileMenu" class="hidden md:hidden border-t border-black/5 dark:border-white/5 px-6 py-4 flex flex-col gap-4">
            <a href="{{ route('larai.dashboard') }}" class="text-slate-600 dark:text-slate-400 font-semibold text-sm">Overview</a>
            <a href="{{ route('larai.logs') }}" class="text-slate-600 dark:text-slate-400 font-semibold text-sm">Logs</a>
            <a href="{{ route('larai.auth.logout') }}" class="text-red-500 font-semibold text-sm">Sign Out</a>
        </div>
    </nav>

    @if(session('success'))
    <div id="toast" class="fixed bottom-6 right-6 z-[200] glass px-6 py-4 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-sm shadow-xl flex items-center gap-3" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div id="toast" class="fixed bottom-6 right-6 z-[200] glass px-6 py-4 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-600 dark:text-red-400 font-bold text-sm shadow-xl flex items-center gap-3" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif
    @if(session('password_success'))
    <div id="toast" class="fixed bottom-6 right-6 z-[200] glass px-6 py-4 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-sm shadow-xl flex items-center gap-3" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('password_success') }}</span>
    </div>
    @endif
    @if(session('password_error'))
    <div id="toast" class="fixed bottom-6 right-6 z-[200] glass px-6 py-4 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-600 dark:text-red-400 font-bold text-sm shadow-xl flex items-center gap-3" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('password_error') }}</span>
    </div>
    @endif

    <main class="max-w-7xl mx-auto px-6 pb-20">
        <form action="{{ route('larai.settings.update') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left: Settings & Budget -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- General Settings -->
                    <section class="glass p-8 rounded-[2.5rem] reveal active">
                        <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2 2 2 0 012 2v.657M7 20h11a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v11a2 2 0 002 2z"/></svg>
                            General Settings
                        </h3>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Currency Code (e.g. USD, VND)</label>
                                <input type="text" name="currency[code]" value="{{ $currency['code'] }}" class="w-full glass bg-transparent px-4 py-3 rounded-xl border-black/10 dark:border-white/10 outline-none focus:ring-2 focus:ring-brand-500/50">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Currency Symbol (e.g. $, ₫)</label>
                                <input type="text" name="currency[symbol]" value="{{ $currency['symbol'] }}" class="w-full glass bg-transparent px-4 py-3 rounded-xl border-black/10 dark:border-white/10 outline-none focus:ring-2 focus:ring-brand-500/50">
                            </div>
                        </div>
                    </section>

                    <!-- Monthly Budget Card -->
                    <section class="glass p-8 rounded-[2.5rem] reveal active">
                        <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Budget Alerts
                        </h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Monthly Limit ($)</label>
                                <input type="number" step="0.01" name="budget[amount]" value="{{ $budget->amount }}" class="w-full glass bg-transparent px-4 py-3 rounded-xl border-black/10 dark:border-white/10 outline-none focus:ring-2 focus:ring-brand-500/50">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Alert Threshold (%)</label>
                                <input type="number" name="budget[threshold]" value="{{ $budget->alert_threshold }}" class="w-full glass bg-transparent px-4 py-3 rounded-xl border-black/10 dark:border-white/10 outline-none focus:ring-2 focus:ring-brand-500/50">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Recipient Email</label>
                                <input type="email" name="budget[email]" value="{{ $budget->recipient_email }}" placeholder="alerts@example.com" class="w-full glass bg-transparent px-4 py-3 rounded-xl border-black/10 dark:border-white/10 outline-none focus:ring-2 focus:ring-brand-500/50">
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="budget[active]" id="budget_active" {{ $budget->is_active ? 'checked' : '' }} class="w-5 h-5 rounded-lg border-black/10 dark:border-white/10 text-brand-500 focus:ring-brand-500/50 bg-black/5 dark:bg-white/5">
                                <label for="budget_active" class="text-sm font-bold text-slate-700 dark:text-slate-300">Enable Monitoring</label>
                            </div>
                        </div>
                    </section>

                    <!-- Log Retention Section -->
                    <section class="glass p-8 rounded-[2.5rem] reveal active">
                        <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Log Retention
                        </h3>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Auto-delete logs older than</label>
                            <select name="log_retention_days" class="w-full glass bg-transparent px-4 py-3 rounded-xl border-black/10 dark:border-white/10 outline-none focus:ring-2 focus:ring-brand-500/50 text-sm font-semibold">
                                <option value="0" {{ $logRetentionDays == 0 ? 'selected' : '' }}>Never (keep all)</option>
                                <option value="30" {{ $logRetentionDays == 30 ? 'selected' : '' }}>30 days</option>
                                <option value="60" {{ $logRetentionDays == 60 ? 'selected' : '' }}>60 days</option>
                                <option value="90" {{ $logRetentionDays == 90 ? 'selected' : '' }}>90 days</option>
                                <option value="180" {{ $logRetentionDays == 180 ? 'selected' : '' }}>180 days</option>
                                <option value="365" {{ $logRetentionDays == 365 ? 'selected' : '' }}>1 year</option>
                            </select>
                            <p class="text-[10px] text-slate-400 dark:text-slate-600 mt-2">Cleanup runs automatically once per day.</p>
                        </div>
                    </section>

                    <!-- Sync Tool -->
                    <section class="glass p-8 rounded-[2.5rem] bg-gradient-to-br from-brand-500/10 to-transparent border-brand-500/20 reveal active">
                        <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Auto Sync
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">Fetch the latest unit prices for all AI models from Gometap's central price registry.</p>
                        <button type="submit" formaction="{{ route('larai.sync-prices') }}" class="w-full py-3 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-brand-500/20 active:scale-95">Sync Global Prices</button>
                    </section>

                    <!-- Security Section -->
                    <section class="glass p-8 rounded-[2.5rem] reveal active">
                        <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Security
                        </h3>

                        @if(session('password_success'))
                            {{-- shown as toast --}}
                        @endif
                        @if(session('password_error'))
                            {{-- shown as toast --}}
                        @endif

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Current Password</label>
                                <input type="password" name="security[current_password]" placeholder="••••••••" class="w-full glass bg-transparent px-4 py-3 rounded-xl border-black/10 dark:border-white/10 outline-none focus:ring-2 focus:ring-brand-500/50">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">New Password</label>
                                <input type="password" name="security[new_password]" placeholder="Min 6 characters" class="w-full glass bg-transparent px-4 py-3 rounded-xl border-black/10 dark:border-white/10 outline-none focus:ring-2 focus:ring-brand-500/50">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Confirm New Password</label>
                                <input type="password" name="security[new_password_confirmation]" placeholder="••••••••" class="w-full glass bg-transparent px-4 py-3 rounded-xl border-black/10 dark:border-white/10 outline-none focus:ring-2 focus:ring-brand-500/50">
                            </div>
                            <p class="text-[10px] text-slate-400 dark:text-slate-600 leading-relaxed">
                                Leave blank if you don't want to change the password. Password set here takes priority over ENV/config.
                            </p>
                        </div>
                    </section>
                </div>

                <!-- Right: Model Prices -->
                <div class="lg:col-span-2">
                    <section class="glass rounded-[2.5rem] overflow-hidden reveal active">
                        <div class="px-8 py-6 border-b border-black/5 dark:border-white/5 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.01]">
                            <h3 class="text-xl font-bold">Model Price Configuration</h3>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="addPriceRow()" class="px-4 py-2 bg-brand-600/10 text-brand-600 dark:text-brand-400 font-bold rounded-xl text-sm hover:bg-brand-600/20 transition-all flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add
                                </button>
                                <button type="submit" class="px-6 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold rounded-xl text-sm hover:scale-105 transition-all">Save Overrides</button>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="bg-black/[0.02] dark:bg-white/[0.02]">
                                        <th class="px-8 py-4 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px]">Model & Provider</th>
                                        <th class="px-8 py-4 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px]">Input ($/1M)</th>
                                        <th class="px-8 py-4 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px]">Output ($/1M)</th>
                                        <th class="px-8 py-4 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px]">Status</th>
                                        <th class="px-8 py-4 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-tighter text-[10px] text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.03] dark:divide-white/[0.03]">
                                    @forelse($customPrices as $price)
                                    <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors">
                                        <td class="px-8 py-5">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-900 dark:text-white">{{ $price->model }}</span>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $price->provider }}</span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <input type="number" step="0.0001" name="prices[{{ $price->id }}][input]" value="{{ (float)$price->input_price_per_1m }}" class="w-32 glass bg-transparent px-3 py-1.5 rounded-lg border-black/10 dark:border-white/10 outline-none text-xs font-mono">
                                        </td>
                                        <td class="px-8 py-5">
                                            <input type="number" step="0.0001" name="prices[{{ $price->id }}][output]" value="{{ (float)$price->output_price_per_1m }}" class="w-32 glass bg-transparent px-3 py-1.5 rounded-lg border-black/10 dark:border-white/10 outline-none text-xs font-mono">
                                        </td>
                                        <td class="px-8 py-5">
                                            @if($price->is_custom)
                                                <span class="text-[10px] bg-purple-500/10 text-purple-500 px-2 py-1 rounded font-bold uppercase tracking-widest">Manual</span>
                                            @else
                                                <span class="text-[10px] bg-emerald-500/10 text-emerald-500 px-2 py-1 rounded font-bold uppercase tracking-widest">Synced</span>
                                            @endif
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <form method="POST" action="{{ route('larai.prices.delete', $price->id) }}" onsubmit="return confirm('Delete this price entry?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-600 transition-colors" title="Delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="empty-row">
                                        <td colspan="5" class="py-20 text-center text-slate-500 font-bold tracking-widest uppercase text-xs">No models found. Click sync to fetch defaults.</td>
                                    </tr>
                                    @endforelse
                                    <!-- New price rows inserted here by JS -->
                                    <tbody id="new-price-rows"></tbody>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </main>

    <script>
        let newRowIndex = 0;

        function addPriceRow() {
            const emptyRow = document.getElementById('empty-row');
            if (emptyRow) emptyRow.style.display = 'none';

            const tbody = document.getElementById('new-price-rows');
            const i = newRowIndex++;
            const row = document.createElement('tr');
            row.className = 'hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors bg-brand-500/5';
            row.innerHTML = `
                <td class="px-8 py-4">
                    <div class="flex flex-col gap-1">
                        <input type="text" name="new_prices[${i}][provider]" placeholder="openai" required class="w-full glass bg-transparent px-3 py-1.5 rounded-lg border-black/10 dark:border-white/10 outline-none text-xs font-mono focus:ring-1 focus:ring-brand-500/50">
                        <input type="text" name="new_prices[${i}][model]" placeholder="model-name" required class="w-full glass bg-transparent px-3 py-1.5 rounded-lg border-black/10 dark:border-white/10 outline-none text-xs font-mono focus:ring-1 focus:ring-brand-500/50">
                    </div>
                </td>
                <td class="px-8 py-4">
                    <input type="number" step="0.0001" name="new_prices[${i}][input]" value="0" class="w-32 glass bg-transparent px-3 py-1.5 rounded-lg border-black/10 dark:border-white/10 outline-none text-xs font-mono">
                </td>
                <td class="px-8 py-4">
                    <input type="number" step="0.0001" name="new_prices[${i}][output]" value="0" class="w-32 glass bg-transparent px-3 py-1.5 rounded-lg border-black/10 dark:border-white/10 outline-none text-xs font-mono">
                </td>
                <td class="px-8 py-4">
                    <span class="text-[10px] bg-brand-500/10 text-brand-500 px-2 py-1 rounded font-bold uppercase tracking-widest">New</span>
                </td>
                <td class="px-8 py-4 text-right">
                    <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-600 transition-colors" title="Remove">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        }

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const reveals = document.querySelectorAll('.reveal');
            reveals.forEach((el, i) => {
                setTimeout(() => el.classList.add('active'), 100 * i);
            });

            const toast = document.getElementById('toast');
            if (toast) {
                setTimeout(() => {
                    toast.style.transition = 'opacity 0.5s ease';
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 500);
                }, 4000);
            }
        });
    </script>
</body>
</html>
