# Laravel: Email change verification

Package allow to add verification for new email when user change email

## Installation

You can install the package via composer:

```bash
composer require yaroslawww/laravel-email-change-verification

php artisan vendor:publish --provider="EmailChangeVerification\ServiceProvider" --tag="config"
```

## Configuration and usage

1. Create migration

```injectablephp
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
   
```injectablephp
use EmailChangeVerification\User\HasEmailChangeVerification;
use EmailChangeVerification\User\WithEmailChangeVerification;

class User extends Authenticatable implements HasEmailChangeVerification
{
    use WithEmailChangeVerification;
    // ...
}
```

3. Send verification on email change

```injectablephp
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

```injectablephp
// routes
Route::get( '/email-change-verification/{token}', [
             \App\Http\Controllers\Dashboard\ProfileController::class,
             'verifyNewEmail',
         ] );
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

## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)
