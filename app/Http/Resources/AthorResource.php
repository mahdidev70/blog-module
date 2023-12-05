<?php

namespace TechStudio\Blog\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AthorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->getUserType(),
            'displayName' => $this->getDisplayName(),
            'avatarUrl' => $this->avatar_url,
            'description' => $this->description
        ];
    }
}
