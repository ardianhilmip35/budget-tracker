<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\CycleService;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function __invoke(CycleService $cycles): View
    {
        $firstDate = Transaction::query()->min('transaction_date');
        $lastDate = Transaction::query()->max('transaction_date');

        if (! $firstDate) {
            return view('history', ['rows' => collect()]);
        }

        $firstKey = $cycles->cycleKeyForDate(CarbonImmutable::parse($firstDate, 'Asia/Jakarta'));
        $lastKey = $cycles->cycleKeyForDate(CarbonImmutable::parse($lastDate, 'Asia/Jakarta'));

        $cursor = CarbonImmutable::createFromFormat('Y-m-d', $firstKey.'-01', 'Asia/Jakarta');
        $end = CarbonImmutable::createFromFormat('Y-m-d', $lastKey.'-01', 'Asia/Jakarta');
        $rows = collect();

        while ($cursor <= $end) {
            $cycle = $cycles->resolve($cursor->format('Y-m'));
            $items = Transaction::query()
                ->where('transaction_date', '>=', $cycle['start']->toDateString())
                ->where('transaction_date', '<', $cycle['end_exclusive']->toDateString())
                ->get();

            $byCategory = $items->groupBy('category')->map->sum('amount');
            $rows->push([
                'cycle' => $cycle,
                'kos' => (int) ($byCategory['kos'] ?? 0),
                'operasional' => (int) ($byCategory['operasional'] ?? 0),
                'allianz' => (int) ($byCategory['allianz'] ?? 0),
                'dana_darurat' => (int) ($byCategory['dana_darurat'] ?? 0),
                'bmri' => (int) ($byCategory['bmri'] ?? 0),
                'emas' => (int) ($byCategory['emas'] ?? 0),
                'uang_bebas' => (int) ($byCategory['uang_bebas'] ?? 0),
                'total' => (int) $items->sum('amount'),
            ]);

            $cursor = $cursor->addMonth();
        }

        return view('history', ['rows' => $rows->reverse()->values()]);
    }
}
