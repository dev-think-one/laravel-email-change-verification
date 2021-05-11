<?php

namespace EmailChangeVerification\User;

use EmailChangeVerification\Notifications\EmailChangeNotification;

trait WithEmailChangeVerification
{

    /**
     * Get the e-mail address where email change verification links are sent.
     *
     * @return string
     */
    public function getEmailForChangeEmail(): string
    {
        return $this->email;
    }

    /**
     * Send the verification notification.
     *
     * @param string $token
     * @param string $newEmail
     *
     * @return void
     */
    public function sendEmailChangeNotification(string $token, string $newEmail)
    {
        $this->notify(new EmailChangeNotification($token, $newEmail));
    }
}
