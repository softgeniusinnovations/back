<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class KycFormSubmit extends FormRequest
{
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'full_name'  => 'required',
            'document_type' => 'required|array|min:1',
            'document_type*' => 'required|in:National Identity card,Passport,License',
            'front_page_of_document' => 'required|mimes:jpg,jpeg,png,pdf',
            'back_page_of_documents' => 'nullable|mimes:jpg,jpeg,png,pdf',
            'document_number' => 'required',
        ];
    }
}
