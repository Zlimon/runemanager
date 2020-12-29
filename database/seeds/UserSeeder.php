<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
        	'uuid' => 'd7d865c5-e37f-4228-a1c1-a5190f0f34cb',
        	'name' => 'Simon',
        	'email' => 'simon@runemanager.com',
        	'password' => bcrypt('runemanager1234'),
            'icon_id' => Helper::randomItemId(true),
        ]);
    }
}
