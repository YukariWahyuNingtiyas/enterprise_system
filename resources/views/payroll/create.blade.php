@extends('layouts.main')
@section('title', 'Tambah Penggajian')
@section('content')
<form method="POST" action="{{ route('payroll.store') }}" class="card card-body">
    @csrf
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Karyawan</label>
            <select name="employee_id" class="form-select" required>
                @foreach($employees as $e)<option value="{{ $e->id }}" @selected(old('employee_id')==$e->id)>{{ $e->kode_karyawan }} - {{ $e->nama }}</option>@endforeach
            </select></div>
        <div class="col-md-6"><label class="form-label">Periode</label><input type="month" name="periode" value="{{ old('periode', date('Y-m')) }}" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Gaji Pokok</label><input type="number" min="0" name="gaji_pokok" id="gp" value="{{ old('gaji_pokok', 0) }}" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Tunjangan</label><input type="number" min="0" name="tunjangan" id="tj" value="{{ old('tunjangan', 0) }}" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Potongan</label><input type="number" min="0" name="potongan" id="pt" value="{{ old('potongan', 0) }}" class="form-control"></div>
        <div class="col-12"><strong>Total gaji: Rp <span id="tot">0</span></strong></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Simpan</button> <a href="{{ route('payroll.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection
@push('scripts')
<script>
const f = () => { const t = (+gp.value||0) + (+tj.value||0) - (+pt.value||0); tot.textContent = t.toLocaleString('id-ID'); };
['gp','tj','pt'].forEach(id => document.getElementById(id).addEventListener('input', f)); f();
</script>
@endpush