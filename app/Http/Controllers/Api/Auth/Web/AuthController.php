<?php

namespace App\Http\Controllers\Api\Auth\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\Web\LoginRequest;
use App\Http\Requests\Api\Auth\Web\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Socialite;

/**
 * @group Authentication
 *
 * Log in the user.
 *
 * @param LoginRequest $request The login request object containing user credentials.
 *
 * @return UserResource The resource representing the logged-in user.
 *
 * @subgroup Web
 */
class AuthController extends Controller
{
    /**
     * Login
     *
     * Log in the user.
     *
     * @param LoginRequest $request The login request object containing user credentials.
     *
     * @return UserResource The resource representing the logged-in user.
     */
    public function login(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return new UserResource($request->user());
    }

    /**
     * Logout
     *
     * Logs out the currently authenticated user.
     *
     * @param Request $request The HTTP request containing the user's session details.
     *
     * @return JsonResponse The JSON response indicating the success status.
     *
     * @authenticated
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Register
     *
     * Register a new user.
     *
     * @param RegisterRequest $request The registration request.
     *
     * @return UserResource The newly created user resource.
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

        Auth::login($user);

        return new UserResource($user);
    }

    /**
     * Google OAuth
     *
     * Initiates the Google OAuth authentication process and returns the redirect URL.
     *
     * @return JsonResponse A JSON response containing the Google authentication redirect URL.
     */
    public function google()
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json([
            'redirect_url' => $url,
        ]);
    }

    /**
     * Google OAuth Callback
     *
     * Handles the Google OAuth callback and logs in or registers the user.
     *
     * @return JsonResource The authenticated user's resource with additional data.
     */
    public function googleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

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

        Auth::login($user);

        return (new UserResource($user))->additional([
            'is_new_user' => $isNewUser,
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
