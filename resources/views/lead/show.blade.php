@extends('layouts.main')
@section('title', $lead->nama)
@section('subtitle', 'Detail prospek dan riwayat komunikasi')
@section('content')
@php $tulis = auth()->user()->hasRole('admin','sales'); @endphp
<div class="row g-3">
  <div class="col-lg-5">
    <div class="card card-body mb-3">
        <div class="d-flex justify-content-between"><h6 class="mb-3">Informasi</h6><x-badge :s="$lead->status" /></div>
        <dl class="row mb-0 small">
            <dt class="col-4">Email</dt><dd class="col-8">{{ $lead->email ?: '-' }}</dd>
            <dt class="col-4">Telepon</dt><dd class="col-8">{{ $lead->telepon ?: '-' }}</dd>
            <dt class="col-4">Anggaran</dt><dd class="col-8">@rp($lead->anggaran)</dd>
            <dt class="col-4">Kebutuhan</dt><dd class="col-8">{{ $lead->kebutuhan ?: '-' }}</dd>
            <dt class="col-4">Skor AI</dt>
            <dd class="col-8">@if($lead->prioritas)<span class="badge text-bg-{{ $lead->prioritas[1] }}">{{ number_format($lead->skor_prioritas,1) }} · {{ $lead->prioritas[0] }}</span>@else -@endif</dd>
        </dl>
    </div>
    @if($tulis)
    <div class="card card-body mb-3">
        <h6>Ubah Tahap Pipeline</h6>
        <div class="d-flex flex-wrap gap-2">
        @foreach(['prospect','negotiation','deal','lost'] as $s)
            <form method="POST" action="{{ route('lead.status', $lead) }}" @if($s=='lost') data-confirm="Tandai prospek ini sebagai Lost?" @endif>
                @csrf @method('PATCH')<input type="hidden" name="status" value="{{ $s }}">
                <button class="btn btn-sm {{ $lead->status==$s ? 'btn-primary' : 'btn-outline-secondary' }}">{{ ucfirst($s) }}</button></form>
        @endforeach
        </div>
        @if($lead->status === 'deal' && !$lead->customer)
            <form method="POST" action="{{ route('lead.convert', $lead) }}" class="mt-3" data-confirm="Buat data pelanggan dari prospek ini?">
                @csrf<button class="btn btn-success w-100"><i class="bi bi-person-check"></i> Jadikan Pelanggan</button></form>
        @elseif($lead->customer)
            <a href="{{ route('customer.show', $lead->customer) }}" class="btn btn-outline-success mt-3"><i class="bi bi-person-check"></i> Sudah menjadi pelanggan</a>
        @endif
    </div>
    @endif
  </div>

  <div class="col-lg-7">
    @if($tulis)
    <div class="card card-body mb-3">
        <h6>Catat Komunikasi</h6>
        <form method="POST" action="{{ route('communication.store', $lead) }}" novalidate>
            @csrf
            <div class="row g-2">
                <x-select name="channel" label="Jenis" col="col-md-4" required
                    :options="['telepon'=>'Telepon','email'=>'Email','whatsapp'=>'WhatsApp','tatap_muka'=>'Tatap muka']" />
                <x-field name="tanggal" type="date" label="Tanggal" col="col-md-4" :value="date('Y-m-d')" max="{{ date('Y-m-d') }}" required />
                <div class="col-md-4 d-flex align-items-end"><button class="btn btn-primary w-100">Simpan</button></div>
                <x-textarea name="catatan" label="Catatan" rows="2" maxlength="500" />
            </div>
        </form>
    </div>
    @endif
    <div class="card"><div class="card-header">Riwayat Komunikasi ({{ $lead->communications->count() }})</div>
        <ul class="list-group list-group-flush">
        @forelse($lead->communications as $c)
            <li class="list-group-item d-flex justify-content-between gap-3">
                <div><span class="badge text-bg-light border">{{ ucfirst(str_replace('_',' ',$c->channel)) }}</span>
                    <span class="text-muted small ms-1">{{ $c->tanggal->format('d/m/Y') }}</span>
                    <div>{{ $c->catatan ?: '-' }}</div></div>
                @if($tulis)
                <form method="POST" action="{{ route('communication.destroy', $c) }}" data-confirm="Hapus catatan komunikasi ini?">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                @endif
            </li>
        @empty
            <li class="list-group-item"><div class="empty"><i class="bi bi-chat-dots"></i>Belum ada komunikasi.</div></li>
        @endforelse
        </ul>
    </div>
  </div>
</div>
@endsection