@props(['name', 'label', 'value' => null, 'col' => 'col-12', 'required' => false, 'rows' => 3])
<div class="{{ $col }}">
    <label for="f_{{ $name }}" class="form-label fw-semibold">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <textarea name="{{ $name }}" id="f_{{ $name }}" rows="{{ $rows }}"
        {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }} @required($required)>{{ old($name, $value) }}</textarea>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>