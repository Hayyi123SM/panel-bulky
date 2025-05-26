<?php

namespace App\Observers;

use App\Models\User;
use App\Traits\PhoneFormater;

class UserObserver
{
    use PhoneFormater;

    public function creating(User $user): void
    {
        $user->username = $user->generateUniqueUsername();
        $user->phone_number = $this->formatIndonesianPhoneNumber($user->phone_number);
    }

    public function updating(User $user): void
    {
        if($user->isDirty(['phone_number'])){
            $user->phone_number = $this->formatIndonesianPhoneNumber($user->phone_number);
        }
    }

    public function deleted(User $user): void
    {
        $user->email = 'deleted_' . $user->email;
    }

    public function restored(User $user): void
    {
        $user->email = str_replace('deleted_', '', $user->email);
    }

    public function forceDeleted(User $user): void
    {
        $user->cart()->delete();
        $user->addresses()->delete();
        $user->banks()->delete();
    }
}
