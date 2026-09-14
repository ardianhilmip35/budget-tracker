<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetProfile extends Model
{
    protected $fillable = [
        'name', 'thp', 'kos', 'operasional', 'allianz',
        'dana_darurat', 'bmri', 'emas', 'uang_bebas',
    ];

    protected $casts = [
        'thp' => 'integer',
        'kos' => 'integer',
        'operasional' => 'integer',
        'allianz' => 'integer',
        'dana_darurat' => 'integer',
        'bmri' => 'integer',
        'emas' => 'integer',
        'uang_bebas' => 'integer',
    ];

    public function budgets(): array
    {
        return [
            'kos' => ['label' => 'Kos', 'budget' => $this->kos],
            'operasional' => ['label' => 'Operasional Jakarta', 'budget' => $this->operasional],
            'allianz' => ['label' => 'Allianz Jiwa/CI', 'budget' => $this->allianz],
            'dana_darurat' => ['label' => 'Dana Darurat', 'budget' => $this->dana_darurat],
            'bmri' => ['label' => 'Saham BMRI', 'budget' => $this->bmri],
            'emas' => ['label' => 'Tabungan Emas', 'budget' => $this->emas],
            'uang_bebas' => ['label' => 'Uang Bebas', 'budget' => $this->uang_bebas],
        ];
    }
}
