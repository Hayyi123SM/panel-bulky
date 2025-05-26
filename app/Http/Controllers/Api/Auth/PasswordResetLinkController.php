<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Request Reset Password
     *
     * Validate the incoming request and send a password reset link
     * to the provided email address. If the reset link is successfully
     * sent, return a success response. Otherwise, throw a validation
     * exception with an appropriate message.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse The JSON response containing
     *                                        the status message.
     * @throws \Illuminate\Validation\ValidationException If the email
     *                                                    validation fails.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if($status == Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => __($status)
            ]);
        } else {
            throw ValidationException::withMessages([
                'email' => __($status)
            ]);
        }
    }
}
