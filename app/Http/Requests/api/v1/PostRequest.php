<?php

namespace App\Http\Requests\api\v1;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:255'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:file/pdf', 'max:10240'],
            'images' => ['nullable', 'aray', 'max:10'],
            'images*' => ['file', 'mimes:jpeg,png,jpg', 'mimetypes:image/png,image/jpg,image/jpeg', 'max:10240'],
            'videos' => ['nullable', 'aray', 'max:5'],
            'videos*' => ['file', 'mimes:amv,mp4', 'mimetypes:video/amv,video/mp4', 'max:102400'],
        ];
    }
}
