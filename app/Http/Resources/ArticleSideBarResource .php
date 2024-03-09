<?php

namespace TechStudio\Blog\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleSideBarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'articleStar' => [
                'id' => $this->id,
                'title' => $this->title,
                'author' => new AthorResource($this->author),
                'star' => $this->star,
            ],
            'popularAuthor' => [
                'author'=> new AthorResource($this->author)
            ],
        ];
    }
}
