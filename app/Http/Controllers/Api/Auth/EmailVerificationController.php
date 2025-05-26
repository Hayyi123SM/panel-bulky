<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


/**
 * @group Authentication
 *
 * Email Verification Controller
 *
 * Handles requests related to email verification. Provides functionality
 * to verify an email address and returns the result in JSON format.
 */
class EmailVerificationController extends Controller
{
    /**
     * Email Verification
     *
     * Handle the email verification request and fulfill it.
     * Responds with a JSON indicating the success status.
     *
     * @param EmailVerificationRequest $request The incoming request for email verification.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating the operation status.
     * @authenticated
     */
    public function index(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return response()->json([
            'status' => 'success',
        ]);
    }
}
