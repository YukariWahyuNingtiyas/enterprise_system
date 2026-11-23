@extends('layouts.main')
@section('title', 'Buat Pesanan')
@section('subtitle', 'Harga diambil otomatis dari data barang; stok diperiksa sebelum disimpan')
@section('content')
<form method="POST" action="{{ route('order.store') }}" class="card card-body" novalidate>
    @csrf
    <div class="row g-3 mb-3">
        <x-select name="customer_id" label="Pelanggan" :options="$customers" :value="$customer_id" placeholder="-- pilih pelanggan --" required col="col-md-6" />
        <x-field name="tanggal" type="date" label="Tanggal Pesanan" :value="date('Y-m-d')" required col="col-md-3" />
    </div>
    <h6>Barang yang Dipesan</h6>
    @error('items')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
    <div class="table-responsive"><table class="table align-middle" id="tbl">
        <thead><tr><th>Barang</th><th width="130">Jumlah</th><th width="150" class="text-end">Harga</th><th width="170" class="text-end">Subtotal</th><th width="50"></th></tr></thead>
        <tbody></tbody>
    </table></div>
    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="tambah"><i class="bi bi-plus"></i> Tambah Barang</button>
    <div class="text-end fs-5 mb-3">Total: <strong>Rp <span id="total">0</span></strong></div>
    <x-textarea name="catatan" label="Catatan" rows="2" maxlength="500" />
    <div class="mt-4"><button class="btn btn-primary" id="btnSimpan">Simpan Pesanan</button> <a href="{{ route('order.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection

@push('scripts')
@php
    $dataBarang = [];
    foreach ($inventories as $b) {
        $dataBarang[$b->id] = ['nama' => $b->nama, 'stok' => $b->stok, 'harga' => (float) $b->harga_jual];
    }
    $lama = old('items', []);
@endphp
<script>
const barang = {!! json_encode($dataBarang, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!};
const lama = {!! json_encode($lama, JSON_HEX_TAG | JSON_HEX_AMP) !!};
const body = document.querySelector('#tbl tbody');
const opsi = Object.entries(barang).map(([id, b]) => `<option value="${id}">${b.nama} (stok ${b.stok})</option>`).join('');
const rp = n => n.toLocaleString('id-ID');
let idx = 0;

function baris(item = {}) {
    const i = idx++, tr = document.createElement('tr');
    tr.innerHTML = `
      <td><select name="items[${i}][inventory_id]" class="form-select form-select-sm brg" required><option value="">-- pilih barang --</option>${opsi}</select></td>
      <td><input type="number" min="1" name="items[${i}][jumlah]" value="${item.jumlah || 1}" class="form-control form-control-sm jml" required><div class="invalid-feedback pesan"></div></td>
      <td class="text-end hrg">0</td><td class="text-end sub">0</td>
      <td><button type="button" class="btn btn-sm btn-outline-danger hapus">&times;</button></td>`;
    body.appendChild(tr);
    if (item.inventory_id) tr.querySelector('.brg').value = item.inventory_id;
    hitung();
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
document.getElementById('tambah').onclick = () => baris();
body.addEventListener('input', hitung);
body.addEventListener('change', hitung);
body.addEventListener('click', e => {
    const h = e.target.closest('.hapus');
    if (!h) return;
    if (body.querySelectorAll('tr').length === 1) { alert('Minimal harus ada satu barang.'); return; }
    h.closest('tr').remove(); hitung();
});
(Object.keys(lama).length ? Object.values(lama) : [{}]).forEach(baris);
</script>
@endpush