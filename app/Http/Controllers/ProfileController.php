<?php

namespace App\Http\Controllers;

use App\Models\BudgetProfile;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $profiles = BudgetProfile::query()->orderBy('id')->get();
        $activeId = (int) Setting::getValue('active_profile_id', (string) optional($profiles->first())->id);

        return view('settings', compact('profiles', 'activeId'));
    }

    public function activate(Request $request): RedirectResponse
    {
        $data = $request->validate(['profile_id' => ['required', 'exists:budget_profiles,id']]);
        Setting::setValue('active_profile_id', (int) $data['profile_id']);

        return back()->with('success', 'Profil budget aktif diperbarui.');
    }

    public function update(Request $request, BudgetProfile $profile): RedirectResponse
    {
        $fields = ['thp', 'kos', 'operasional', 'allianz', 'dana_darurat', 'bmri', 'emas', 'uang_bebas'];
        $rules = array_fill_keys($fields, ['required', 'integer', 'min:0']);
        $data = $request->validate($rules);

        $profile->update($data);

        return back()->with('success', "{$profile->name} berhasil diperbarui.");
    }
}
