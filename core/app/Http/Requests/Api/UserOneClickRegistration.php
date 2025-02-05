<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UserOneClickRegistration extends FormRequest
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
        $general            = gs();
        $agree              = 'nullable';
        $welcomeCheck              = 'nullable';
        $firstBonusCheck              = 'nullable';
        if ($general->agree) {
            $agree = 'required';
        }
        $countryData        = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes       = implode(',', array_keys($countryData));
        $countries          = implode(',', array_column($countryData, 'country'));

        return [
            'email'         => 'nullable|email|unique:users',
            'captcha'       => 'sometimes|required',
            'country_code'  => 'required|in:' . $countryCodes,
            'country'       => 'required|in:' . $countries,
            'currencies'    => 'required',
            'agree'         => $agree,
            'welcomeCheck'         => $welcomeCheck,
            'firstBonusCheck'         => $firstBonusCheck,
            'promo'         => 'nullable|exists:promotions,promo_code,status,1,is_admin_approved,1',
        ];
    }
}
