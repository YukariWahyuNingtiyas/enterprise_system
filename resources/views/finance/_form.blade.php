@php $f = $finance ?? null; @endphp
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Jenis</label>
        <select name="jenis" class="form-select" required>
            <option value="pemasukan" @selected(old('jenis', $f->jenis ?? '')=='pemasukan')>Pemasukan</option>
            <option value="pengeluaran" @selected(old('jenis', $f->jenis ?? '')=='pengeluaran')>Pengeluaran</option>
        </select></div>
    <div class="col-md-6"><label class="form-label">Kategori</label>
        <input name="kategori" value="{{ old('kategori', $f->kategori ?? '') }}" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Nominal (Rp)</label>
        <input type="number" min="1" step="0.01" name="nominal" value="{{ old('nominal', $f->nominal ?? '') }}" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Tanggal</label>
        <input type="date" name="tanggal" value="{{ old('tanggal', isset($f) ? $f->tanggal->toDateString() : date('Y-m-d')) }}" class="form-control" required></div>
    <div class="col-12"><label class="form-label">Deskripsi</label>
        <textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi', $f->deskripsi ?? '') }}</textarea></div>
</div>