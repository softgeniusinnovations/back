<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\KycFormSubmit;
use App\Http\Resources\KycFormResource;
use App\Http\Resources\NewsCollection;
use App\Http\Resources\NewsDetailsCollection;
use App\Http\Resources\PolicyCollection;
use App\Http\Resources\RefundPolicyCollection;
use App\Http\Resources\TermsOfServiceCollection;
use App\Models\Form;
use App\Models\Frontend;

class CommonController extends Controller
{
    public function getPrivacyPolicy()
    {
        $policy    = Frontend::where('id', 3)->where('data_keys', 'policy_pages.element')->first();
        $payload = [
            'status'         => true,
            'data' => new PolicyCollection($policy),
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }
    public function getTermsOfService()
    {
        $policy    = Frontend::where('id', 4)->where('data_keys', 'policy_pages.element')->first();
        $payload = [
            'status'         => true,
            'data' => new TermsOfServiceCollection($policy),
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }
    public function getRefundPolicy()
    {
        $policy    = Frontend::where('id', 40)->where('data_keys', 'policy_pages.element')->first();
        $payload = [
            'status'         => true,
            'data' => new RefundPolicyCollection($policy),
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }
    public function getAllNews($pageNo)
    {
        $perPage = 2;
        $skip = $pageNo == 1 ? 0 : $perPage * $pageNo;
        $news    = Frontend::where('data_keys', 'blog.element')->where('id', '!=', '36')->orderBy('id', 'desc')
            ->skip($skip)->take($perPage)->get();
        $paginationData = [
            'currentPage'         => $pageNo,
            'nextPage'         => $pageNo + 1,
            'totalPages'         => round($news->count() / $perPage),
            'totalItems'         => $news->count(),
            'itemsPerPage'         => $perPage,
        ];
        $payload = [
            'status'         => true,
            'data' => NewsCollection::collection($news),
            'paginationData' =>  $paginationData,
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }
    public function getAllNewsDetails($id)
    {
        $news    = Frontend::where('id', $id)->where('data_keys', 'blog.element')->first();
        $recentNews    = Frontend::whereNotIn('id', [$id, '36'])->where('data_keys', 'blog.element')
            ->orderBy('id', 'desc')->get()->take(5);
        $payload = [
            'status'         => true,
            'data' => new NewsDetailsCollection($news),
            'recentNews' => NewsCollection::collection($recentNews),
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }

    public function kycForm()
    {

        $form      = Form::where('act', 'kyc')->first();
        $payload = [
            'status'         => true,
            'data' => [
                'id' => $form->id,
                'act' => $form->act,
                'form_data' => $form->form_data
            ],
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];

        return response()->json($payload, 200);
    }
}
