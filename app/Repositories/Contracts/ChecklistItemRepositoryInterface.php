<?php

namespace App\Repositories\Contracts;

use App\Models\ChecklistItem;

interface ChecklistItemRepositoryInterface
{
    public function findOwnedByUser(int $userId, int $checklistItemId): ?ChecklistItem;

    public function save(ChecklistItem $checklistItem): bool;
}
