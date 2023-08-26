<?php

namespace EmailChangeVerification\Tests\Fixtures\Models;

use EmailChangeVerification\Tests\Fixtures\Factories\UserFactory;
use EmailChangeVerification\User\HasEmailChangeVerification;
use EmailChangeVerification\User\WithEmailChangeVerification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends \Illuminate\Foundation\Auth\User implements HasEmailChangeVerification
{
    use WithEmailChangeVerification;
    use Notifiable;
    use HasFactory;

    protected $table = 'users';

    protected $guarded = [];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
