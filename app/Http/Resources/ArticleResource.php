<?php

namespace TechStudio\Blog\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
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
            'title' => $this->title,
            'summary' => $this->summary,
            'type' => $this->type,
            'slug' => $this->slug,
            'bannerUrl' => $this->bannerUrl,
            'bannerUrlMobile' => $this->bannerUrlMobile,
            'publicationDate' => $this->publicationDate,
            'author' => new AthorResource($this->author),
            'category' => new CategoryResource($this->category),
            'tags' => TagResource::collection($this->tags),
            "minutesToRead" => $this->minutesToRead(),
            "information" => $this->information,
            "status" => $this->status,
            'creationDate' => $this->created_at,
            'commentsCounts' => $this->comments->count(),
            'category_id' => $this->category_id,
            'author_id' => $this->author_id,
        ];
    }
}
