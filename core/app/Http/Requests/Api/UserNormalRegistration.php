<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Models\GeneralSetting;
class UserNormalRegistration extends FormRequest
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
        $general            = GeneralSetting::first();
        $passwordValidation = Password::min(6);
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }
        $agree = 'nullable';
        $welcomeCheck              = 'nullable';
        $firstBonusCheck              = 'nullable';
        $dob='nullable';
        if ($general->agree) {
            $agree = 'required';
        }
        $countryData  = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));


        return [
            'email'        => 'required|string|email|unique:users',
            'mobile_code'  => 'required|in:' . $mobileCodes,
            'country_code' => 'required|in:' . $countryCodes,
            'country'      => 'required|in:' . $countries,
            'mobile'       => 'required|regex:/^([0-9]*)$/',
            'currencies'       => 'required',
            'password'     => ['required', 'confirmed', $passwordValidation],
            'agree'        => $agree,
            'welcomeCheck'         => $welcomeCheck,
            'firstBonusCheck'         => $firstBonusCheck,
            'dob'=>$dob,
        ];
    }
}
