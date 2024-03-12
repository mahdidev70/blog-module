<?php

namespace TechStudio\Blog\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use TechStudio\Core\app\Models\Follow;

class AthorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isFollowed = 0;
        if (Auth('sanctum')->user()) {
            $user = Auth('sanctum')->user();
            $isFollowed = (bool) Follow::where('follower_id', $user->id)
                ->where('following_id', $this->user_id)->first();
        }
        return [
            'id' => $this->user_id,
            'type' => $this->getUserType(),
            'displayName' => $this->getDisplayName(),
            'avatarUrl' => $this->avatar_url,
            'description' => $this->description,
            'isFollowed' => $isFollowed,
        ];
    }
}
