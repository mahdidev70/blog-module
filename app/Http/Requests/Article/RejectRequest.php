<?php

namespace TechStudio\Blog\app\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;

class RejectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string'],
        ];
    }
}
