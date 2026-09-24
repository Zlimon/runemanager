<?php

namespace App\Models;

use Laravel\Jetstream\Membership as JetstreamMembership;

class Membership extends JetstreamMembership
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * A property rather than a Table attribute: that attribute would also
     * reset the `team_user` table name Jetstream's parent class declares.
     *
     * @var bool
     */
    public $incrementing = true;
}
