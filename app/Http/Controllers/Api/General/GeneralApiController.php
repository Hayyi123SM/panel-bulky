<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\General\WholesaleForm\StoreWholesaleFormRequest;
use App\Http\Resources\BankResource;
use App\Http\Resources\ReviewResource;
use App\Jobs\SendBulkWholesaleEmailJob;
use App\Models\Bank;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Settings\WhatsAppFloatingSettings;
use App\Settings\WholesaleFormSetting;

/**
 * @group General
 *
 * Handles API requests for general functionalities.
 */
class GeneralApiController extends Controller
{
    /**
     * Floating Button
     *
     * Handles the floating button functionality by retrieving WhatsApp floating settings
     * and returning them as a JSON response.
     */
    public function floatingButton()
    {
        $data = new WhatsAppFloatingSettings();

        return response()->json([
            'data' => $data->toArray()
        ]);
    }

    /**
     * Budget
     *
     * @subgroup Wholesale Form
     */
    public function wholesaleFormBudget()
    {
        $data = new WholesaleFormSetting();

        return response()->json([
            'data' => $data->budgets
        ]);
    }

    /**
     * Send
     *
     * Handles the submission of the wholesale form by dispatching
     * an email job for each recipient email set in the settings.
     *
     * @param StoreWholesaleFormRequest $request The validated request object containing the form data.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response confirming successful submission.
     * @subgroup Wholesale Form
     */
    public function storeWholesaleForm(StoreWholesaleFormRequest $request)
    {
        $setting = new WholesaleFormSetting();
        $emails = $setting->emails;

        $categories = ProductCategory::whereIn('id', $request->categories)->pluck('name')->implode(', ');

        foreach ($emails as $email) {
            dispatch(new SendBulkWholesaleEmailJob($email, [
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'budget' => $request->budget,
                'categories' => $categories,
            ]));
        }

        return response()->json([
            'message' => 'Wholesale form submitted successfully.'
        ]);
    }

    /**
     * Available Banks
     *
     * Retrieve and return a collection of ordered bank resources.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function banks()
    {
        $banks = Bank::orderBy('name')->get();
        return BankResource::collection($banks);
    }

    /**
     * Fetch Reviews
     *
     * Retrieves the most recent approved reviews, including the associated product details
     * (even if the product has been soft deleted). Limits the result to the 20 latest reviews.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection Returns a collection of reviews wrapped in a resource.
     */
    public function reviews()
    {
        $reviews = Review::orderBy('created_at', 'desc')
            ->whereApproved(true)
            ->with(['product' => function ($query) {
                $query->withTrashed();
            }])
            ->take(20)
            ->get();

        return ReviewResource::collection($reviews);
    }
}
