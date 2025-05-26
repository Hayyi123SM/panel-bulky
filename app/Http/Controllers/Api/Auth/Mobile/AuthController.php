<?php

namespace App\Http\Controllers\Api\Auth\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\Mobile\LoginRequest;
use App\Http\Requests\Api\Auth\Mobile\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\InvalidStateException;

/**
 * @group Authentication
 *
 * Handles authentication-related actions such as login, logout, registration, and social login.
 *
 * @subgroup Mobile
 */
class AuthController extends Controller
{
    /**
     * Login
     *
     * Logs in a user.
     *
     * @param LoginRequest $request The login request.
     *
     * @return UserResource
     */
    public function login(LoginRequest $request)
    {
        return $request->authenticate();
    }

    /**
     * Logout
     *
     * Logout the authenticated user and delete their access tokens.
     *
     * @param Request $request
     * @return JsonResponse
     * @authenticated
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Register
     *
     * Register a new user.
     *
     * @param RegisterRequest $request The register request instance.
     * @return UserResource The newly registered user as a resource with additional token.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'username' => $request->username,
        ]);

        event(new Registered($user));

        $token = $user->createToken($request->input('device_name'))->plainTextToken;

        return (new UserResource($user))->additional(['token' => $token]);
    }

    /**
     * Google OAuth
     *
     * Handles Google OAuth login and user registration.
     *
     * @param Request $request The HTTP request containing the token and device name.
     * @return UserResource A resource representation of the authenticated user, including the token and user status.
     *
     * @throws ValidationException If the provided data fails validation.
     * @throws InvalidStateException If the state on the Socialite request is invalid.
     */
    public function google(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_name' => 'required|string',
        ]);

        $googleUser = Socialite::driver('google')->userFromToken($request->input('token'));

        $user = User::updateOrCreate([
            'email' => $googleUser->getEmail(),
        ], [
            'name' => $googleUser->getName(),
            'username' => $this->generateUsernameFromEmail($googleUser->getEmail()),
        ]);

        $isNewUser = $user->wasRecentlyCreated;
        if($isNewUser){
            $user->markEmailAsVerified();
        }

        $token = $user->createToken($request->input('device_name'))->plainTextToken;

        return (new UserResource($user))
            ->additional([
                'token' => $token,
                'is_new_user' => $isNewUser
            ]);
    }

    /**
     * Generates a username from the given email.
     *
     * @param string $email The email address to generate a username from.
     * @return string The generated username.
     */
    private function generateUsernameFromEmail(string $email)
    {
        $username = strstr($email, '@', true);
        $existingUserCount = User::where('username', 'like', "{$username}%")->count();

        if ($existingUserCount > 0) {
            $username .= $existingUserCount + 1;
        }

        return $username;
    }
}
