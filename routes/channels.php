<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Live Map — any authenticated user may watch every account's position.
Broadcast::channel('map', fn ($user) => $user !== null);

// Account Show — any authenticated user may watch an account's live data updates
// (profiles are viewable by any logged-in user).
Broadcast::channel('account.{accountId}', fn ($user, $accountId) => $user !== null);
