<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UserProfileUpdate extends FormRequest
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
            'firstname' => 'required',
            'lastname' => 'required',
            'dob'       => 'date|nullable',
            'email'    => 'nullable|email|unique:users,email,' . $this->user()->id,
            'mobile'    => 'nullable',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'state' => 'required',
            'city' => 'required',
            'zip' => 'required',
            'address' => 'required'
        ];
    }
}
