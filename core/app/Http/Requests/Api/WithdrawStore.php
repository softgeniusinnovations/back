<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
class WithdrawStore extends FormRequest
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
            'payment_gateway'   => ['required', 'in:local,cash'],
            'provider' => 'required',
            'agent'   => 'required|numeric',
            'amount'   => 'required|numeric',
            'payment_number' => 'required_if:payment_gateway,==,local',
            'method_code' => 'required_without:payment_gateway',
        ];
    }
}
