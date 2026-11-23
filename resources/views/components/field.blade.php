@props(['name', 'label', 'type' => 'text', 'value' => null, 'col' => 'col-md-6', 'required' => false, 'help' => null])
<div class="{{ $col }}">
    <label for="f_{{ $name }}" class="form-label fw-semibold">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <input type="{{ $type }}" name="{{ $name }}" id="f_{{ $name }}"
           value="{{ $type === 'password' ? '' : old($name, $value) }}"
           {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }} @required($required)>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if($help)<div class="form-text">{{ $help }}</div>@endif
</div>