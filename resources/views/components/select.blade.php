@props(['name', 'label', 'options' => [], 'value' => null, 'col' => 'col-md-6', 'required' => false, 'placeholder' => null])
<div class="{{ $col }}">
    <label for="f_{{ $name }}" class="form-label fw-semibold">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <select name="{{ $name }}" id="f_{{ $name }}" {{ $attributes->class(['form-select', 'is-invalid' => $errors->has($name)]) }} @required($required)>
        @if($placeholder)<option value="">{{ $placeholder }}</option>@endif
        @foreach($options as $k => $v)
            <option value="{{ $k }}" @selected((string) old($name, $value) === (string) $k)>{{ $v }}</option>
        @endforeach
    </select>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>