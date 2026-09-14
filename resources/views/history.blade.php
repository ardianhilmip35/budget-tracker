@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 mb-1">Riwayat Bulanan</h1><div class="text-secondary">Rekap permanen berdasarkan tanggal transaksi.</div></div><a class="btn btn-success" href="{{ route('export.history') }}">Download Semua Excel</a></div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Siklus</th><th class="text-end">Kos</th><th class="text-end">Operasional</th><th class="text-end">BMRI</th><th class="text-end">Emas</th><th class="text-end">Total</th><th></th></tr></thead><tbody>
@forelse($rows as $row)
<tr><td><strong>{{ $row['cycle']['key'] }}</strong><div class="small text-secondary">{{ $row['cycle']['label'] }}</div></td><td class="text-end">Rp{{ number_format($row['kos'],0,',','.') }}</td><td class="text-end">Rp{{ number_format($row['operasional'],0,',','.') }}</td><td class="text-end">Rp{{ number_format($row['bmri'],0,',','.') }}</td><td class="text-end">Rp{{ number_format($row['emas'],0,',','.') }}</td><td class="text-end fw-bold">Rp{{ number_format($row['total'],0,',','.') }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('dashboard',['cycle'=>$row['cycle']['key']]) }}">Buka</a></td></tr>
@empty<tr><td colspan="7" class="text-center text-secondary py-5">Belum ada riwayat.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
