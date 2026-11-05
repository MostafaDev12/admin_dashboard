<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin
{
    public function handle($request, Closure $next, $guard = 'admin')
    {
        if (! Auth::guard($guard)->check()) {
            return redirect()->route('admin.login');
        }

        Auth::shouldUse($guard);
        $admin = Auth::guard($guard)->user();
        if ($request->hasSession()) {
            // Ends other sessions as soon as the account's password changes.
            $key = 'admin_session_'.$admin->getAuthIdentifier();
            $known = $request->session()->get($key);
            if ($known !== null && ! hash_equals($known, $admin->sessionFingerprint())) {
                Auth::guard($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login');
            }
            $request->session()->put($key, $admin->sessionFingerprint());
        }

        return $next($request);
    }
}
