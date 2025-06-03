@php
    $id = $name;
@endphp
<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    <input
        type="{{ $type ?? 'text' }}"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ old($name) }}"
        class="form-control @error($name) is-invalid @enderror"
        {{ !empty($required) ? 'required' : '' }}
        {{ isset($step) ? "step=$step" : '' }}
    >
    @error($name)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
