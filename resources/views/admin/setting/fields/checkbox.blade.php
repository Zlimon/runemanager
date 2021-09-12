<div class="form-check {{ $errors->has($field['key']) ? ' has-error' : '' }}">
    <input type="hidden" name="{{ $field['key'] }}" value="0">
    <input type="checkbox"
           name="{{ $field['key'] }}"
           value="1"
           class="form-check-input"
           id="{{ $field['key'] }}"
           @if (old($field['key'], \App\Helpers\SettingHelper::getSetting($field['key']))) checked="checked" @endif>
    <label class="form-check-label" for="{{ $field['key'] }}">{{ $field['label'] }}</label>

    @if ($errors->has($field['key'])) <small class="text-danger">{{ $errors->first($field['key']) }}</small> @endif
    <small class="form-text text-muted">{!! $field['description'] !!}</small>
</div>
