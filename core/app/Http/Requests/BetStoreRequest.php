<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

class BetStoreRequest extends FormRequest
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
            'stake_amount' => 'required',
            'amount_type' => 'required',
            'bettype' => 'required',
            // 'bets' => 'required|array',
            // 'bets.*.category' => 'required',
            // 'bets.*.leauge' => 'required',
            // 'bets.*.oddId' => 'required',
            // 'bets.*.bookmarkId' => 'required',
            // 'bets.*.matchId' => 'required',
            // 'bets.*.odd_details' => 'required',
            // 'bets.*.odds' => 'required',
            // 'bets.*.odds_point' => 'required',
            // 'bets.*.stake_amount' => 'required',
            // 'bets.*.return_amount' => 'required',
            // 'bets.*.checker' => 'required',
            // 'bets.*.amount_type' => 'required',
            // 'bets.*.status' => 'required',
            // 'bets.*.api_source_type' => 'required',
            // 'bets.*.is_live' => 'required',
            // 'bets.*.team1' => 'required',
            // 'bets.*.team2' => 'required',
            // 'bets.*.market_name' => 'required',
        ];
    }
    public function failedValidation(ValidationValidator $validator) {
        throw new HttpResponseException(response()->json($validator->errors(), 200));
    }
}

