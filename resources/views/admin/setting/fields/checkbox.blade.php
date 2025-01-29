<div class="form-check {{ $errors->has($field['key']) ? ' has-error' : '' }}">
    <input type="hidden" name="{{ $field['key'] }}" value="0">
    <input type="checkbox"
           name="{{ $field['key'] }}"
           value="1"
           class="form-check-input"
           id="{{ $field['key'] }}"
           @if (old($field['key'], \App\Helpers\SettingHelper::getSetting($field['key']))) checked="checked" @endif>
    <label class="form-check-label" for="{{ $field['key'] }}">{{ $field['label'] }}</label>
    <br>
    @if ($errors->has($field['key'])) <small class="form-text text-danger">{{ $errors->first($field['key']) }}</small><br> @endif
    <small class="form-text text-dark">{!! $field['description'] !!}</small>
</div>
