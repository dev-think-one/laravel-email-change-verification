<?php

namespace EmailChangeVerification\Tests;

use EmailChangeVerification\Broker\Broker;
use EmailChangeVerification\EmailChange;
use EmailChangeVerification\Notifications\EmailChangeNotification;
use EmailChangeVerification\Tests\Fixtures\Models\User;
use Illuminate\Support\Facades\Notification;

class SendVerificationTest extends TestCase
{

    /** @test */
    public function send_verification_link()
    {
        $currentEmail = 'old@test.home';
        $newEmail     = 'new@test.home';

        $user = User::factory()->create([
            'email' => $currentEmail,
        ]);

        $status = EmailChange::sendVerificationLink([
            'email' => $user->email,
        ], $newEmail);

        $this->assertEquals(Broker::VERIFICATION_LINK_SENT, $status);

        Notification::assertSentTo(
            [$user],
            EmailChangeNotification::class
        );
    }

    /** @test */
    public function error_if_user_not_found()
    {
        $currentEmail = 'old@test.home';
        $newEmail     = 'new@test.home';

        $user = User::factory()->create([
            'email' => $currentEmail,
        ]);

        $status = EmailChange::sendVerificationLink([
            'email' => "foo.{$user->email}",
        ], $newEmail);


        $this->assertEquals(Broker::INVALID_USER, $status);
    }

    /** @test */
    public function error_if_recently_created_token()
    {
        $currentEmail = 'old@test.home';
        $newEmail     = 'new@test.home';

        $user = User::factory()->create([
            'email' => $currentEmail,
        ]);

        $status = EmailChange::sendVerificationLink([
            'email' => $user->email,
        ], $newEmail);

        $this->assertEquals(Broker::VERIFICATION_LINK_SENT, $status);

        // try again:

        $status = EmailChange::sendVerificationLink([
            'email' => $user->email,
        ], $newEmail);

        $this->assertEquals(Broker::EMAIL_THROTTLED, $status);
    }

    /** @test */
    public function can_be_used_custom_callback()
    {
        $currentEmail = 'old@test.home';
        $newEmail     = 'new@test.home';

        $user = User::factory()->create([
            'email' => $currentEmail,
        ]);

        $status = EmailChange::sendVerificationLink([
            'email' => $user->email,
        ], $newEmail, function ($foundUser, $createdToken, $passedEmail) use ($user, $newEmail) {
            $this->assertIsString($createdToken);
            $this->assertNotEmpty($createdToken);
            $this->assertEquals($newEmail, $passedEmail);
            $this->assertEquals($user->getKey(), $foundUser->getKey());
        });

        $this->assertEquals(Broker::VERIFICATION_LINK_SENT, $status);

    }

    /** @test */
    public function error_if_user_has_not_interface()
    {
        app('config')->set('auth.providers.users.model', \Illuminate\Foundation\Auth\User::class);

        $currentEmail = 'old@test.home';
        $newEmail     = 'new@test.home';

        $user = User::factory()->create([
            'email' => $currentEmail,
        ]);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('User must implement HasEmailChangeVerificationContract interface.');

        EmailChange::sendVerificationLink([
            'email' => $user->email,
        ], $newEmail);
    }

}
