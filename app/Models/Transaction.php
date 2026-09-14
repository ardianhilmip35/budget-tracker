<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_date', 'category', 'subcategory', 'amount',
        'payment_method', 'note',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'integer',
    ];
}
