<?php

namespace TechStudio\Blog\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'skills' => json_decode($this->skills),
            // 'UserEnrolledCount' => 10,
            // 'courseCount' => count($this->courses),
            // 'courses' => CourseResource::collection($this->courses),
        ];
    }
}
