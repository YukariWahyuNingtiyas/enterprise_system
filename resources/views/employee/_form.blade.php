@php $e = $employee ?? null; @endphp
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Kode Karyawan</label><input name="kode_karyawan" value="{{ old('kode_karyawan', $e->kode_karyawan ?? '') }}" class="form-control" required></div>
    <div class="col-md-8"><label class="form-label">Nama Lengkap</label><input name="nama" value="{{ old('nama', $e->nama ?? '') }}" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email', $e->email ?? '') }}" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Telepon</label><input name="telepon" value="{{ old('telepon', $e->telepon ?? '') }}" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">Jabatan</label><input name="jabatan" value="{{ old('jabatan', $e->jabatan ?? '') }}" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Bagian</label><input name="bagian" value="{{ old('bagian', $e->bagian ?? '') }}" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Tanggal Bergabung</label><input type="date" name="tanggal_bergabung" value="{{ old('tanggal_bergabung', isset($e) ? $e->tanggal_bergabung->toDateString() : date('Y-m-d')) }}" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="aktif" @selected(old('status', $e->status ?? 'aktif')=='aktif')>Aktif</option>
            <option value="nonaktif" @selected(old('status', $e->status ?? '')=='nonaktif')>Nonaktif</option></select></div>
</div>