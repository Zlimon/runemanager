<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountDiary;
use App\Support\Diaries;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountDiary>
 */
class AccountDiaryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $diaries = [];
        foreach (Diaries::AREAS as $area) {
            $diaries[$area] = [];
            // Tiers complete in order (you can't finish Hard before Easy), so
            // pick a "progress" cutoff per area for realistic-looking data.
            $progress = $this->faker->numberBetween(0, count(Diaries::TIERS));
            foreach (Diaries::TIERS as $i => $tier) {
                $diaries[$area][$tier] = $i < $progress;
            }
        }

        return [
            'account_id' => Account::factory(),
            'diaries' => $diaries,
        ];
    }
}
