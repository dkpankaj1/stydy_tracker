<?php

namespace App\Services;

use App\Models\ChecklistItem;
use App\Repositories\Contracts\ChecklistItemRepositoryInterface;
use App\Repositories\Contracts\TopicRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ChecklistService
{
    public function __construct(
        private readonly ChecklistItemRepositoryInterface $checklistItems,
        private readonly TopicRepositoryInterface $topics,
        private readonly ProgressService $progressService,
    ) {
    }

    public function toggle(int $userId, ChecklistItem $checklistItem): ChecklistItem
    {
        return DB::transaction(function () use ($userId, $checklistItem): ChecklistItem {
            $ownedChecklistItem = $this->checklistItems->findOwnedByUser($userId, $checklistItem->id);

            if ($ownedChecklistItem === null) {
                abort(404);
            }

            $ownedChecklistItem->is_completed = !$ownedChecklistItem->is_completed;
            $ownedChecklistItem->completed_at = $ownedChecklistItem->is_completed ? now() : null;
            $this->checklistItems->save($ownedChecklistItem);

            $topic = $this->topics->findOwnedWithChecklist($userId, $ownedChecklistItem->topic_id);
            if ($topic !== null) {
                $this->progressService->recalculateForTopic($topic);
            }

            return $ownedChecklistItem->refresh();
        });
    }
}
