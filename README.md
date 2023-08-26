# Laravel: Email change verification

![Packagist License](https://img.shields.io/packagist/l/think.studio/laravel-email-change-verification?color=%234dc71f)
[![Packagist Version](https://img.shields.io/packagist/v/think.studio/laravel-email-change-verification)](https://packagist.org/packages/think.studio/laravel-email-change-verification)
[![Total Downloads](https://img.shields.io/packagist/dt/think.studio/laravel-email-change-verification)](https://packagist.org/packages/think.studio/laravel-email-change-verification)
[![Build Status](https://scrutinizer-ci.com/g/dev-think-one/laravel-email-change-verification/badges/build.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-email-change-verification/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/dev-think-one/laravel-email-change-verification/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-email-change-verification/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dev-think-one/laravel-email-change-verification/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-email-change-verification/?branch=main)

Package allow to add verification for new email when user change email

## Installation

You can install the package via composer:

```bash
composer require think.studio/laravel-email-change-verification

php artisan vendor:publish --provider="EmailChangeVerification\ServiceProvider" --tag="config"
```

## Configuration and usage

1. Create migration. Package not provide default migrations, so you will need create table manually. But packages provide class with default columns.

```php
public function up() {
    Schema::create('email_changes', function (\Illuminate\Database\Schema\Blueprint $table) {
        \EmailChangeVerification\Database\MigrationHelper::defaultColumns($table);
    });
}

public function down() {
    Schema::dropIfExists('email_changes');
}
```

```shell
php artisan migrate
```

2. Update User model
   
```php
use EmailChangeVerification\User\HasEmailChangeVerification;
use EmailChangeVerification\User\WithEmailChangeVerification;

class User extends Authenticatable implements HasEmailChangeVerification
{
    use WithEmailChangeVerification;
    // ...
}
```

3. Send verification on email change

```php
if ($user->email != $request->input('email')) {
    $status = EmailChange::sendVerificationLink([
        'email' => $user->email,
    ], $request->input('email'));
    if ($status == EmailChange::VERIFICATION_LINK_SENT) {
        $successMessage = __($status);
    } else {
        throw ValidationException::withMessages([
            'email' => __($status),
        ]);
    }
}
```

4. Verify new email

```php
// routes
Route::get( '/email-change-verification/{token}', [
             \App\Http\Controllers\Dashboard\ProfileController::class,
             'verifyNewEmail',
         ] )->name('email.change.verification');
```
```php
// controller
public function verifyNewEmail( Request $request, string $token ) {

    $validator = Validator::make(
        array_merge($request->all(), [ 'token'     => $token, ]), [
        'email'     => [ 'required', 'email' ],
        'new_email' => [ 'required', 'email' ],
        'token'     => [ 'required', 'string', 'max:64' ],
    ] );

    if($validator->fails()) {
        abort(404);
    }

    $status = EmailChange::verify( [
        'email'     => $request->input( 'email', '' ),
        'new_email' => $request->input( 'new_email', '' ),
        'token'     => $token,
    ], function ( $user, string $newEmail ) {
        // user manipulation
        $user->email = $newEmail;
        $user->save();
    } );

    if ( $status != EmailChange::EMAIL_CHANGED ) {
        return __( $status ); // return view or redirect
    }

    return 'Success'; // return view or redirect
}
```
5. Check is request sent

```php
// returns email or null if expired, Example: test@test.com
$lastRequestedEmailChange = EmailChange::getRepository()->lastRequestedEmail($user); 
```


## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)
