<?php

namespace App\Policies;

use App\Models\ChecklistItem;
use App\Models\User;

class ChecklistItemPolicy
{
    public function update(User $user, ChecklistItem $checklistItem): bool
    {
        return $user->id === $checklistItem->user_id;
    }

    public function delete(User $user, ChecklistItem $checklistItem): bool
    {
        return $user->id === $checklistItem->user_id;
    }
}
