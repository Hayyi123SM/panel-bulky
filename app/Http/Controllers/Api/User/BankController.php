<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Bank\CreateBankRequest;
use App\Http\Requests\Api\User\Bank\UpdateBankRequest;
use App\Http\Resources\UserBankResource;
use App\Models\UserBank;
use Auth;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * @group User
 *
 * @subgroup Banks
 * @authenticated
 */
class BankController extends Controller
{
    /**
     * Get Bank
     *
     * Display a paginated list of the authenticated user's banks.
     *
     * Retrieves the currently authenticated user, fetches their associated banks,
     * paginates the results, and returns the information as a collection of resources.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        $user = Auth::user();
        $banks = $user->banks()->paginate();
        return UserBankResource::collection($banks);
    }

    /**
     * Create
     *
     * Handle the creation of a new bank record for the authenticated user.
     *
     * @param CreateBankRequest $request
     * @return UserBankResource
     */
    public function create(CreateBankRequest $request)
    {
        $user = Auth::user();
        $bank = $user->banks()->create($request->validated());
        return new UserBankResource($bank);
    }

    /**
     * Update
     *
     * Handle the update of an existing bank record for the authenticated user.
     *
     * @param UpdateBankRequest $request
     * @param UserBank $userBank
     * @return UserBankResource
     */
    public function update(UpdateBankRequest $request, UserBank $userBank)
    {
        $userBank->update($request->validated()->except('id'));
        return new UserBankResource($userBank);
    }

    /**
     * Delete
     *
     * Deletes the specified UserBank record and returns a no content response.
     *
     * @param UserBank $userBank The UserBank instance to be deleted.
     * @return Response A response indicating no content.
     */
    public function delete(UserBank $userBank)
    {
        $userBank->delete();
        return response()->noContent();
    }
}
