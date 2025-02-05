<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
class SupportTicketStore extends FormRequest
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
            'subject'   => 'required',
            'priority'   => 'required',
            'transaction_id'   => 'nullable',
            'transaction_date'   => 'nullable',
            'message'   => 'required',
            'attachments.*'     => 'nullable|image|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
        ];
    }
}
