<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class CycleService
{
    public function defaultCycle(): string
    {
        $today = CarbonImmutable::now('Asia/Jakarta');
        $base = $today->day >= 15 ? $today : $today->subMonth();

        return $base->format('Y-m');
    }

    /**
     * @return array{key:string,start:CarbonImmutable,end_exclusive:CarbonImmutable,label:string,cutoff_label:string}
     */
    public function resolve(?string $cycle): array
    {
        $cycle = preg_match('/^\d{4}-\d{2}$/', (string) $cycle)
            ? $cycle
            : $this->defaultCycle();

        $month = CarbonImmutable::createFromFormat('Y-m-d', $cycle.'-01', 'Asia/Jakarta');
        $start = $month->setDay(15)->startOfDay();
        $endExclusive = $start->addMonth();

        return [
            'key' => $cycle,
            'start' => $start,
            'end_exclusive' => $endExclusive,
            'label' => $start->translatedFormat('d F Y').' - '.$endExclusive->subDay()->translatedFormat('d F Y'),
            'cutoff_label' => $endExclusive->translatedFormat('d F Y'),
        ];
    }

    public function choices(int $past = 18, int $future = 12): Collection
    {
        $current = CarbonImmutable::now('Asia/Jakarta')->startOfMonth();

        return collect(range(-$past, $future))->map(function (int $offset) use ($current) {
            $month = $current->addMonths($offset);
            return [
                'key' => $month->format('Y-m'),
                'label' => $month->translatedFormat('F Y'),
            ];
        });
    }

    public function cycleKeyForDate(CarbonImmutable $date): string
    {
        $date = $date->setTimezone('Asia/Jakarta');
        $month = $date->day >= 15 ? $date : $date->subMonth();
        return $month->format('Y-m');
    }
}
