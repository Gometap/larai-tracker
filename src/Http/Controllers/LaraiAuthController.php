<?php

namespace Gometap\LaraiTracker\Http\Controllers;

use Gometap\LaraiTracker\Models\LaraiSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class LaraiAuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(Request $request)
    {
        // Already authenticated → redirect to dashboard
        if ($this->isAuthenticated($request)) {
            return redirect()->route('larai.dashboard');
        }

        $setupRequired = $request->session()->get('setup_required', false);
        $throttleKey   = $this->throttleKey($request);
        $isLocked      = RateLimiter::tooManyAttempts($throttleKey, config('larai-tracker.max_attempts', 5));
        $secondsLeft   = $isLocked ? RateLimiter::availableIn($throttleKey) : 0;

        return view('larai::login', compact('setupRequired', 'isLocked', 'secondsLeft'));
    }

    /**
     * Handle login / initial setup.
     */
    public function login(Request $request)
    {
        $throttleKey = $this->throttleKey($request);
        $maxAttempts = config('larai-tracker.max_attempts', 5);
        $lockoutMins = config('larai-tracker.lockout_minutes', 15);
        $lockoutSecs = $lockoutMins * 60;

        // Check if locked out
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            return back()->withErrors([
                'password' => "Too many failed attempts. Please try again in {$minutes} minute(s).",
            ]);
        }

        $password = $this->getPassword();

        // Initial setup: no password exists yet → set one
        if (is_null($password)) {
            $request->validate([
                'password' => 'required|min:6|confirmed',
            ]);

            LaraiSetting::set('dashboard_password', Hash::make($request->input('password')));

            $this->authenticate($request);
            RateLimiter::clear($throttleKey);

            return redirect()->route('larai.dashboard')
                ->with('success', 'Password has been set successfully!');
        }

        // Normal login
        $request->validate([
            'password' => 'required',
        ]);

        if ($this->verifyPassword($request->input('password'), $password)) {
            // Clear rate limiter on successful login
            RateLimiter::clear($throttleKey);
            $this->authenticate($request);

            return redirect()->route('larai.dashboard');
        }

        // Increment the rate limiter for failed attempts
        RateLimiter::hit($throttleKey, $lockoutSecs);

        $remaining = $maxAttempts - RateLimiter::attempts($throttleKey);

        if ($remaining <= 0) {
            $minutes = $lockoutMins;
            return back()->withErrors([
                'password' => "Too many failed attempts. Please try again in {$minutes} minute(s).",
            ]);
        }

        return back()->withErrors([
            'password' => "The password is incorrect. {$remaining} attempt(s) remaining.",
        ]);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        $request->session()->forget(['larai_authenticated', 'larai_auth_time']);

        return redirect()->route('larai.auth.login');
    }

    /**
     * Generate a unique throttle key per IP.
     */
    protected function throttleKey(Request $request): string
    {
        return 'larai_login|' . $request->ip();
    }

    /**
     * Verify a plain password against the stored password.
     */
    protected function verifyPassword(string $input, string $stored): bool
    {
        // If stored password is hashed (from DB)
        if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$')) {
            return Hash::check($input, $stored);
        }

        // Plain text password (from config/env)
        return $input === $stored;
    }

    /**
     * Get the effective password (DB > ENV > Config).
     */
    protected function getPassword(): ?string
    {
        try {
            $dbPassword = LaraiSetting::get('dashboard_password');
            if (!is_null($dbPassword) && $dbPassword !== '') {
                return $dbPassword;
            }
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        return config('larai-tracker.password');
    }

    /**
     * Mark the session as authenticated.
     */
    protected function authenticate(Request $request): void
    {
        $request->session()->put('larai_authenticated', true);
        $request->session()->put('larai_auth_time', time());
    }

    /**
     * Check if the current session is authenticated.
     */
    protected function isAuthenticated(Request $request): bool
    {
        if (!$request->session()->has('larai_authenticated')) {
            return false;
        }

        $authTime = $request->session()->get('larai_auth_time', 0);
        $lifetime = config('larai-tracker.session_lifetime', 120) * 60;

        return (time() - $authTime) <= $lifetime;
    }
}
