<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UserLogin extends FormRequest
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
        $firstCredentialValue = $this->username;
        $firstCredentialValueType = filter_var($firstCredentialValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $firstCredentialValidateType = $firstCredentialValueType == 'email' ? 'email' : 'string';
        return [
            "password"   => "required",
            "username"   => "required|".$firstCredentialValidateType,
        ];
    }
}
