<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Address\CreateAddressRequest;
use App\Http\Requests\Api\User\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @group User
 * @subgroup Address
 * @authenticated
 */
class AddressController extends Controller
{
    /**
     * Address List
     *
     * Retrieves the addresses of the authenticated user.
     *
     * This method retrieves the addresses associated with the currently authenticated user. It first fetches the authenticated user using the `user()` method from the `request()` object. Then, it retrieves the addresses associated with the user by accessing the `addresses` property. Finally, it returns a collection of AddressResource objects by using the `AddressResource::collection()` method, passing in the addresses as the argument.
     *
     * @return ResourceCollection The collection of AddressResource objects representing the addresses.
     */
    public function getAddresses()
    {
        $user = request()->user();
        $addresses = $user->addresses()->orderByDesc('is_primary')->paginate();
        return AddressResource::collection($addresses);
    }

    /**
     * Retrieve Address
     *
     * Retrieves the details of a specific address.
     *
     * This method retrieves the details of a specific address by accepting an instance of the Address model as a parameter. It then creates a new instance of the AddressResource class, passing in the address object as the argument. Finally, it returns the created AddressResource object.
     *
     * @param Address $address The Address model instance representing the address to retrieve the details for.
     * @return AddressResource The AddressResource object representing the details of the address.
     */
    public function getAddressDetail(Address $address)
    {
        return new AddressResource($address);
    }

    /**
     * Creates a new address.
     *
     * This method creates a new address record in the database for the authenticated user.
     *
     * @param CreateAddressRequest $request The request object containing the address data.
     *
     * @return AddressResource Returns an instance of the AddressResource.
     */
    public function createAddress(CreateAddressRequest $request)
    {
        $user = $request->user();
        $address = $user->addresses()->create($request->all());
        return new AddressResource($address);
    }

    /**
     * Edit an existing address.
     *
     * This method updates the details of an existing address record in the database for the authenticated user.
     *
     * @param UpdateAddressRequest $request The request object containing the updated address data.
     *
     * @return AddressResource Returns an instance of the AddressResource for the updated address.
     *
     * @throws ModelNotFoundException   if the address with the provided ID is not found.
     */
    public function editAddress(UpdateAddressRequest $request)
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($request->input('address_id'));
        $address->update($request->all());
        return new AddressResource($address);
    }

    /**
     * Delete Address
     *
     * Deletes an address from the database.
     *
     * @param Address $address The address instance to be deleted.
     * @return JsonResponse The JSON response indicating the success of the operation.
     */
    public function deleteAddress(Address $address)
    {
        $address->delete();
        return response()->json([
            'message' => 'Alamat berhasil dihapus',
            'data' => [
                'success' => true,
            ]
        ]);
    }

    /**
     * Set Primary
     *
     * Sets the given address as the primary address for the user and updates the database accordingly.
     *
     * @param Address $address The address instance to be set as primary.
     * @return AddressResource A resource representation of the updated address.
     */
    public function setPrimary(Address $address)
    {
        $address->update(['is_primary' => true]);
        return new AddressResource($address);
    }
}
