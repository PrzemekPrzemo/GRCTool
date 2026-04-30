<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('asset.view');
    }

    public function view(User $user, Asset $asset): bool
    {
        return $user->can('asset.view');
    }

    public function create(User $user): bool
    {
        return $user->can('asset.create');
    }

    public function update(User $user, Asset $asset): bool
    {
        return $user->can('asset.update') || ($user->can('asset.update') && $asset->owner_id === $user->id);
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $user->can('asset.delete');
    }
}
