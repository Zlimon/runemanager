<?php

use App\Models\Inventory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MongoDB\Client;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $client = new Client(config('database.connections.mongodb-client.dsn'));
        $collection = $client->{config('database.connections.mongodb-client.database')}->{(new Inventory)->getTable()};

        // Ensure unique index on account_id field
        $collection->createIndex(
            ['account_id' => 1],
            ['unique' => true]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $client = new Client(config('database.connections.mongodb-client.dsn'));
        $collection = $client->{config('database.connections.mongodb-client.database')}->{(new Inventory)->getTable()};

        $collection->dropIndex('account_id_1');
    }
};
