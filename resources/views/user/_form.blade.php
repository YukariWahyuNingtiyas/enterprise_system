@php $x = $user ?? null; @endphp
<div class="row g-3">
    <x-field name="name" label="Nama Lengkap" :value="$x->name ?? ''" required maxlength="100" />
    <x-field name="email" type="email" label="Email"
         :value="$x->email ?? ''"
         required maxlength="100"
         pattern="[A-Za-z0-9._%+\-]+@gmail\.com"
         title="Gunakan email @gmail.com"
         placeholder="nama@gmail.com" />
    <x-select name="role_id" label="Role" :options="$roles" :value="$x->role_id ?? ''" placeholder="-- pilih role --" required />
    <div class="col-md-6"></div>
    <x-field name="password" type="password" label="Password" minlength="8" :required="!$x"
             help="{{ $x ? 'Kosongkan jika tidak ingin mengubah password.' : 'Minimal 8 karakter.' }}" />
    <x-field name="password_confirmation" type="password" label="Ulangi Password" :required="!$x" />
</div>