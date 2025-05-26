<?php

namespace App\Observers;

use App\Models\Address;

class AddressObserver
{
    public function creating(Address $address): void
    {
        if($address->is_primary){
            $address->user->addresses()->update(['is_primary' => false]);
        }
    }

    public function created(Address $address): void
    {
    }

    public function updating(Address $address): void
    {
        if($address->is_primary){
            $address->user->addresses()->update(['is_primary' => false]);
        }
    }

    public function updated(Address $address): void
    {
    }

    public function saving(Address $address): void
    {
    }

    public function saved(Address $address): void
    {
    }

    public function deleting(Address $address): void
    {
    }

    public function deleted(Address $address): void
    {
    }

    public function restoring(Address $address): void
    {
    }

    public function restored(Address $address): void
    {
    }

    public function retrieved(Address $address): void
    {
    }

    public function forceDeleting(Address $address): void
    {
    }

    public function forceDeleted(Address $address): void
    {
    }

    public function replicating(Address $address): void
    {
    }
}
