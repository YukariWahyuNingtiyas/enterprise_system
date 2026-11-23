@extends('layouts.main')
@section('title', 'Detail Invoice')
@section('content')
@php $alur = ['draft'=>['diterbitkan','dibatalkan'],'diterbitkan'=>['lunas','jatuh_tempo','dibatalkan'],'jatuh_tempo'=>['lunas','dibatalkan'],'lunas'=>[],'dibatalkan'=>[]]; @endphp
<div class="card"><div class="card-body">
    <h5>{{ $invoice->nomor_invoice }} <span class="badge bg-secondary">{{ $invoice->status }}</span></h5>
    <p class="mb-1">Pelanggan: <strong>{{ $invoice->customer->nama }}</strong></p>
    <p class="mb-3">Terbit {{ $invoice->tanggal_terbit->format('d/m/Y') }} | Jatuh tempo {{ $invoice->jatuh_tempo->format('d/m/Y') }}</p>
    <table class="table table-bordered">
        <thead class="table-light"><tr><th>Item</th><th>Jumlah</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
        @foreach($invoice->items as $it)
        <tr><td>{{ $it->nama_item }}</td><td>{{ $it->jumlah }}</td>
            <td class="text-end">{{ number_format($it->harga,0,',','.') }}</td>
            <td class="text-end">{{ number_format($it->subtotal,0,',','.') }}</td></tr>
        @endforeach
        <tr><th colspan="3" class="text-end">Total</th><th class="text-end">Rp {{ number_format($invoice->total,0,',','.') }}</th></tr>
    </table>

    @if(auth()->user()->hasRole('admin','keuangan'))
        @foreach($alur[$invoice->status] as $s)
        <form method="POST" action="{{ route('invoice.status', $invoice) }}" class="d-inline">
            @csrf @method('PATCH')<input type="hidden" name="status" value="{{ $s }}">
            <button class="btn btn-sm btn-outline-primary">Ubah ke: {{ $s }}</button></form>
        @endforeach
        @if($invoice->status=='draft')
        <form method="POST" action="{{ route('invoice.destroy', $invoice) }}" class="d-inline" onsubmit="return confirm('Hapus invoice draft ini?')">
            @csrf @method('DELETE')<button class="btn btn-sm btn-danger">Hapus</button></form>
        @endif
    @endif
    <a href="{{ route('invoice.index') }}" class="btn btn-sm btn-light">Kembali</a>
</div></div>
@endsection