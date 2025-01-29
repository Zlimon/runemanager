<div class="mb-3">
    <label for="{{ $field['key'] }}" class="form-label">{{ $field['label'] }}</label>
    <input type="{{ $field['type'] }}"
           id="{{ $field['key'] }}"
           name="{{ $field['key'] }}"
           class="form-control {{ $field['class'] }} {{ $errors->has($field['key']) ? 'is-invalid' : '' }}"
           value="{{ old($field['key']) ?: \App\Helpers\SettingHelper::getSetting($field['key']) }}"
           placeholder="{{ $field['label'] }}">
    @if ($errors->has($field['key'])) <small class="form-text text-danger">{{ $errors->first($field['key']) }}</small><br> @endif
    <small class="form-text text-dark">{{ $field['description'] }}</small>
</div>
