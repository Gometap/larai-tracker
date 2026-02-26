<?php

namespace Gometap\LaraiTracker\Http\Middleware;

use Closure;
use Gometap\LaraiTracker\Models\LaraiSetting;
use Illuminate\Http\Request;

class LaraiAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $password = $this->getPassword();

        // No password set and running locally → allow access
        if (is_null($password) && app()->environment('local')) {
            return $next($request);
        }

        // No password configured at all (non-local) → force setup
        if (is_null($password)) {
            return redirect()->route('larai.auth.login')
                ->with('setup_required', true);
        }

        // Already authenticated via session
        if ($this->isAuthenticated($request)) {
            return $next($request);
        }

        // Not authenticated → redirect to login
        return redirect()->route('larai.auth.login');
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
     * Check if the current session is authenticated.
     */
    protected function isAuthenticated(Request $request): bool
    {
        if (!$request->session()->has('larai_authenticated')) {
            return false;
        }

        $authTime = $request->session()->get('larai_auth_time', 0);
        $lifetime = config('larai-tracker.session_lifetime', 120) * 60; // to seconds

        if (time() - $authTime > $lifetime) {
            $request->session()->forget(['larai_authenticated', 'larai_auth_time']);
            return false;
        }

        return true;
    }
}
