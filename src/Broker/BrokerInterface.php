<?php

namespace EmailChangeVerification\Broker;

use Closure;

interface BrokerInterface
{
    /**
     * Constant representing a successfully sent reminder.
     *
     * @var string
     */
    const VERIFICATION_LINK_SENT = 'email_change.sent';

    /**
     * Constant representing a successfully email changed.
     *
     * @var string
     */
    const EMAIL_CHANGED = 'email_change.changed';

    /**
     * Constant representing the user not found response.
     *
     * @var string
     */
    const INVALID_USER = 'email_change.user';

    /**
     * Constant representing an invalid token.
     *
     * @var string
     */
    const INVALID_TOKEN = 'email_change.token';

    /**
     * Constant representing a throttled email change attempt.
     *
     * @var string
     */
    const EMAIL_THROTTLED = 'email_change.throttled';

    /**
     * Send a verification link to a user.
     *
     * @param array $credentials
     * @param string $newEmail
     * @param Closure|null $callback
     *
     * @return string
     */
    public function sendVerificationLink(array $credentials, string $newEmail, Closure $callback = null);

    /**
     * Verify new email for the given token.
     *
     * @param  array  $credentials
     * @param  \Closure  $callback
     * @return mixed
     */
    public function verify(array $credentials, Closure $callback);
}
