<?php

namespace TechStudio\Blog\app\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\Blog\AthorResource;
use App\Http\Resources\Blog\CategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
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
            'slug' => $this->slug
        ];
    }
}
