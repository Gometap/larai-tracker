<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Larai Tracker | Sign In</title>
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
            backdrop-filter: blur(16px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        }
        .dark .glass {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37);
        }
        .login-card {
            animation: slideUp 0.6s cubic-bezier(0.22, 1, 0.36, 1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        .orb-1 { animation: float 8s ease-in-out infinite; }
        .orb-2 { animation: float 10s ease-in-out infinite reverse; }
        .orb-3 { animation: float 12s ease-in-out infinite 2s; }
        input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }
    </style>
</head>
<body class="min-h-screen font-sans selection:bg-brand-500 selection:text-white flex items-center justify-center relative overflow-hidden">

    <!-- Background Orbs -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="orb-1 absolute -top-20 -left-20 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl"></div>
        <div class="orb-2 absolute top-1/2 -right-32 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl"></div>
        <div class="orb-3 absolute -bottom-20 left-1/3 w-80 h-80 bg-pink-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="login-card w-full max-w-md mx-4 relative z-10">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl overflow-hidden shadow-xl shadow-brand-500/20 mb-6">
                <img src="https://doq9otz3zrcmp.cloudfront.net/blogs/1_1771417079_rJ7ATPHw.png" alt="Larai Tracker" class="w-full h-full object-cover">
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Larai<span class="text-brand-500">Tracker</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 font-medium">
                @if($setupRequired ?? false)
                    Set up your dashboard password to get started
                @else
                    Enter your password to access the dashboard
                @endif
            </p>
        </div>

        <!-- Login Card -->
        <div class="glass rounded-[2rem] p-8">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if($isLocked ?? false)
                <div class="mb-6 p-4 rounded-xl bg-orange-500/10 border border-orange-500/20 text-orange-700 dark:text-orange-400 text-sm font-medium">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-bold">Account Temporarily Locked</span>
                    </div>
                    <p class="ml-7 text-xs opacity-80">Too many failed attempts. Try again in <span id="lockout-countdown" class="font-bold">{{ $secondsLeft ?? 0 }}</span>s.</p>
                </div>
            @elseif($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-700 dark:text-red-400 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('larai.auth.login.submit') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                        @if($setupRequired ?? false)
                            New Password
                        @else
                            Password
                        @endif
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            autofocus
                            placeholder="••••••••"
                            class="w-full pl-12 pr-4 py-3.5 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 text-slate-900 dark:text-white placeholder-slate-400 font-medium transition-all"
                        >
                    </div>
                </div>

                @if($setupRequired ?? false)
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                        Confirm Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            required
                            placeholder="••••••••"
                            class="w-full pl-12 pr-4 py-3.5 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 text-slate-900 dark:text-white placeholder-slate-400 font-medium transition-all"
                        >
                    </div>
                </div>
                @endif

                <button
                    type="submit"
                    @if($isLocked ?? false) disabled @endif
                    class="w-full py-3.5 rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 text-white font-bold text-sm uppercase tracking-widest shadow-lg shadow-brand-500/30 hover:shadow-brand-500/50 transition-all duration-300 active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed disabled:pointer-events-none"
                >
                    @if($isLocked ?? false)
                        🔒 Account Locked
                    @elseif($setupRequired ?? false)
                        Set Password & Continue
                    @else
                        Access Dashboard
                    @endif
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-xs text-slate-400 dark:text-slate-600 font-medium">
                &copy; {{ date('Y') }} Gometap Group · Larai Tracker
            </p>
        </div>

        <!-- Theme Toggle -->
        <div class="fixed top-6 right-6">
            <button onclick="toggleTheme()" class="glass w-10 h-10 rounded-xl flex items-center justify-center hover:bg-black/5 dark:hover:bg-white/5 transition-all text-slate-500 dark:text-slate-400">
                <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1m-16 0h-1m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.071 16.071l.707.707M7.929 7.929l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </button>
        </div>
    </div>

    <script>
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }

        // Countdown timer for lockout
        const countdownEl = document.getElementById('lockout-countdown');
        if (countdownEl) {
            let seconds = parseInt(countdownEl.textContent, 10);
            const interval = setInterval(() => {
                seconds--;
                if (seconds <= 0) {
                    clearInterval(interval);
                    window.location.reload();
                } else {
                    countdownEl.textContent = seconds;
                }
            }, 1000);
        }
    </script>
</body>
</html>
