<?php

namespace Gometap\LaraiTracker\Http\Controllers;

use Gometap\LaraiTracker\Models\LaraiSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\Factory as ViewFactory;

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

        $setupRequired = is_null($this->getPassword());

        $views = app(ViewFactory::class);

        return $views->file($views->getFinder()->find('larai::login'), compact('setupRequired'));
    }

    /**
     * Handle login / initial setup.
     */
    public function login(Request $request)
    {
        $password = $this->getPassword();

        // Initial setup: no password exists yet → set one
        if (is_null($password)) {
            if (! app()->environment('local')) {
                $setupToken = config('larai-tracker.setup_token');

                if (! is_string($setupToken) || strlen($setupToken) < 16) {
                    abort(403, 'Larai Tracker setup is disabled until LARAI_TRACKER_SETUP_TOKEN is configured.');
                }

                $request->validate([
                    'setup_token' => ['required', 'string'],
                ]);

                if (! hash_equals($setupToken, (string) $request->input('setup_token'))) {
                    return back()->withErrors([
                        'setup_token' => 'The setup token is invalid.',
                    ])->withInput($request->except(['password', 'password_confirmation', 'setup_token']));
                }
            }

            $request->validate([
                'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
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
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('larai.auth.login');
    }

    /**
     * Verify a plain password against the stored password.
     */
    protected function verifyPassword(string $input, string $stored): bool
    {
        // If stored password is hashed (from DB)
        if (str_starts_with($stored, '$2') || str_starts_with($stored, '$argon2')) {
            return Hash::check($input, $stored);
        }

        // Plain text password (from config/env)
        return hash_equals($stored, $input);
    }

    /**
     * Get the effective password (DB > ENV > Config).
     */
    protected function getPassword(): ?string
    {
        try {
            $dbPassword = LaraiSetting::get('dashboard_password');
            if (! is_null($dbPassword) && $dbPassword !== '') {
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
        $request->session()->regenerate();
        $request->session()->put('larai_authenticated', true);
        $request->session()->put('larai_auth_time', time());
    }

    /**
     * Check if the current session is authenticated.
     */
    protected function isAuthenticated(Request $request): bool
    {
        if (! $request->session()->has('larai_authenticated')) {
            return false;
        }

        $authTime = $request->session()->get('larai_auth_time', 0);
        $lifetime = config('larai-tracker.session_lifetime', 120) * 60;

        return (time() - $authTime) <= $lifetime;
    }
}
