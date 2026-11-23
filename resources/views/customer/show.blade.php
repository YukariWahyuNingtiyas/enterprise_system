@extends('layouts.main')
@section('title', $customer->nama)
@section('subtitle', 'Detail pelanggan dan status pengiriman pesanan')
@section('content')
<div class="row g-3">
  <div class="col-lg-4"><div class="card card-body">
    <h6 class="mb-3">Informasi</h6>
    <dl class="row mb-0 small">
        <dt class="col-4">Email</dt><dd class="col-8">{{ $customer->email ?: '-' }}</dd>
        <dt class="col-4">Telepon</dt><dd class="col-8">{{ $customer->telepon ?: '-' }}</dd>
        <dt class="col-4">Alamat</dt><dd class="col-8">{{ $customer->alamat ?: '-' }}</dd>
        <dt class="col-4">Asal Lead</dt>
        <dd class="col-8">@if($customer->lead)<a href="{{ route('lead.show', $customer->lead) }}">{{ $customer->lead->nama }}</a>@else -@endif</dd>
    </dl>
    @if(auth()->user()->hasRole('admin','sales'))
        <a href="{{ route('order.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary mt-3"><i class="bi bi-bag-plus"></i> Buat Pesanan</a>
    @endif
  </div></div>
  <div class="col-lg-8"><div class="card"><div class="card-header">Pesanan & Status Pengiriman</div>
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Nomor</th><th>Tanggal</th><th class="text-end">Total</th><th>Pesanan</th><th>Pengiriman</th><th>Resi</th></tr></thead>
        <tbody>
        @forelse($customer->orders as $o)
            <tr><td><a href="{{ route('order.show', $o) }}" class="text-decoration-none fw-semibold">{{ $o->nomor_order }}</a></td>
                <td>{{ $o->tanggal->format('d/m/Y') }}</td><td class="text-end">@rp($o->total)</td>
                <td><x-badge :s="$o->status" /></td>
                <td>@if($o->status==='dikonfirmasi')<x-badge :s="$o->status_pengiriman" />@else - @endif</td>
                <td>{{ $o->no_resi ?: '-' }}</td></tr>
        @empty
            <tr><td colspan="6"><div class="empty"><i class="bi bi-bag"></i>Belum ada pesanan.</div></td></tr>
        @endforelse
        </tbody>
    </table></div></div></div>
</div>
@endsection