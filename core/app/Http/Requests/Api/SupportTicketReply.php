<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
class SupportTicketReply extends FormRequest
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
            'subject'     => 'required_without:ticket_reply|max:255',
            'user_type'    => 'required',
            'priority'    => 'required_without:ticket_reply|in:1,2,3',
            'message'     => 'required',
            'attachments.*'     => 'nullable|image|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
        ];
    }
}
