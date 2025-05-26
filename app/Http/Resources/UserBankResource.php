<?php

namespace App\Http\Resources;

use App\Models\UserBank;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserBank */
class UserBankResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'bank' => new BankResource($this->bank),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
