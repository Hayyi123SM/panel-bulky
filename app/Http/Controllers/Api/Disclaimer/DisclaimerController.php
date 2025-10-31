<?php

namespace App\Http\Controllers\Api\Disclaimer;

use App\Http\Resources\ActiveDisclaimerResource;
use App\Models\Disclaimer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DisclaimerController extends Controller
{
    /**
     * Return the currently active disclaimer. Optionally accept ?locale=xx
     */
    public function active(Request $request)
    {
        $locale = $request->query('locale') ?? $request->header('Accept-Language') ?? config('app.locale');
        // Normalize locale to first part (e.g. "en-US" -> "en")
        if (str_contains($locale, ',')) {
            $locale = explode(',', $locale)[0];
        }
        if (str_contains($locale, '-')) {
            $locale = explode('-', $locale)[0];
        }

        $disclaimer = Disclaimer::where('is_active', true)
            ->whereNull('deleted_at')
            ->latest('updated_at')
            ->first();

        if (! $disclaimer) {
            return response()->json(['data' => null], 200);
        }

        return new ActiveDisclaimerResource($disclaimer->setAppends([])->setHidden([]));
    }
}
