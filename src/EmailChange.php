<?php

namespace EmailChangeVerification;

use EmailChangeVerification\Broker\BrokerInterface;
use EmailChangeVerification\Token\TokenRepositoryInterface;
use EmailChangeVerification\User\HasEmailChangeVerification;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed verify( array $credentials, \Closure $callback )
 * @method static string sendVerificationLink( array $credentials, string $newEmail )
 * @method static HasEmailChangeVerification getUser( array $credentials )
 * @method static string createToken( HasEmailChangeVerification $user, string $newEmail )
 * @method static void deleteToken( HasEmailChangeVerification $user )
 * @method static bool tokenExists( HasEmailChangeVerification $user, string $token, string $newEmail )
 * @method static TokenRepositoryInterface getRepository()
 * @method static BrokerInterface broker( string|null $name = null )
 *
 * @see Broker
 */
class EmailChange extends Facade
{
    /**
     * Constant representing a successfully sent reminder.
     *
     * @var string
     */
    const VERIFICATION_LINK_SENT = BrokerInterface::VERIFICATION_LINK_SENT;

    /**
     * Constant representing a successfully email changed.
     *
     * @var string
     */
    const EMAIL_CHANGED = BrokerInterface::EMAIL_CHANGED;

    /**
     * Constant representing the user not found response.
     *
     * @var string
     */
    const INVALID_USER = BrokerInterface::INVALID_USER;

    /**
     * Constant representing an invalid token.
     *
     * @var string
     */
    const INVALID_TOKEN = BrokerInterface::INVALID_TOKEN;

    /**
     * Constant representing a throttled verification attempt.
     *
     * @var string
     */
    const EMAIL_THROTTLED = BrokerInterface::EMAIL_THROTTLED;

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'auth.email_changes';
    }
}
