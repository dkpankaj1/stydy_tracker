<?php

namespace App\Repositories;

use App\Models\ChecklistItem;
use App\Repositories\Contracts\ChecklistItemRepositoryInterface;

class EloquentChecklistItemRepository implements ChecklistItemRepositoryInterface
{
    public function findOwnedByUser(int $userId, int $checklistItemId): ?ChecklistItem
    {
        return ChecklistItem::query()
            ->where('user_id', $userId)
            ->find($checklistItemId);
    }

    public function save(ChecklistItem $checklistItem): bool
    {
        return $checklistItem->save();
    }
}
