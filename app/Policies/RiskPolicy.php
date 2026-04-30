<?php

namespace App\Policies;

use App\Models\Risk;
use App\Models\User;

class RiskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('risk.view');
    }

    public function view(User $user, Risk $risk): bool
    {
        return $user->can('risk.view');
    }

    public function create(User $user): bool
    {
        return $user->can('risk.create');
    }

    public function update(User $user, Risk $risk): bool
    {
        if (! $user->can('risk.update')) {
            return false;
        }

        // SoD: właściciel kontroli powiązanej z ryzykiem nie może edytować ryzyka jako Risk Owner
        // (gdy mamy taki link). Na MVP — tylko prosty check.
        return true;
    }

    public function delete(User $user, Risk $risk): bool
    {
        return $user->can('risk.delete');
    }
}
