<?php

namespace EmailChangeVerification\Tests;

use EmailChangeVerification\EmailChange;
use EmailChangeVerification\Tests\Fixtures\Models\User;
use EmailChangeVerification\Token\TokenRepositoryInterface;
use Illuminate\Hashing\HashManager;

class TokenManipulationTest extends TestCase
{

    /** @test */
    public function create_token()
    {
        $user = User::factory()->create([]);

        $token = EmailChange::createToken($user, 'foo@bar.baz');

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    /** @test */
    public function delete_token()
    {
        $user = User::factory()->create([]);

        $token = EmailChange::createToken($user, 'foo@bar.baz');

        $this->assertTrue(EmailChange::tokenExists($user, $token, 'foo@bar.baz'));

        EmailChange::deleteToken($user);

        $this->assertFalse(EmailChange::tokenExists($user, $token, 'foo@bar.baz'));
    }

    /** @test */
    public function get_repository()
    {
        $repository = EmailChange::getRepository();

        $this->assertInstanceOf(TokenRepositoryInterface::class, $repository);
    }

    /** @test */
    public function get_latest_requested_email()
    {
        $user = User::factory()->create([]);

        EmailChange::createToken($user, 'foo@bar.baz');

        $lastRequestedEmailChange = EmailChange::getRepository()->lastRequestedEmail($user);

        $this->assertEquals('foo@bar.baz', $lastRequestedEmailChange);
    }

    /** @test */
    public function get_latest_requested_email_return_null_if_not_exists()
    {
        $user = User::factory()->create([]);

        $lastRequestedEmailChange = EmailChange::getRepository()->lastRequestedEmail($user);

        $this->assertNull($lastRequestedEmailChange);
    }

    /** @test */
    public function get_hasher()
    {
        $hasher = EmailChange::getRepository()->getHasher();


        $this->assertInstanceOf(HashManager::class, $hasher);
    }

    /** @test */
    public function get_connection()
    {
        $connection = EmailChange::getRepository()->getConnection();


        // test use SQLite
        $this->assertInstanceOf(\Illuminate\Database\SQLiteConnection::class, $connection);
    }

}
