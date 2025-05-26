<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Profile\DeleteAccountRequest;
use App\Http\Requests\Api\User\Profile\UpdateProfileRequest;
use App\Http\Resources\UserBankResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * @group User
 *
 * @subgroup Profile
 * @authenticated
 */
class ProfileController extends Controller
{
    /**
     * Update Profile
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->all());

        return new UserResource($user);
    }

    /**
     * Change Profile Picture
     *
     * Handles profile picture change for the authenticated user.
     *
     * Validates the image input, ensuring it meets the required
     * criteria: file type and size. Saves the uploaded image to
     * a specific directory, updates the user's profile picture
     * path in the database, and returns the updated user resource.
     *
     * @param Request $request The HTTP request containing the profile picture.
     *
     * @return UserResource The updated user resource.
     * @throws ValidationException If the uploaded image is invalid or missing.
     *
     */
    public function changeProfilePicture(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        if(!$request->hasFile('image')) {
            throw ValidationException::withMessages([
                'images' => 'Gambar tidak valid.'
            ]);
        }

        $image = $request->file('image');
        $path = 'public/profile/' . $image->hashName();
        $image->storePubliclyAs('public/profile/', $image->hashName());

        $user->profile_picture = $path;
        $user->save();

        return new UserResource($user);
    }

    /**
     * Delete Account
     *
     * Handle the account deletion process for an authenticated user.
     *
     * @param DeleteAccountRequest $request The request object containing validation and authenticated user data.
     * @return \Illuminate\Http\JsonResponse JSON response confirming account deletion.
     */
    public function deleteAccount(DeleteAccountRequest $request)
    {
        $user = $request->user();
        $user->delete();

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Akun berhasil dihapus.'
        ]);
    }
}
