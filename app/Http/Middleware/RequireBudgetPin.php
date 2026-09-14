<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireBudgetPin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('budget_pin_ok') === true) {
            return $next($request);
        }

        return redirect()->route('pin.form');
    }
}
