<?php

namespace App\Http\Controllers;

use App\Models\BudgetProfile;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\CycleService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, CycleService $cycles): View
    {
        $cycle = $cycles->resolve($request->query('cycle'));

        $profiles = BudgetProfile::query()->orderBy('id')->get();
        $activeId = (int) Setting::getValue('active_profile_id', (string) optional($profiles->first())->id);
        $profile = $profiles->firstWhere('id', $activeId) ?? $profiles->firstOrFail();

        $transactions = Transaction::query()
            ->where('transaction_date', '>=', $cycle['start']->toDateString())
            ->where('transaction_date', '<', $cycle['end_exclusive']->toDateString())
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $spent = $transactions->groupBy('category')->map->sum('amount');

        $summary = collect($profile->budgets())->map(function (array $item, string $key) use ($spent) {
            $realization = (int) ($spent[$key] ?? 0);
            return [
                'key' => $key,
                'label' => $item['label'],
                'budget' => (int) $item['budget'],
                'realization' => $realization,
                'remaining' => (int) $item['budget'] - $realization,
                'percent' => $item['budget'] > 0 ? min(100, round(($realization / $item['budget']) * 100)) : 0,
            ];
        })->values();

        return view('dashboard', [
            'cycle' => $cycle,
            'cycleChoices' => $cycles->choices(),
            'profiles' => $profiles,
            'profile' => $profile,
            'summary' => $summary,
            'transactions' => $transactions,
        ]);
    }
}
