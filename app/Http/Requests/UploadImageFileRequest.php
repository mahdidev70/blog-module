<?php

namespace TechStudio\Blog\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class UploadImageFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,
     */
    public function rules(): array
    {
        return [
            'files' => 'required|array',
            'files.*' => 'mimes:jpeg,png,jpg,webp|max:2048'
        ];
    }
}
