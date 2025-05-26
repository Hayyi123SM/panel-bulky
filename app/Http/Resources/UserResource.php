<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'has_set_password' => !is_null($this->password),
            'profile_picture' => \Storage::disk('public')->url($this->profile_picture),
            'email_verified_at' => $this->email_verified_at,
            'phone_number' => $this->phone_number,
            'notifications_count' => $this->notifications_count,
            'orders_count' => $this->orders_count,
            'read_notifications_count' => $this->read_notifications_count,
            'unread_notifications_count' => $this->unread_notifications_count,
            'video_likes_count' => $this->video_likes_count,
            'videos_count' => $this->videos_count,
            'watch_later_count' => $this->watch_later_count,

            'sub_district_id' => $this->sub_district_id,
        ];
    }
}
