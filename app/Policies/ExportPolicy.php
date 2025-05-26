<?php

namespace App\Policies;

use App\Models\User;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExportPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, Export $export): bool
    {
        return $export->user()->is($user);
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, Export $export): bool
    {
    }

    public function delete(User $user, Export $export): bool
    {
    }

    public function restore(User $user, Export $export): bool
    {
    }

    public function forceDelete(User $user, Export $export): bool
    {
    }
}
