<?php

namespace TechStudio\Blog\app\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;
use TechStudio\Blog\app\Rules\FilePrefix;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
             'content.*.url' => ['string', new FilePrefix()],
        ];
    }
}
