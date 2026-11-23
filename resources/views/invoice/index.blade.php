@extends('layouts.main')
@section('title', 'ERP - Invoice')
@section('content')
<div class="mb-3">
    @if(auth()->user()->hasRole('admin','keuangan','sales'))
        <a href="{{ route('invoice.create') }}" class="btn btn-primary">+ Buat Invoice</a>
    @endif
</div>
<table class="table table-bordered bg-white">
    <thead class="table-light"><tr><th>Nomor</th><th>Pelanggan</th><th>Terbit</th><th>Jatuh Tempo</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($data as $d)
        <tr>
            <td>{{ $d->nomor_invoice }}</td><td>{{ $d->customer->nama }}</td>
            <td>{{ $d->tanggal_terbit->format('d/m/Y') }}</td><td>{{ $d->jatuh_tempo->format('d/m/Y') }}</td>
            <td class="text-end">{{ number_format($d->total,0,',','.') }}</td>
            <td><span class="badge bg-secondary">{{ $d->status }}</span></td>
            <td><a href="{{ route('invoice.show', $d) }}" class="btn btn-sm btn-info">Detail</a></td>
        </tr>
    @empty <tr><td colspan="7" class="text-center text-muted">Belum ada invoice.</td></tr> @endforelse
    </tbody>
</table>
{{ $data->links() }}
@endsection