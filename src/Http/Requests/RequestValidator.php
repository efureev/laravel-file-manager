<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Http\Requests;

use Alexusmai\LaravelFileManager\Rules\DiskExist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;

class RequestValidator extends FormRequest
{
    use CustomErrorMessage;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'disk' => [
                'sometimes',
                'string',
                DiskExist::class,
            ],
            'path' => [
                'sometimes',
                'string',
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && !Storage::disk($this->input('disk'))->exists($value)) {
                        $fail('pathNotFound');
                    }
                },
            ],
        ];
    }

    /**
     * Not found message
     *
     * @return array|\Illuminate\Contracts\Translation\Translator|string|null
     */
    public function message()
    {
        return 'notFound';
    }
}
