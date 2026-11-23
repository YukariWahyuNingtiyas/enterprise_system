@extends('layouts.main')
@section('title', 'Buat Invoice')
@section('subtitle', 'Pilih pelanggan dan barang yang ditagihkan')
@section('content')
<form method="POST" action="{{ route('invoice.store') }}" class="card card-body" id="formInvoice">
    @csrf
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Pelanggan</label>
            <select name="customer_id" class="form-select" required>
                <option value="">-- pilih pelanggan --</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->nama }}</option>
                @endforeach
            </select>
            @error('customer_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Terbit</label>
            <input type="date" name="tanggal_terbit" value="{{ old('tanggal_terbit', date('Y-m-d')) }}" class="form-control" required>
            @error('tanggal_terbit')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Jatuh Tempo</label>
            <input type="date" name="jatuh_tempo" value="{{ old('jatuh_tempo', date('Y-m-d', strtotime('+14 days'))) }}" class="form-control" required>
            @error('jatuh_tempo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>

    <h6 class="fw-bold mb-2">Item Tagihan</h6>
    @if($inventories->isEmpty())
        <div class="alert alert-warning">Belum ada data barang. Tambahkan dulu di menu <b>Inventory</b>.</div>
    @endif
    @if($errors->has('items') || $errors->has('items.*'))
        <div class="alert alert-danger py-2">{{ $errors->first('items') ?: $errors->first() }}</div>
    @endif

    <div class="table-responsive">
        <table class="table align-middle" id="tbl-items">
            <thead>
                <tr><th>Barang</th><th width="120">Jumlah</th><th width="170" class="text-end">Harga</th><th width="170" class="text-end">Subtotal</th><th width="50"></th></tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div><button type="button" class="btn btn-outline-primary btn-sm mb-3" id="tambah"><i class="bi bi-plus-lg"></i> Tambah Barang</button></div>
    <div class="text-end mb-3 fs-5"><strong>Total: <span id="total">Rp 0</span></strong></div>

    <div class="mb-4">
        <label class="form-label">Catatan</label>
        <textarea name="catatan" class="form-control" rows="2" maxlength="500" placeholder="Opsional">{{ old('catatan') }}</textarea>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary" id="btnSimpan"><i class="bi bi-check2-circle"></i> Simpan Invoice</button>
        <a href="{{ route('invoice.index') }}" class="btn btn-light">Batal</a>
    </div>
</form>
@endsection

@push('scripts')
@php
    $dataBarang = [];
    foreach ($inventories as $b) {
        $dataBarang[$b->id] = ['kode' => $b->kode_barang, 'nama' => $b->nama, 'stok' => $b->stok, 'harga' => (float) $b->harga_jual];
    }
    $lama = old('items', []);
    $barisLama = [];
    foreach ($lama as $it) {
        $barisLama[] = [(string) ($it['inventory_id'] ?? ''), (int) ($it['jumlah'] ?? 1)];
    }
@endphp
<script>
const barang = {!! json_encode($dataBarang, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!};
const rp   = n => 'Rp ' + Number(n).toLocaleString('id-ID');
const body = document.querySelector('#tbl-items tbody');
let idx = 0;

function opsi() {
    return '<option value="">-- pilih barang --</option>' + Object.entries(barang)
        .map(([id, b]) => `<option value="${id}">${b.kode} — ${b.nama} (stok ${b.stok})</option>`).join('');
}
function tambahBaris(idBarang = '', jumlah = 1) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><select name="items[${idx}][inventory_id]" class="form-select brg" required>${opsi()}</select></td>
      <td><input type="number" min="1" step="1" value="${jumlah}" name="items[${idx}][jumlah]" class="form-control jml" required><div class="invalid-feedback pesan"></div></td>
      <td class="text-end hrg">Rp 0</td>
      <td class="text-end sub fw-semibold">Rp 0</td>
      <td><button type="button" class="btn btn-sm btn-danger hapus" title="Hapus baris">&times;</button></td>`;
    body.appendChild(tr);
    if (idBarang) tr.querySelector('.brg').value = idBarang;
    idx++; hitung();
}
function hitung() {
    let total = 0, salah = 0;
    const dipakai = {};
    body.querySelectorAll('tr').forEach(tr => {
        const pilih = tr.querySelector('.brg'), j = tr.querySelector('.jml'), pesan = tr.querySelector('.pesan');
        const b = barang[pilih.value], qty = parseInt(j.value) || 0;
        const harga = b ? b.harga : 0;
        let err = '';
        if (!b) err = 'Pilih barang terlebih dahulu.';
        else if (dipakai[pilih.value]) err = 'Barang ini sudah dipilih di baris lain.';
        else if (b.stok <= 0) err = 'Stok barang habis.';
        else if (qty < 1) err = 'Jumlah minimal 1.';
        else if (qty > b.stok) err = 'Melebihi stok. Maksimal ' + b.stok + '.';
        if (b) dipakai[pilih.value] = true;
        j.classList.toggle('is-invalid', !!err && !!b);
        j.classList.remove('is-valid');
        pesan.textContent = err;
        if (err) salah++;
        tr.querySelector('.hrg').textContent = rp(harga);
        tr.querySelector('.sub').textContent = rp(harga * qty);
        total += harga * qty;
    });
    document.getElementById('total').textContent = rp(total);
    const btn = document.getElementById('btnSimpan');
    btn.disabled = salah > 0;
    btn.title = salah > 0 ? 'Perbaiki jumlah/barang yang bermasalah dulu' : '';
}
document.getElementById('tambah').onclick = () => tambahBaris();
body.addEventListener('input', hitung);
body.addEventListener('change', hitung);
body.addEventListener('click', e => {
    const h = e.target.closest('.hapus');
    if (!h) return;
    if (body.querySelectorAll('tr').length === 1) { alert('Minimal harus ada satu barang.'); return; }
    h.closest('tr').remove(); hitung();
});
const barisLama = {!! json_encode($barisLama) !!};
if (barisLama.length) { barisLama.forEach(r => tambahBaris(r[0], r[1])); } else { tambahBaris(); }
</script>
@endpush