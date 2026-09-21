<?php

use App\Models\Loot;
use Illuminate\Database\Migrations\Migration;
use MongoDB\Client;

/**
 * Uppercase every stored loot record type.
 *
 * The loot hiscores match on RuneLite's LootRecordType exactly (NPC, EVENT,
 * PICKPOCKET…), but the API used to store the type as the client sent it. Any
 * record saved in another casing became unreachable: its source still appeared
 * in the loot directory, but the source page matched nothing and 404'd — and
 * the directory filed it under "Other" instead of its real section.
 *
 * The API now uppercases on write; this brings the records already stored in
 * line. It only touches string types that are not already uppercase, so it is a
 * no-op on data that was correct, and safe to re-run. Null types are left alone:
 * they are legacy drops recorded before types existed, which the hiscores treat
 * as "Other" on purpose.
 */
return new class extends Migration
{
    public function up(): void
    {
        $client = new Client(config('database.connections.mongodb.dsn'));
        $collection = $client->{config('database.connections.mongodb.database')}->{(new Loot)->getTable()};

        $collection->updateMany(
            [
                'type' => ['$type' => 'string'],
                '$expr' => ['$ne' => ['$type', ['$toUpper' => '$type']]],
            ],
            [['$set' => ['type' => ['$toUpper' => '$type']]]],
        );
    }

    /**
     * Not reversible: the original casing was never recorded, and restoring it
     * would only reintroduce the mismatch this fixes.
     */
    public function down(): void {}
};
