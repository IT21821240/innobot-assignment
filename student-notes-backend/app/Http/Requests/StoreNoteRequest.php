<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:120'
            ],

            'content' => [
                'required',
                'string',
                'max:1000'
            ],

            'priority' => [
                'required',
                'in:low,medium,high'
            ]
        ];
    }
}