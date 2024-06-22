<?php

namespace TechStudio\Blog\app\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;
use TechStudio\Blog\app\Models\Article;
use TechStudio\Blog\app\Rules\FilePrefix;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->id) {
            $article = Article::query()->where('id', $this->id)->where('language', $this->route('locale'))->firstOrFail();

            $user = auth()->user();

            if (! $user->can('blogs')) {
                return $article->author_id == $user->id;
            }
        }

        return true;
    }

    public function rules(): array
    {
        return [
//             'content.*.url' => ['string', new FilePrefix()],
        ];
    }
}
