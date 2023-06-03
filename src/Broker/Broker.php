<?php

namespace EmailChangeVerification\Broker;

use Closure;
use EmailChangeVerification\Token\TokenRepositoryInterface;
use EmailChangeVerification\User\HasEmailChangeVerification as HasEmailChangeVerificationContract;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Arr;
use UnexpectedValueException;

class Broker implements BrokerInterface
{
    /**
     * The email change token repository.
     */
    protected TokenRepositoryInterface $tokens;

    /**
     * The user provider implementation.
     */
    protected UserProvider $users;

    /**
     * Create a new email change broker instance.
     *
     * @param  TokenRepositoryInterface  $tokens
     * @param  \Illuminate\Contracts\Auth\UserProvider  $users
     * @return void
     */
    public function __construct(TokenRepositoryInterface $tokens, UserProvider $users)
    {
        $this->users  = $users;
        $this->tokens = $tokens;
    }

    /**
     * Send a verification link to a user.
     *
     * @param array $credentials
     * @param string $newEmail
     * @param Closure|null $callback
     *
     * @return string
     */
    public function sendVerificationLink(array $credentials, string $newEmail, Closure $callback = null)
    {
        // First we will check to see if we found a user at the given credentials and
        // if we did not we will redirect back to this current URI with a piece of
        // "flash" data in the session to indicate to the developers the errors.
        $user = $this->getUser($credentials);

        if (is_null($user)) {
            return static::INVALID_USER;
        }

        if ($this->tokens->recentlyCreatedToken($user)) {
            return static::EMAIL_THROTTLED;
        }

        $token = $this->tokens->create($user, $newEmail);

        if ($callback) {
            $callback($user, $token, $newEmail);
        } else {
            // Once we have the change token, we are ready to send the message out to this
            // user with a link to change their email. We will then redirect back to
            // the current URI having nothing set in the session to indicate errors.
            $user->sendEmailChangeNotification($token, $newEmail);
        }

        return static::VERIFICATION_LINK_SENT;
    }

    /**
     * Verify new email for the given token.
     *
     * @param  array  $credentials
     * @param  \Closure  $callback
     * @return mixed
     */
    public function verify(array $credentials, Closure $callback)
    {
        $user = $this->validateChanges($credentials);

        // If the responses from the validate method is not a user instance, we will
        // assume that it is a redirect and simply return it from this method and
        // the user is properly redirected having an error message on the post.
        if (!$user instanceof HasEmailChangeVerificationContract) {
            return $user;
        }

        $newEmail = $credentials['new_email'];

        // Once the change has been validated, we'll call the given callback with the
        // new password. This gives the user an opportunity to store the password
        // in their persistent storage. Then we'll delete the token and return.
        $callback($user, $newEmail);

        $this->tokens->delete($user);

        return static::EMAIL_CHANGED;
    }

    /**
     * Validate a email change for the given credentials.
     *
     * @param  array  $credentials
     * @return HasEmailChangeVerificationContract|string
     */
    protected function validateChanges(array $credentials)
    {
        if (is_null($user = $this->getUser($credentials))) {
            return static::INVALID_USER;
        }

        if (!$this->tokens->exists($user, $credentials['token'], $credentials['new_email'])) {
            return static::INVALID_TOKEN;
        }

        return $user;
    }

    /**
     * Get the user for the given credentials.
     *
     * @param  array  $credentials
     * @return HasEmailChangeVerificationContract|null
     *
     * @throws \UnexpectedValueException
     */
    public function getUser(array $credentials)
    {
        $credentials = Arr::except($credentials, ['token', 'new_email']);

        $user = $this->users->retrieveByCredentials($credentials);

        if ($user && !($user instanceof HasEmailChangeVerificationContract)) {
            throw new UnexpectedValueException('User must implement HasEmailChangeVerificationContract interface.');
        }

        return $user;
    }

    /**
     * Create a new email change  token for the given user.
     *
     * @param HasEmailChangeVerificationContract $user
     * @param string $newEmail
     *
     * @return string
     */
    public function createToken(HasEmailChangeVerificationContract $user, string $newEmail)
    {
        return $this->tokens->create($user, $newEmail);
    }

    /**
     * Delete password change tokens of the given user.
     *
     * @param  HasEmailChangeVerificationContract  $user
     * @return void
     */
    public function deleteToken(HasEmailChangeVerificationContract $user)
    {
        $this->tokens->delete($user);
    }

    /**
     * Validate the given password cjamhe token.
     *
     * @param HasEmailChangeVerificationContract $user
     * @param string $token
     * @param string $newEmail
     *
     * @return bool
     */
    public function tokenExists(HasEmailChangeVerificationContract $user, string $token, string $newEmail)
    {
        return $this->tokens->exists($user, $token, $newEmail);
    }

    /**
     * Get the password change token repository implementation.
     *
     * @return TokenRepositoryInterface
     */
    public function getRepository()
    {
        return $this->tokens;
    }
}
