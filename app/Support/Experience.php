<?php

namespace App\Support;

/**
 * The OSRS experience table.
 */
class Experience
{
    public const MAX_LEVEL = 99;

    /** @var list<int>|null Experience needed for each level, index 0 = level 1. */
    private static ?array $table = null;

    /** The skill level (1–99) that an amount of experience gives. */
    public static function levelFor(int $xp): int
    {
        $table = self::table();
        $level = 1;

        while ($level < self::MAX_LEVEL && $xp >= $table[$level]) {
            $level++;
        }

        return $level;
    }

    /** The experience at which a level (1–99) is reached. */
    public static function forLevel(int $level): int
    {
        return self::table()[max(1, min(self::MAX_LEVEL, $level)) - 1];
    }

    /**
     * @return list<int>
     */
    private static function table(): array
    {
        if (self::$table !== null) {
            return self::$table;
        }

        $table = [0];
        $points = 0;

        for ($level = 1; $level < self::MAX_LEVEL; $level++) {
            $points += (int) floor($level + 300 * 2 ** ($level / 7));
            $table[] = (int) floor($points / 4);
        }

        return self::$table = $table;
    }
}
