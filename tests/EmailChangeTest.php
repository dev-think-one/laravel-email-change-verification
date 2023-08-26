<?php


namespace EmailChangeVerification\Tests;

use EmailChangeVerification\Broker\Broker;
use EmailChangeVerification\Broker\BrokerManager;
use EmailChangeVerification\EmailChange;
use EmailChangeVerification\Token\TokenRepositoryInterface;
use EmailChangeVerification\User\HasEmailChangeVerification;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Str;

class EmailChangeTest extends TestCase
{

    /** @test */
    public function send_link()
    {
        $currentEmail = 'old@test.home';
        $newEmail     = 'new@test.home';
        $token        = Str::random();

        $mockedProvider      = $this->mock(EloquentUserProvider::class);
        $mockUser            = $this->mock(HasEmailChangeVerification::class);
        $mockTokenRepository = $this->mock(TokenRepositoryInterface::class);

        $mockUser->shouldReceive('sendEmailChangeNotification');

        $mockedProvider->shouldReceive('retrieveByCredentials')
                       ->once()
                       ->andReturn($mockUser);


        $mockTokenRepository->shouldReceive('recentlyCreatedToken')
                            ->once()
                            ->andReturn(false);

        $mockTokenRepository->shouldReceive('create')
                            ->once()
                            ->andReturn($token);

        $this->app->instance('auth.email_changes', $this->partialMock(BrokerManager::class, function ($mock) use ($mockTokenRepository, $mockedProvider) {
            $mock
                ->shouldAllowMockingProtectedMethods()
                ->shouldReceive('resolve')
                ->once()
                ->andReturn(new Broker($mockTokenRepository, $mockedProvider));

            $mock
                ->shouldReceive('getDefaultDriver')
                ->once()
                ->andReturn('users');
        }));

        $status = EmailChange::sendVerificationLink([
            'email' => $currentEmail,
        ], $newEmail);
    }
}
