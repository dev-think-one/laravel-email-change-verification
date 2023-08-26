<?php

namespace EmailChangeVerification\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class EmailChangeNotification extends Notification
{
    /**
     * The email verification token.
     *
     * @var string
     */
    public string $token;

    /**
     * The new email.
     *
     * @var string
     */
    public string $newEmail;

    /**
     * The callback that should be used to create the verification email URL.
     *
     * @var \Closure|null
     */
    public static $createUrlCallback;

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

    /**
     * Create a notification instance.
     *
     * @param string $token
     * @param string $newEmail
     */
    public function __construct(string $token, string $newEmail)
    {
        $this->token    = $token;
        $this->newEmail = $newEmail;
    }

    /**
     * Get the notification's channels.
     *
     * @param mixed $notifiable
     *
     * @return array|string
     */
    public function via($notifiable)
    {
        return [ 'mail' ];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        if (static::$createUrlCallback) {
            $url = call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        } else {
            $url = url(route('email.change.verification', [
                'token'     => $this->token,
                'email'     => $notifiable->getEmailForChangeEmail(),
                'new_email' => $this->newEmail,
            ], false));
        }

        return $this->buildMailMessage($url);
    }

    /**
     * Get the email verification notification mail message for the given URL.
     *
     * @param string $url
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        return ( new MailMessage )
            ->subject(__('Email Change Verification'))
            ->line(__('You are receiving this email because we received a email change request for your account.'))
            ->line(__('New Email is: :email.', [ 'email' => $this->newEmail ]))
            ->action(__('Accept Changes'), $url)
            ->line(__('This link will expire in :count minutes.', [ 'count' => config('email-change-verification.brokers.' . config('email-change-verification.default') . '.expire') ]))
            ->line(__('If you did not request a email change, no further action is required.'));
    }

    /**
     * Set a callback that should be used when creating the new email verification button URL.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public static function createUrlUsing($callback)
    {
        static::$createUrlCallback = $callback;
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }
}
