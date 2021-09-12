<div class="form-group">
    <label for="{{ $field['key'] }}">{{ $field['label'] }}</label>
    <select name="{{ $field['key'] }}"
            class="col-12 col-md-6 form-control {{ $field['class'] }} {{ $errors->has($field['key']) ? 'is-invalid' : '' }}"
            id="{{ $field['key'] }}">
        @foreach($field['options'] as $val => $label)
            <option @if (old($field['key'], \App\Helpers\SettingHelper::getSetting($field['key']))) selected @endif value="{{ $val }}">{{ $label }}</option>
        @endforeach
    </select>

    @if ($errors->has($field['key'])) <small class="text-danger">{{ $errors->first($field['key']) }}</small> @endif
    <small class="form-text text-muted">{{ $field['description'] }}</small>
</div>
