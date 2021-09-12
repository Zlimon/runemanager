<div class="form-group">
    <label for="{{ $field['key'] }}">{{ $field['label'] }}</label>
    <input type="{{ $field['type'] }}"
           name="{{ $field['key'] }}"
           value="{{ old($field['key']) ?: \App\Helpers\SettingHelper::getSetting($field['key']) }}"
           class="col-12 col-md-6 form-control {{ $field['class'] }} {{ $errors->has($field['key']) ? 'is-invalid' : '' }}"
           id="{{ $field['key'] }}"
           placeholder="{{ $field['label'] }}">

    @if ($errors->has($field['key'])) <small class="text-danger">{{ $errors->first($field['key']) }}</small> @endif
    <small class="form-text text-muted">{{ $field['description'] }}</small>
</div>
