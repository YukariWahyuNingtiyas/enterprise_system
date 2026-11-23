@extends('layouts.main')
@section('title', $order->nomor_order)
@section('subtitle', 'Detail pesanan dan status pengiriman')
@section('actions')<a href="{{ route('order.index') }}" class="btn btn-light">Kembali</a>@endsection
@section('content')
@php
$u = auth()->user(); $sp = $order->status_pengiriman; $ok = $order->status === 'dikonfirmasi';
$langkah = [['Dibuat','bi-pencil',true],['Dikonfirmasi','bi-check2',$ok],
    ['Diproses','bi-box-seam',$ok && in_array($sp,['diproses','dikirim','diterima'])],
    ['Dikirim','bi-truck',$ok && in_array($sp,['dikirim','diterima'])],['Diterima','bi-house-check',$ok && $sp==='diterima']];
@endphp
@if($order->status==='dibatalkan')<div class="alert alert-danger">Pesanan ini dibatalkan.</div>
@else
<div class="card card-body mb-3"><div class="steps">
    @foreach($langkah as [$t,$i,$d])<div class="step {{ $d ? 'done' : '' }}"><div class="dot"><i class="bi {{ $i }}"></i></div>{{ $t }}</div>@endforeach
</div></div>
@endif

<div class="row g-3">
  <div class="col-lg-8"><div class="card"><div class="card-header">Barang</div>
    <div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Barang</th><th class="text-center">Jumlah</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>@foreach($order->items as $it)
            <tr><td>{{ $it->inventory->nama }}</td><td class="text-center">{{ $it->jumlah }}</td>
                <td class="text-end">@rp($it->harga)</td><td class="text-end">@rp($it->subtotal)</td></tr>@endforeach</tbody>
        <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end">@rp($order->total)</th></tr></tfoot>
    </table></div></div></div>

  <div class="col-lg-4">
    <div class="card card-body mb-3 small">
        <div>Pelanggan: <a href="{{ route('customer.show', $order->customer) }}" class="fw-semibold">{{ $order->customer->nama }}</a></div>
        <div>Tanggal: {{ $order->tanggal->format('d/m/Y') }}</div>
        <div>Status pesanan: <x-badge :s="$order->status" /></div>
        @if($ok)<div>Pengiriman: <x-badge :s="$sp" /></div>@endif
        <div>No. resi: <strong>{{ $order->no_resi ?: '-' }}</strong></div>
        @if($order->invoice && $u->hasRole('admin','sales','keuangan','pimpinan'))
            <div>Invoice: <a href="{{ route('invoice.show', $order->invoice) }}">{{ $order->invoice->nomor_invoice }}</a></div>
        @endif
        @if($order->catatan)<div class="mt-2 text-muted">{{ $order->catatan }}</div>@endif
    </div>

    @if($u->hasRole('admin','sales') && $order->status==='draft')
        <form method="POST" action="{{ route('order.konfirmasi', $order) }}" class="mb-2" data-confirm="Konfirmasi pesanan? Invoice dan jurnal pendapatan akan dibuat otomatis.">
            @csrf<button class="btn btn-success w-100"><i class="bi bi-check2-circle"></i> Konfirmasi Pesanan</button></form>
        <form method="POST" action="{{ route('order.batal', $order) }}" data-confirm="Batalkan pesanan ini?">
            @csrf<button class="btn btn-outline-danger w-100">Batalkan</button></form>
    @endif

    @if($u->hasRole('admin','manajer_gudang') && $ok)
        @if($sp==='menunggu')
            <form method="POST" action="{{ route('order.proses', $order) }}" data-confirm="Proses pesanan? Stok barang akan dikurangi.">
                @csrf<button class="btn btn-primary w-100"><i class="bi bi-box-seam"></i> Proses Pesanan</button></form>
        @elseif($sp==='diproses')
            <form method="POST" action="{{ route('order.kirim', $order) }}" novalidate>@csrf
                <x-field name="no_resi" label="Nomor Resi Pengiriman" col="col-12" minlength="5" maxlength="50" required />
                <button class="btn btn-info w-100 mt-2"><i class="bi bi-truck"></i> Kirim Pesanan</button></form>
        @elseif($sp==='dikirim')
            <form method="POST" action="{{ route('order.terima', $order) }}" data-confirm="Tandai pesanan sudah diterima pelanggan?">
                @csrf<button class="btn btn-success w-100"><i class="bi bi-house-check"></i> Tandai Diterima</button></form>
        @endif
    @endif
  </div>
</div>
@endsection