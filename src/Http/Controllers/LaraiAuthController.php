<?php

namespace Gometap\LaraiTracker\Http\Controllers;

use Gometap\LaraiTracker\Models\LaraiSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

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

        return view('larai::login', compact('setupRequired'));
    }

    /**
     * Handle login / initial setup.
     */
    public function login(Request $request)
    {
        $password = $this->getPassword();

        // Initial setup: no password exists yet → set one
        if (is_null($password)) {
            $request->validate([
                'password' => 'required|min:6|confirmed',
            ]);

            LaraiSetting::set('dashboard_password', Hash::make($request->input('password')));

            $this->authenticate($request);

            return redirect()->route('larai.dashboard')
                ->with('success', 'Password has been set successfully!');
        }

        // Normal login
        $request->validate([
            'password' => 'required',
        ]);

        if ($this->verifyPassword($request->input('password'), $password)) {
            $this->authenticate($request);

            return redirect()->route('larai.dashboard');
        }

        return back()->withErrors([
            'password' => 'The password is incorrect.',
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
