<?php

namespace App\Http\Controllers\Api\Testimony;

use App\Http\Controllers\Controller;
use App\Http\Resources\TestimonyResource;
use App\Models\Testimony;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Testimony
 *
 * Handles operations related to testimonies.
 */
class TestimonyController extends Controller
{
    /**
     * Get Testimony
     *
     * Retrieves a collection of testimonies based on the user's request.
     *
     * @param Request $request The incoming HTTP request.
     * @return AnonymousResourceCollection Returns a collection of TestimonyResource objects.
     */
    public function index(Request $request)
    {
        $request->validate([
            'take' => 'nullable|integer|min:3',
        ]);

        $testimony = Testimony::take($request->input('take', 3))->get();
        return TestimonyResource::collection($testimony);
    }
}
