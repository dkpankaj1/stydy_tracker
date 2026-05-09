<?php

namespace App\Repositories\Contracts;

use App\Models\Topic;

interface TopicRepositoryInterface
{
    public function findOwnedWithChecklist(int $userId, int $topicId): ?Topic;

    public function save(Topic $topic): bool;
}
