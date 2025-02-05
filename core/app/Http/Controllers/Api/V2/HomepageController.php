<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\FrontendResource;
use App\Http\Resources\LanguageResource;
use App\Http\Resources\PromotionResource;
use App\Models\Frontend;
use App\Models\Language;
use App\Models\PromoBanner;
use App\Models\News;
use Illuminate\Http\Request;

class HomepageController extends Controller
{
    //Promotion Api
    public function getPromotion()
    {
        $data = News::where("status", 1)->orderBy('created_at', 'desc')->paginate(20);
        $payload = [
            'code'         => 200,
            'data'         => PromotionResource::collection($data),
            'app_message'  => 'Successfully',
            'user_message' => 'Successfully'
        ];
        return response()->json($payload);
    }

    public function getPromotionDetails($id)
    {
        $data = News::findOrFail($id);
        $payload = [
            'code'         => 200,
            'data'         => new PromotionResource($data),
            'app_message'  => 'Successfully',
            'user_message' => 'Successfully'
        ];
        return response()->json($payload);
    }

    // Frontend Api
    public function getFrontendData($type)
    {
        $data = Frontend::where('data_keys', $type . '.element')->orderBy('id', 'desc')->get();
        $payload = [
            'code'         => 200,
            'data'         => FrontendResource::collection($data),
            'app_message'  => 'Successfully',
            'user_message' => 'Successfully'
        ];
        return response()->json($payload);
    }

    public function getNewsDetails($type, $id)
    {
        $data = Frontend::where('data_keys', $type . '.element')->findOrFail($id);
        $payload = [
            'code'         => 200,
            'data'         => new FrontendResource($data),
            'app_message'  => 'Successfully',
            'user_message' => 'Successfully'
        ];
        return response()->json($payload);
    }

    public function getContent($data)
    {
        $data = Frontend::where('data_keys', $data . '.content')->orderBy('id', 'desc')->get();
        $payload = [
            'code'         => 200,
            'data'         => FrontendResource::collection($data),
            'app_message'  => 'Successfully',
            'user_message' => 'Successfully'
        ];
        return response()->json($payload);
    }

    public function currencylist()
    {
        $data = file_get_contents(public_path('assets/symbol.json'));
        $data = json_decode($data, true);
        $payload = [
            'code'         => 200,
            'data'         => $data,
            'app_message'  => 'Successfully',
            'user_message' => 'Successfully'
        ];

        return response()->json($payload);
    }

    public function countrylist()
    {
        $data = json_decode(file_get_contents(resource_path('views/partials/country.json')), true);
        $data = collect($data)->map(function ($item, $key) {
            return [
                'name' => $item['country'],
                'dial_code' => $item['dial_code'],
                'code' => $key
            ];
        })->filter();

        $payload = [
            'code'         => 200,
            'data'         => $data->values(),
            'app_message'  => 'Successfully',
            'user_message' => 'Successfully'
        ];

        return response()->json($payload);
    }

    public function languagelist(){
        $data = Language::get();
        $payload = [
            'code'         => 200,
            'data'         => LanguageResource::collection($data),
            'app_message'  => 'Successfully',
            'user_message' => 'Successfully'
        ];

        return response()->json($payload);
    }

    public function policypage()
    {
        $data = Frontend::where('data_keys', 'policy_pages.element')->where('id', 3)->get();
        $payload = [
            'code'         => 200,
            'data'         => FrontendResource::collection($data),
            'app_message'  => 'Successfully',
            'user_message' => 'Successfully'
        ];
        return response()->json($payload);
    }

    public function termspage()
    {
        $data = Frontend::where('data_keys', 'policy_pages.element')->where('id', 4)->get();
        $payload = [
            'code'         => 200,
            'data'         => FrontendResource::collection($data),
            'app_message'  => 'Successfully',
            'user_message' => 'Successfully'
        ];
        return response()->json($payload);
    }

    public function refundpage()
    {
        $data = Frontend::where('data_keys', 'policy_pages.element')->where('id', 40)->get();
        $payload = [
            'code'         => 200,
            'data'         => FrontendResource::collection($data),
            'app_message'  => 'Successfully',
            'user_message' => 'Successfully'
        ];
        return response()->json($payload);
    }
    
     public function promoBanner()
    {
        $data = PromoBanner::get();
        $payload = [
            'code'         => 200,
            'data'         => $data,
            'app_message'  => 'Successfully',
            'user_message' => 'Successfully'
        ];
        return response()->json($payload);
    }
}
