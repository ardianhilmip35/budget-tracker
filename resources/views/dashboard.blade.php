@extends('layouts.app')
@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
    <div>
        <div class="text-secondary small">Profil aktif: <strong>{{ $profile->name }}</strong> · THP <strong>Rp{{ number_format($profile->thp,0,',','.') }}</strong></div>
        <h1 class="h3 mb-1">{{ $cycle['label'] }}</h1>
        <div class="text-secondary small">Cut-off berikutnya: {{ $cycle['cutoff_label'] }}. Tanggal cut-off masuk siklus baru.</div>
    </div>
    <form method="GET" class="d-flex gap-2">
        <select name="cycle" class="form-select" onchange="this.form.submit()">
            @foreach($cycleChoices as $choice)
                <option value="{{ $choice['key'] }}" @selected($choice['key']===$cycle['key'])>{{ $choice['label'] }}</option>
            @endforeach
        </select>
        <a href="{{ route('export.monthly',['cycle'=>$cycle['key']]) }}" class="btn btn-success text-nowrap">Download Excel</a>
    </form>
</div>

<div class="row g-3 mb-4">
@foreach($summary as $item)
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card budget-card shadow-sm h-100"><div class="card-body">
            <div class="d-flex justify-content-between gap-2"><strong>{{ $item['label'] }}</strong><span class="badge {{ $item['remaining'] < 0 ? 'text-bg-danger':'text-bg-light' }}">{{ $item['percent'] }}%</span></div>
            <div class="small text-secondary mt-2">Sisa</div>
            <div class="h5 money mb-2 {{ $item['remaining'] < 0 ? 'text-danger':'' }}">Rp{{ number_format($item['remaining'],0,',','.') }}</div>
            <div class="progress mb-2"><div class="progress-bar {{ $item['percent']>=90 ? 'bg-warning':'' }}" style="width:{{ $item['percent'] }}%"></div></div>
            <div class="d-flex justify-content-between small text-secondary"><span>Rp{{ number_format($item['realization'],0,',','.') }}</span><span>Rp{{ number_format($item['budget'],0,',','.') }}</span></div>
        </div></div>
    </div>
@endforeach
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm"><div class="card-body p-3 p-md-4"><h2 class="h5 mb-3">Tambah Pengeluaran</h2>
            <form method="POST" action="{{ route('transactions.store') }}">@csrf
                <input type="hidden" name="cycle" value="{{ $cycle['key'] }}">
                <div class="row g-3">
                    <div class="col-12"><label class="form-label">Tanggal</label><input type="date" class="form-control" name="transaction_date" value="{{ old('transaction_date', now('Asia/Jakarta')->toDateString()) }}" required></div>
                    <div class="col-12"><label class="form-label">Kategori</label><select class="form-select" name="category" required>
                        <option value="">Pilih kategori</option>
                        @foreach($summary as $item)<option value="{{ $item['key'] }}">{{ $item['label'] }}</option>@endforeach
                    </select></div>
                    <div class="col-12"><label class="form-label">Keterangan</label><input class="form-control" name="subcategory" placeholder="Contoh: makan siang, TransJakarta, laundry"></div>
                    <div class="col-12"><label class="form-label">Nominal</label><input class="form-control" type="number" min="1" step="100" name="amount" inputmode="numeric" required></div>
                    <div class="col-12"><label class="form-label">Metode</label><select class="form-select" name="payment_method"><option value="">-</option>@foreach(['Cash','Transfer','QRIS','Debit','E-Wallet'] as $m)<option>{{ $m }}</option>@endforeach</select></div>
                    <div class="col-12"><label class="form-label">Catatan</label><input class="form-control" name="note"></div>
                    <div class="col-12"><button class="btn btn-primary w-100 btn-lg">Simpan Pengeluaran</button></div>
                </div>
            </form>
        </div></div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm"><div class="card-body p-0">
            <div class="p-3 p-md-4 pb-2"><h2 class="h5 mb-0">Transaksi Siklus Ini</h2></div>
            <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Tanggal</th><th>Keterangan</th><th class="text-end">Nominal</th><th></th></tr></thead><tbody>
                @forelse($transactions as $tx)
                <tr><td class="text-nowrap">{{ $tx->transaction_date->format('d/m') }}</td><td><strong>{{ $tx->subcategory ?: ucfirst(str_replace('_',' ',$tx->category)) }}</strong><div class="small text-secondary">{{ $tx->payment_method }} {{ $tx->note ? '· '.$tx->note : '' }}</div></td><td class="text-end money text-nowrap">Rp{{ number_format($tx->amount,0,',','.') }}</td><td class="text-end"><form method="POST" action="{{ route('transactions.destroy',$tx) }}" onsubmit="return confirm('Hapus transaksi?')">@csrf @method('DELETE')<input type="hidden" name="cycle" value="{{ $cycle['key'] }}"><button class="btn btn-sm btn-outline-danger">Hapus</button></form></td></tr>
                @empty<tr><td colspan="4" class="text-center text-secondary py-5">Belum ada transaksi pada siklus ini.</td></tr>@endforelse
            </tbody></table></div>
        </div></div>
    </div>
</div>
@endsection
