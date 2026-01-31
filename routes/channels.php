<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('voters', function ($user) {
    return [
        'id' => $user->id,
        'nick_name' => $user->nick_name,
        'role' => $user->role->name ?? 'fan'
    ];
});
