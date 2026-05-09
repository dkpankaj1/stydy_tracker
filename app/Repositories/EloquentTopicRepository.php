<?php

namespace App\Repositories;

use App\Models\Topic;
use App\Repositories\Contracts\TopicRepositoryInterface;

class EloquentTopicRepository implements TopicRepositoryInterface
{
    public function findOwnedWithChecklist(int $userId, int $topicId): ?Topic
    {
        return Topic::query()
            ->with(['checklistItems', 'lesson.topics', 'subject.lessons.topics'])
            ->where('user_id', $userId)
            ->find($topicId);
    }

    public function save(Topic $topic): bool
    {
        return $topic->save();
    }
}
