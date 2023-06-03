<?php

namespace EmailChangeVerification\Token;

use EmailChangeVerification\User\HasEmailChangeVerification as HasEmailChangeVerificationContract;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DatabaseTokenRepository implements TokenRepositoryInterface
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * The Hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * The token database table.
     *
     * @var string
     */
    protected $table;

    /**
     * The hashing key.
     *
     * @var string
     */
    protected $hashKey;

    /**
     * The number of seconds a token should last.
     *
     * @var int
     */
    protected $expires;

    /**
     * Minimum number of seconds before re-redefining the token.
     *
     * @var int
     */
    protected $throttle;

    /**
     * Create a new token repository instance.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @param  string  $table
     * @param  string  $hashKey
     * @param  int  $expires
     * @param  int  $throttle
     * @return void
     */
    public function __construct(
        ConnectionInterface $connection,
        HasherContract $hasher,
        $table,
        $hashKey,
        $expires = 60,
        $throttle = 60
    ) {
        $this->table      = $table;
        $this->hasher     = $hasher;
        $this->hashKey    = $hashKey;
        $this->expires    = $expires * 60;
        $this->connection = $connection;
        $this->throttle   = $throttle;
    }

    /**
     * @inheritDoc
     */
    public function create(HasEmailChangeVerificationContract $user, string $newEmail): string
    {
        $email = $user->getEmailForChangeEmail();

        $this->deleteExisting($user);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken();

        $this->getTable()->insert($this->getPayload($email, $newEmail, $token));

        return $token;
    }

    /**
     * Delete all existing reset tokens from the database.
     *
     * @param  HasEmailChangeVerificationContract  $user
     * @return int
     */
    protected function deleteExisting(HasEmailChangeVerificationContract $user)
    {
        return $this->getTable()->where('email', $user->getEmailForChangeEmail())->delete();
    }

    /**
     * Build the record payload for the table.
     *
     * @param  string  $email
     * @param  string  $newEmail
     * @param  string  $token
     * @return array
     */
    protected function getPayload(string $email, string $newEmail, string $token)
    {
        return [
            'email'      => $email,
            'new_email'  => $newEmail,
            'token'      => $this->hasher->make($token),
            'created_at' => new Carbon,
        ];
    }

    /**
     * @inheritDoc
     */
    public function exists(HasEmailChangeVerificationContract $user, string $token, string $newEmail): bool
    {
        $record = (array) $this->getTable()->where(
            'email',
            $user->getEmailForChangeEmail()
        )->where(
            'new_email',
            $newEmail
        )->first();

        return $record                                     &&
               !$this->tokenExpired($record['created_at']) &&
                 $this->hasher->check($token, $record['token']);
    }

    /**
     * Determine if the token has expired.
     *
     * @param  string  $createdAt
     * @return bool
     */
    protected function tokenExpired($createdAt)
    {
        return Carbon::parse($createdAt)->addSeconds($this->expires)->isPast();
    }

    /**
     * @inheritDoc
     */
    public function recentlyCreatedToken(HasEmailChangeVerificationContract $user): bool
    {
        $record = (array) $this->getTable()->where(
            'email',
            $user->getEmailForChangeEmail()
        )->first();

        return $record && $this->tokenRecentlyCreated($record['created_at']);
    }

    /**
     * @inheritDoc
     */
    public function lastRequestedEmail(HasEmailChangeVerificationContract $user): ?string
    {
        $record = (array) $this->getTable()->where(
            'email',
            $user->getEmailForChangeEmail()
        )->first();

        if ($record && !$this->tokenExpired($record['created_at'])) {
            return $record['new_email'];
        }

        return null;
    }

    /**
     * Determine if the token was recently created.
     *
     * @param  string  $createdAt
     * @return bool
     */
    protected function tokenRecentlyCreated($createdAt)
    {
        if ($this->throttle <= 0) {
            return false;
        }

        return Carbon::parse($createdAt)->addSeconds(
            $this->throttle
        )->isFuture();
    }

    /**
     * Delete a token record by user.
     *
     * @param  HasEmailChangeVerificationContract  $user
     * @return void
     */
    public function delete(HasEmailChangeVerificationContract $user)
    {
        $this->deleteExisting($user);
    }

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired()
    {
        $expiredAt = Carbon::now()->subSeconds($this->expires);

        $this->getTable()->where('created_at', '<', $expiredAt)->delete();
    }

    /**
     * Create a new token for the user.
     *
     * @return string
     */
    public function createNewToken()
    {
        return hash_hmac('sha256', Str::random(40), $this->hashKey);
    }

    /**
     * Get the database connection instance.
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Begin a new database query against the table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getTable()
    {
        return $this->connection->table($this->table);
    }

    /**
     * Get the hasher instance.
     *
     * @return \Illuminate\Contracts\Hashing\Hasher
     */
    public function getHasher()
    {
        return $this->hasher;
    }
}
