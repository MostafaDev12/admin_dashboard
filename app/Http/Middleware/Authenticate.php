<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */

     public function handle($request, Closure $next, ...$guards)
     {
         // Your custom logic here
    //   if (!is_null(request()->user())) {
    //         return $next($request);
    //     } else {
    //         return redirect('login');
    //     }
      //   return parent::handle($request, $next, ...$guards);
     }
    // protected function redirectTo($request)
    // {
    //     if (! $request->expectsJson()) {
    //         return route('login');
    //     }
    // }




}
