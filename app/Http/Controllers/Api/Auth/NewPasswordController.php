<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * @group Authentication
 *
 * Class NewPasswordController
 *
 * Handles requests related to resetting user passwords.
 */
class NewPasswordController extends Controller
{
    /**
     * Reset Password
     *
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if($status == Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status)
            ]);
        } else {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}
