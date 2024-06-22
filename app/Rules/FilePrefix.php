<?php

namespace TechStudio\Blog\app\Rules;

use Illuminate\Contracts\Validation\Rule;

class FilePrefix implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return str_starts_with($value, env('FILE_PREFIX', 'https://storage-demo-seller-hub.digikala.com'));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must started with write file prefix.';
    }

}