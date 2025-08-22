<?php

namespace App\Policies;

use App\Models\TravelRequest;
use App\Models\User;

class TravelRequestPolicy
{
    public function view(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin() || $travelRequest->user_id === $user->id;
    }

    public function updateStatus(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin();
    }
}