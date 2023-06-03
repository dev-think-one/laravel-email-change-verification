# Laravel: Email change verification

![Packagist License](https://img.shields.io/packagist/l/yaroslawww/laravel-email-change-verification?color=%234dc71f)
[![Packagist Version](https://img.shields.io/packagist/v/yaroslawww/laravel-email-change-verification)](https://packagist.org/packages/yaroslawww/laravel-email-change-verification)
[![Total Downloads](https://img.shields.io/packagist/dt/yaroslawww/laravel-email-change-verification)](https://packagist.org/packages/yaroslawww/laravel-email-change-verification)
[![Build Status](https://scrutinizer-ci.com/g/yaroslawww/laravel-email-change-verification/badges/build.png?b=master)](https://scrutinizer-ci.com/g/yaroslawww/laravel-email-change-verification/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/yaroslawww/laravel-email-change-verification/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yaroslawww/laravel-email-change-verification/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yaroslawww/laravel-email-change-verification/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yaroslawww/laravel-email-change-verification/?branch=master)



Package allow to add verification for new email when user change email

## Installation

You can install the package via composer:

```bash
composer require yaroslawww/laravel-email-change-verification

php artisan vendor:publish --provider="EmailChangeVerification\ServiceProvider" --tag="config"
```

## Configuration and usage

1. Create migration

```php
public function up() {
    // TODO: change temple name to same as email-change-verification provider
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
```injectablephp
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
