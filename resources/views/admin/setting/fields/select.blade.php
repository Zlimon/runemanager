<div class="mb-3">
    <label for="{{ $field['key'] }}" class="form-label">{{ $field['label'] }}</label>
    <select id="{{ $field['key'] }}"
            name="{{ $field['key'] }}"
            class="form-control {{ $field['class'] }} {{ $errors->has($field['key']) ? 'is-invalid' : '' }}">
        @foreach($field['options'] as $val => $label)
            <option value="{{ $val }}" @if (old($field['key'], \App\Helpers\SettingHelper::getSetting($field['key']))) selected @endif>{{ $label }}</option>
        @endforeach
    </select>

    @if ($errors->has($field['key'])) <small class="form-text text-danger">{{ $errors->first($field['key']) }}</small><br> @endif
    <small class="form-text text-dark">{{ $field['description'] }}</small>
</div>
