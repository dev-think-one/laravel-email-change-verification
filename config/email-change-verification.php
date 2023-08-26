<?php

/*
|--------------------------------------------------------------------------
| Email Change Verification
|--------------------------------------------------------------------------
|
|
*/

return [

    'default' => 'users',

    'brokers' => [
        'users' => [
            'provider' => 'users',
            'table'    => 'email_changes',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],
];
