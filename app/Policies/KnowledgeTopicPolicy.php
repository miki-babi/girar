<?php

namespace App\Policies;

use App\Models\KnowledgeTopic;
use App\Models\User;

class KnowledgeTopicPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, KnowledgeTopic $knowledgeTopic): bool
    {
        return $knowledgeTopic->business_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, KnowledgeTopic $knowledgeTopic): bool
    {
        return $knowledgeTopic->business_id === $user->id;
    }

    public function delete(User $user, KnowledgeTopic $knowledgeTopic): bool
    {
        return $knowledgeTopic->business_id === $user->id;
    }
}
