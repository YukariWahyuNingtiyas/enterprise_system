@php $x = $lead ?? null; @endphp
<div class="row g-3">
    <x-field name="nama" label="Nama Prospek / Perusahaan" :value="$x->nama ?? ''" required maxlength="100" />
    <x-field name="email" type="email" label="Email" :value="$x->email ?? ''" maxlength="100" />
    <x-field name="telepon" label="Telepon" :value="$x->telepon ?? ''" maxlength="20" pattern="[0-9+\-\s]{8,20}"
             help="Contoh: 081234567890" />
    <x-field name="anggaran" type="number" label="Anggaran (Rp)" :value="$x->anggaran ?? ''" min="0" step="1000" required />
    <x-select name="status" label="Status Pipeline" :value="$x->status ?? 'prospect'" required
        :options="['prospect'=>'Prospect','negotiation'=>'Negotiation','deal'=>'Deal','lost'=>'Lost']" />
    <div class="col-md-6"></div>
    <x-textarea name="kebutuhan" label="Kebutuhan Calon Pelanggan" :value="$x->kebutuhan ?? ''" maxlength="500" />
</div>