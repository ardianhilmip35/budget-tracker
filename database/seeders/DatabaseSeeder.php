<?php

namespace Database\Seeders;

use App\Models\BudgetProfile;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $a = BudgetProfile::query()->updateOrCreate(['name' => 'Opsi A'], [
            'thp' => 8500000,
            'kos' => 1300000,
            'operasional' => 2700000,
            'allianz' => 1300000,
            'dana_darurat' => 850000,
            'bmri' => 1000000,
            'emas' => 1250000,
            'uang_bebas' => 100000,
        ]);

        $b = BudgetProfile::query()->updateOrCreate(['name' => 'Opsi B'], [
            'thp' => 9000000,
            'kos' => 1300000,
            'operasional' => 2700000,
            'allianz' => 1300000,
            'dana_darurat' => 900000,
            'bmri' => 1100000,
            'emas' => 1500000,
            'uang_bebas' => 200000,
        ]);

        Setting::setValue('active_profile_id', $b->id);
    }
}
