@extends('layouts.main')
@section('title', 'CRM · Sales Pipeline')
@section('subtitle', 'Perkembangan prospek per tahap penjualan')
@section('content')
@php
$kolom = ['prospect'=>['Prospect','bi-eye','info'],'negotiation'=>['Negotiation','bi-chat-dots','warning'],
          'deal'=>['Deal','bi-check2-circle','success'],'lost'=>['Lost','bi-x-circle','danger']];
$tulis = auth()->user()->hasRole('admin','sales');
@endphp
<div class="kanban">
@foreach($kolom as $kode => [$judul, $ikon, $warna])
    <div class="kanban-col">
        <h6><span><i class="bi {{ $ikon }} text-{{ $warna }}"></i> {{ $judul }}</span>
            <span class="badge text-bg-{{ $warna }}">{{ isset($data[$kode]) ? $data[$kode]->count() : 0 }}</span></h6>
        @forelse($data[$kode] ?? [] as $l)
            <div class="k-card">
                <a href="{{ route('lead.show', $l) }}" class="fw-semibold text-decoration-none">{{ $l->nama }}</a>
                <div class="small text-muted">@rp($l->anggaran)</div>
                @if($l->prioritas)<span class="badge text-bg-{{ $l->prioritas[1] }} mt-1">Skor {{ number_format($l->skor_prioritas,0) }} · {{ $l->prioritas[0] }}</span>@endif
                @if($tulis)
                <div class="d-flex flex-wrap gap-1 mt-2">
                @foreach($kolom as $k2 => $info)
                    @if($k2 !== $kode)
                    <form method="POST" action="{{ route('lead.status', $l) }}">@csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $k2 }}">
                        <button class="btn btn-sm btn-outline-{{ $info[2] }} py-0">→ {{ $info[0] }}</button></form>
                    @endif
                @endforeach
                </div>
                @endif
            </div>
        @empty
            <div class="text-muted small text-center py-3">Kosong</div>
        @endforelse
    </div>
@endforeach
</div>
@endsection