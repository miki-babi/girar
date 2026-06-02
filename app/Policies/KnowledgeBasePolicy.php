<?php

namespace App\Policies;

use App\Models\KnowledgeBase;
use App\Models\KnowledgeTopic;
use App\Models\User;

class KnowledgeBasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, KnowledgeBase $knowledgeBase): bool
    {
        return $this->ownedByUser($user, $knowledgeBase);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, KnowledgeBase $knowledgeBase): bool
    {
        return $this->ownedByUser($user, $knowledgeBase);
    }

    public function delete(User $user, KnowledgeBase $knowledgeBase): bool
    {
        return $this->ownedByUser($user, $knowledgeBase);
    }

    private function ownedByUser(User $user, KnowledgeBase $knowledgeBase): bool
    {
        return KnowledgeTopic::query()
            ->whereKey($knowledgeBase->knowledge_topic_id)
            ->where('business_id', $user->id)
            ->exists();
    }
}
