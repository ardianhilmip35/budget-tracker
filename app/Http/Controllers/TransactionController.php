<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public const CATEGORIES = [
        'kos', 'operasional', 'allianz', 'dana_darurat', 'bmri', 'emas', 'uang_bebas',
    ];

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cycle' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'transaction_date' => ['required', 'date'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'subcategory' => ['nullable', 'string', 'max:120'],
            'amount' => ['required', 'integer', 'min:1'],
            'payment_method' => ['nullable', Rule::in(['Cash', 'Transfer', 'QRIS', 'Debit', 'E-Wallet'])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $cycle = $data['cycle'];
        unset($data['cycle']);
        Transaction::create($data);

        return redirect()->route('dashboard', ['cycle' => $cycle])
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        $cycle = (string) $request->input('cycle');
        $transaction->delete();

        return redirect()->route('dashboard', ['cycle' => $cycle])
            ->with('success', 'Transaksi dihapus.');
    }
}
