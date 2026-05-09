<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportSubjectStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'csv_file' => [
                'bail',
                'required',
                'file',
                'max:5120',
                'mimes:csv,txt',
            ],
        ];
    }
}
