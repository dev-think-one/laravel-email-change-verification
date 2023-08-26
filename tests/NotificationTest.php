<?php

namespace EmailChangeVerification\Tests;

use EmailChangeVerification\EmailChange;
use EmailChangeVerification\Notifications\EmailChangeNotification;
use EmailChangeVerification\Tests\Fixtures\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

class NotificationTest extends TestCase
{

    /** @test */
    public function send_mail()
    {
        Route::get('/foo', fn () => null)->name('email.change.verification');
        $currentEmail = 'old@test.home';
        $newEmail     = 'new@test.home';

        $user = User::factory()->create([
            'email' => $currentEmail,
        ]);

        EmailChange::sendVerificationLink([
            'email' => $user->email,
        ], $newEmail);


        Notification::assertSentTo(
            [$user],
            EmailChangeNotification::class,
            function (EmailChangeNotification $notification, $channels) use ($user, $newEmail) {
                $this->assertTrue(in_array('mail', $channels));

                /** @var MailMessage $mailMessage */
                $mailMessage = $notification->toMail($user);

                $this->assertInstanceOf(MailMessage::class, $mailMessage);

                $render = $mailMessage->render()?->toHtml();

                $this->assertStringContainsString("New Email is: {$newEmail}.", $render);

                return true;
            }
        );
    }

    /** @test */
    public function send_mail_custom_url_callback()
    {
        Route::get('/foo', fn () => null)->name('email.change.verification');
        $currentEmail = 'old@test.home';
        $newEmail     = 'new@test.home';

        $user = User::factory()->create([
            'email' => $currentEmail,
        ]);

        EmailChange::sendVerificationLink([
            'email' => $user->email,
        ], $newEmail);

        EmailChangeNotification::createUrlUsing(function (User $notifiable, string $token) use ($newEmail) {

            $this->assertTrue(EmailChange::tokenExists($notifiable, $token, $newEmail));

            return 'foo_bar_link';
        });

        Notification::assertSentTo(
            [$user],
            EmailChangeNotification::class,
            function (EmailChangeNotification $notification, $channels) use ($user, $newEmail, $currentEmail) {
                $this->assertTrue(in_array('mail', $channels));

                /** @var MailMessage $mailMessage */
                $mailMessage = $notification->toMail($user);

                $this->assertInstanceOf(MailMessage::class, $mailMessage);

                $render = $mailMessage->render()?->toHtml();

                $this->assertStringContainsString("New Email is: {$newEmail}.", $render);

                $this->assertStringContainsString('foo_bar_link', $render);

                return true;
            }
        );
    }

    /** @test */
    public function send_mail_custom_callback()
    {
        Route::get('/foo', fn () => null)->name('email.change.verification');
        $currentEmail = 'old@test.home';
        $newEmail     = 'new@test.home';

        $user = User::factory()->create([
            'email' => $currentEmail,
        ]);

        EmailChange::sendVerificationLink([
            'email' => $user->email,
        ], $newEmail);

        EmailChangeNotification::toMailUsing(function (User $notifiable, string $token) use ($newEmail) {
            $this->assertTrue(EmailChange::tokenExists($notifiable, $token, $newEmail));

            return 'foo_bar_mail';
        });

        Notification::assertSentTo(
            [$user],
            EmailChangeNotification::class,
            function (EmailChangeNotification $notification, $channels) use ($user, $newEmail, $currentEmail) {
                $this->assertTrue(in_array('mail', $channels));

                $this->assertEquals('foo_bar_mail', $notification->toMail($user));

                return true;
            }
        );
    }
}
