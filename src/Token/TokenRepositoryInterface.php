<?php

namespace EmailChangeVerification\Token;

use EmailChangeVerification\User\HasEmailChangeVerification as HasEmailChangeVerificationContract;

interface TokenRepositoryInterface
{
    /**
     * Create a new token.
     *
     * @param HasEmailChangeVerificationContract $user
     * @param string $newEmail
     *
     * @return string
     */
    public function create(HasEmailChangeVerificationContract $user, string $newEmail): string;

    /**
     * Determine if a token record exists and is valid.
     *
     * @param HasEmailChangeVerificationContract $user
     * @param string $token
     * @param string $newEmail
     *
     * @return bool
     */
    public function exists(HasEmailChangeVerificationContract $user, string $token, string $newEmail): bool;

    /**
     * Determine if the given user recently created a password reset token.
     *
     * @param HasEmailChangeVerificationContract $user
     *
     * @return bool
     */
    public function recentlyCreatedToken(HasEmailChangeVerificationContract $user): bool;

    /**
     * Get Last Requested Email
     *
     * @param HasEmailChangeVerificationContract $user
     *
     * @return string|null
     */
    public function lastRequestedEmail(HasEmailChangeVerificationContract $user): ?string;

    /**
     * Delete a token record.
     *
     * @param HasEmailChangeVerificationContract $user
     *
     * @return void
     */
    public function delete(HasEmailChangeVerificationContract $user);

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired();
}
