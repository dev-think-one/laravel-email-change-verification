<?php

namespace EmailChangeVerification\User;

interface HasEmailChangeVerification
{
    /**
     * Get the e-mail address where email change verification links are sent.
     *
     * @return string
     */
    public function getEmailForChangeEmail(): string;

    /**
     * Send the verification notification.
     *
     * @param string $token
     * @param string $newEmail
     *
     * @return void
     */
    public function sendEmailChangeNotification(string $token, string $newEmail);
}
