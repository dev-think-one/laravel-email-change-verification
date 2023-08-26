<?php

namespace EmailChangeVerification\Tests;

use Carbon\Carbon;
use EmailChangeVerification\EmailChange;
use EmailChangeVerification\Tests\Fixtures\Models\User;

class ClearEmailChangesCommandTest extends TestCase
{
    /** @test */
    public function clear()
    {
        $user = User::factory()->create([]);

        EmailChange::createToken($user, 'foo@bar.baz');
        $this->assertEquals(1, EmailChange::getRepository()->getConnection()->table('email_changes')->count());

        EmailChange::createToken($user, 'foo@bar.baz');
        $this->assertEquals(1, EmailChange::getRepository()->getConnection()->table('email_changes')->count());

        $this->artisan('auth:clear-email-changes')->assertExitCode(0);

        $this->assertEquals(1, EmailChange::getRepository()->getConnection()->table('email_changes')->count());

        EmailChange::getRepository()->getConnection()->table('email_changes')->update([
            'created_at' => Carbon::now()->subDay(),
        ]);

        $this->artisan('auth:clear-email-changes')->assertExitCode(0);

        $this->assertEquals(0, EmailChange::getRepository()->getConnection()->table('email_changes')->count());
    }
}
