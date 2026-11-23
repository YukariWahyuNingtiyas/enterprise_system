@php $x = $customer ?? null; @endphp
<div class="row g-3">
    <x-field name="nama" label="Nama Pelanggan" :value="$x->nama ?? ''" required maxlength="100" />
    <x-field name="email" type="email" label="Email" :value="$x->email ?? ''" maxlength="100" />
    <x-field name="telepon" label="Telepon" :value="$x->telepon ?? ''" maxlength="20" pattern="[0-9+\-\s]{8,20}" />
    <div class="col-md-6"></div>
    <x-textarea name="alamat" label="Alamat" :value="$x->alamat ?? ''" maxlength="300" />
</div>