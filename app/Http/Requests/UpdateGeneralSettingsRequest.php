<?php

namespace App\Http\Requests;

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Services\Hiscores\GroupIronmanValidator;
use App\Services\Instance\ApplyInstanceSettings;
use App\Support\Instance;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateGeneralSettingsRequest extends FormRequest
{
    /** Access is enforced by the admin route's permission middleware. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'instance_mode' => ['required', Rule::in(Instance::MODES)],
            'clan_name' => ['nullable', 'string', 'max:60'],
            'group_name' => ['nullable', 'string', 'max:60'],
            'instance_description' => ['nullable', 'string', 'max:2000'],
            'resource_pack_id' => ['nullable', 'integer', 'exists:resource_packs,id'],
            'default_dark_mode' => ['nullable', 'string', Rule::in(['', 'light', 'dark'])],
            'public_anonymize_accounts' => ['boolean'],
            'confirm' => ['nullable', 'string'],
        ];
    }

    /**
     * Checks that only run once the rules pass, in order, stopping at the first
     * failure — so a missing GIM group is reported before the destructive
     * mode-switch confirmation is even considered.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(GroupIronmanValidator $gimValidator, ApplyInstanceSettings $settings): array
    {
        return [
            function (Validator $validator) use ($gimValidator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                // A transient lookup failure returns null and is allowed through
                // ("validated where possible"); only a definitive "not found" blocks.
                $groupName = trim((string) $this->input('group_name'));

                if ($this->input('instance_mode') === Instance::MODE_GROUP && $groupName !== ''
                    && $groupName !== trim((string) SettingHelper::getSetting('group_name', ''))
                    && $gimValidator->exists($groupName) === false) {
                    $validator->errors()->add('group_name', "No Group Ironman group named \"{$groupName}\" was found on the OSRS hiscores.");
                }
            },
            function (Validator $validator) use ($settings): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                // Confirmation is only demanded when there's account data to lose.
                $mode = $this->input('instance_mode');

                if ($settings->switchesToRoster($mode) && Account::query()->exists()
                    && mb_strtolower(trim((string) $this->input('confirm'))) !== $mode) {
                    $validator->errors()->add('confirm', 'Type the new mode name to confirm — this deletes all account data.');
                }
            },
        ];
    }
}
