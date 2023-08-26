<?php

namespace EmailChangeVerification\Tests;

use EmailChangeVerification\Broker\Broker;
use EmailChangeVerification\EmailChange;
use EmailChangeVerification\Tests\Fixtures\Models\User;

class VerificationTest extends TestCase
{

    protected string $currentEmail;
    protected string $newEmail;
    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currentEmail = 'old@test.home';
        $this->newEmail     = 'new@test.home';

        $this->user = User::factory()->create([
            'email' => $this->currentEmail,
        ]);

        EmailChange::sendVerificationLink([
            'email' => $this->user->email,
        ], $this->newEmail, function ($foundUser, $createdToken, $passedEmail) {
            $this->token = $createdToken;
        });
    }

    /** @test */
    public function verify_email()
    {
        $status = EmailChange::verify([
            'email'     => $this->currentEmail,
            'new_email' => $this->newEmail,
            'token'     => $this->token,
        ], function (User $user, string $newEmail) {
            $this->assertEquals($this->newEmail, $newEmail);
            $this->assertEquals($this->user->getKey(), $user->getKey());
        });

        $this->assertEquals(Broker::EMAIL_CHANGED, $status);
    }

    /** @test */
    public function error_if_user_not_found()
    {
        $status = EmailChange::verify([
            'email'     => "foo.{$this->currentEmail}",
            'new_email' => $this->newEmail,
            'token'     => $this->token,
        ], function (User $user, string $newEmail) {
            $this->assertEquals($this->newEmail, $newEmail);
            $this->assertEquals($this->user->getKey(), $user->getKey());
        });

        $this->assertEquals(Broker::INVALID_USER, $status);
    }

    /** @test */
    public function error_if_incorrect_token()
    {
        $status = EmailChange::verify([
            'email'     => $this->currentEmail,
            'new_email' => $this->newEmail,
            'token'     => 'test',
        ], function (User $user, string $newEmail) {
            $this->assertEquals($this->newEmail, $newEmail);
            $this->assertEquals($this->user->getKey(), $user->getKey());
        });

        $this->assertEquals(Broker::INVALID_TOKEN, $status);
    }
}
