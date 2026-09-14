<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PinController extends Controller
{
    public function form(): View
    {
        return view('auth.pin');
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate(['pin' => ['required', 'string', 'max:32']]);

        if (! hash_equals((string) env('APP_PIN', ''), $data['pin'])) {
            return back()->withErrors(['pin' => 'PIN tidak sesuai.']);
        }

        $request->session()->regenerate();
        $request->session()->put('budget_pin_ok', true);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pin.form');
    }
}
