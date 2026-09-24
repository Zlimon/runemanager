<?php

namespace App\Http\Requests;

use App\Models\FeedEvent;
use App\Services\Feed\RecordFeedEvent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** A notable in-game event (pet, death, reward, level-up) pushed by the RuneLite plugin. */
class StoreFeedEventRequest extends FormRequest
{
    /** Event types the plugin may push through this generic endpoint. */
    public const TYPES = [
        FeedEvent::TYPE_PET,
        FeedEvent::TYPE_DEATH,
        FeedEvent::TYPE_REWARD,
        FeedEvent::TYPE_LEVEL_UP,
    ];

    /** Access is enforced by the route middleware. */
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
            'type' => ['required', 'string', Rule::in(self::TYPES)],
            'source' => ['nullable', 'string', 'max:255'],
            // Level-ups carry the skill + level (every level is stored; the feed
            // UI filters to milestones).
            'skill' => ['required_if:type,level_up', 'string', 'max:40'],
            'level' => ['required_if:type,level_up', 'integer', 'min:1', 'max:126'],
            ...RecordFeedEvent::positionRules(),
        ];
    }
}
