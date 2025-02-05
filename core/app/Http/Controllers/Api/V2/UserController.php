<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\NotificationUpdate;
use App\Models\UserNotification;
use Carbon\Carbon;
use App\Models\User;
use App\Constants\Status;
use App\Models\TramcardUser;
use Illuminate\Http\Request;
use App\Lib\GoogleAuthenticator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserCollection;
use App\Http\Requests\Api\KycFormSubmit;
use App\Http\Requests\Api\CreateTwoFactore;
use App\Http\Requests\Api\DisableTwoFactore;
use App\Http\Requests\Api\UserProfileUpdate;
use App\Http\Requests\Api\UserChangePassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserReferralsCollection;
use App\Models\ApplicationForm;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function getProfile()
    {
        $data = User::where('id', auth()->user()->id)->get();

        $payload = [
            'status'         => true,
            'data' => UserCollection::collection($data),
            'app_message'  => 'Successfully Retrive Data',
            'user_message' => 'Successfully Retrive Data'
        ];
        return response()->json($payload, 200);
    }
    public function getNotifications()
    {
        $data = [
            'userNotificationsData' =>  UserNotification::latest()->where('user_id', auth()->user()->id)->limit(5)->get(),
            'unreadNotificationCountData' => UserNotification::latest()->where('user_id', auth()->user()->id)->take(1)->where('is_read', 0)->count(),
        ];

        $payload = [
            'status'         => true,
            'data' => $data,
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }
    public function getAllNotifications(Request $request)
    {
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 10;
        $notificationsData = UserNotification::where('user_id', auth()->user()->id);
        if (is_numeric($page)) {
            $notificationsData = $notificationsData->paginate($perPage);
        } else {
            $notificationsData = $notificationsData->get();
        }

        return response()->json(
            responseBuilder(
                Response::HTTP_OK,
                "Success",
                [
                    'data'          => $notificationsData,
                    'per_page'      => $notificationsData->perPage() ?? 10,
                    'current_page'  => $notificationsData->currentPage() ?? 1,
                    'total'         => $notificationsData->total() ?? count($notificationsData),
                    'last_page'     => $notificationsData->lastPage() ?? count($notificationsData)
                ]
            ),
            Response::HTTP_OK
        );
    }
    public function profileUpdate(UserProfileUpdate $request)
    {

        $user = auth()->user();

        if ($request->hasFile('image')) {
            $makeUniqueName = 'profile_user_' . time() . '-' . uniqid();
            $name = $makeUniqueName . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move('assets/profile/user/', $name);
            $user->profile_photo = $name;
        }
        $user->firstname = $request->firstname;
        $user->lastname  = $request->lastname;
        $user->email  = $request->email;
        $user->mobile  = $request->mobile;
        $user->occupation  = $request->occupation;
        if ($request->filled('dob')) {
            $user->dob       = $request->dob;
        }
        $user->address = [
            'address' => $request->address,
            'state'   => $request->state,
            'zip'     => $request->zip,
            'city'    => $request->city,
        ];

        $update = $user->save();
        if ($update) {

            $payload = [
                'status'         => true,
                'app_message'  => 'Profile update successful',
                'user_message' => 'Profile update successful'
            ];
            return response()->json($payload, 200);
        } else {
            $payload = [
                'status'         => false,
                'app_message'  => 'Profile update unsuccessful',
                'user_message' => 'Profile update unsuccessful'
            ];
            return response()->json($payload, 200);
        }
    }
    public function updateNotifications(NotificationUpdate $request)
    {

        $notificationsData = UserNotification::where('user_id', auth()->user()->id)
            ->where('id',$request->notification_id)->first();
        if(!$notificationsData){
            $payload = [
                'status'         => false,
                'app_message'  => 'Data not found',
                'user_message' => 'Data not found'
            ];
            return response()->json($payload, 200);
        }else{
            $notificationsData->is_read = $request->is_read;
            $update = $notificationsData->save();

            if($update) {
                $payload = [
                    'status'         => true,
                    'app_message'  => 'Update successful',
                    'user_message' => 'Update successful'
                ];
                return response()->json($payload, 200);
            } else {
                $payload = [
                    'status'         => false,
                    'app_message'  => 'Update unsuccessful',
                    'user_message' => 'Update unsuccessful'
                ];
                return response()->json($payload, 200);
            }
        }

    }
    public function changePassword(UserChangePassword $request)
    {
        $user = User::where('id', $request->user()->id)->first();

        if (Hash::check($request->current_password, $user->password)) {

            $user->password = Hash::make($request->password);
            $user->save();
            $payload = [
                'code'         => 200,
                'app_message'  => 'successful',
                'user_message' => 'Password information updated successfully.',
            ];
            return response()->json($payload, 200);
        } else {
            $payload = [
                'code'         => 500,
                'app_message'  => 'The password doesn\'t match!',
                'user_message' => 'The password doesn\'t match!.',
            ];
            return response()->json($payload, 401);
        }
    }
    public function getTwoFactorData()
    {
        $ga        = new GoogleAuthenticator();
        $user      = auth()->user();
        $secret    = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . gs('site_name'), $secret);
        $payload = [
            'status'         => true,
            'data' => [
                'secret' => $secret,
                'qrCodeUrl' => $qrCodeUrl,
            ],
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }
    public function createTwoFactor(CreateTwoFactore $request)
    {

        $user = auth()->user();
        $response = verifyG2fa($user, $request->code, $request->key);
        if ($response) {
            $user->tsc = $request->key;
            $user->ts  = 1;
            $user->save();
            $payload = [
                'status'         => true,
                'app_message'  => 'Google authenticator activated successfully',
                'user_message' => 'Google authenticator activated successfully'
            ];
            return response()->json($payload, 200);
        } else {
            $payload = [
                'status'         => false,
                'app_message'  => 'Wrong verification code',
                'user_message' => 'Wrong verification code'
            ];
            return response()->json($payload, 200);
        }
    }
    public function disableTwoFactor(DisableTwoFactore $request)
    {

        $user = auth()->user();
        $response = verifyG2fa($user, $request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts  = 0;
            $user->save();
            $payload = [
                'status'         => true,
                'app_message'  => 'Two factor authenticator deactivated successfully',
                'user_message' => 'Two factor authenticator deactivated successfully'
            ];
            return response()->json($payload, 200);
        } else {
            $payload = [
                'status'         => false,
                'app_message'  => 'Wrong verification code',
                'user_message' => 'Wrong verification code'
            ];
            return response()->json($payload, 200);
        }
    }
    public function getReferralsData()
    {
        $getUserReferrals = User::with('refBy', 'referrals')->where('id', auth()->user()->id)->first();
        $returnData = [
            'referredBy' => $getUserReferrals->refBy ? UserReferralsCollection::collection($getUserReferrals->refBy) : [],
            'referrals' => $getUserReferrals->referrals ? UserReferralsCollection::collection($getUserReferrals->referrals) : []
        ];
        $payload = [
            'status'        => true,
            'data'          => $returnData,
            'app_message'   => 'Successfully Retrive Data',
            'user_message'  => 'Successfully Retrive Data'
        ];
        return response()->json($payload, 200);
    }

    public function changeForgotPassword(Request $request){
        $user = User::findOrFail($request->token);
//        Log::info($user);
//        return $user;
        $request->validate([
            'password' => 'required|min:8', // Add confirmation validation
        ]);
        $user->password = Hash::make($request->password);
        $user->save();
        $notify[] = ['success', 'Password successfully changed'];
        $payload = [
            'status' => true,
            'app_message' => 'Password successfully changed',
            'user_message' => 'Password successfully changed',
            'user' => $user,
        ];
        return response()->json($payload, 200);
    }
    public function kycSubmit(KycFormSubmit $request)
    {
        $requestForm = [];
        $requestForm[] = [
            "name" => "Full Name",
            "type" => "text",
            "value" => $request->full_name
        ];
        $requestForm[] = [
            "name" => "Document Type",
            "type" => "checkbox",
            "value" => $request->document_type
        ];
        $requestForm[] = [
            "name" => "Document number",
            "type" => "text",
            "value" => $request->document_number
        ];
        if ($request->hasFile('front_page_of_document')) {
            $directory = date("Y") . "/" . date("m") . "/" . date("d");
            $path = getFilePath('verify') . '/' . $directory;
            $value = $directory . '/' . fileUploader($request->front_page_of_document, $path);
            $requestForm[] = [
                "name" => "Front Page of Document",
                "type" => "file",
                "value" => $value
            ];
        } else {
            $requestForm[] = [
                "name" => "Front Page of Document",
                "type" => "file",
                "value" => null
            ];
        }
        if ($request->hasFile('back_page_of_documents')) {
            $directory = date("Y") . "/" . date("m") . "/" . date("d");
            $path = getFilePath('verify') . '/' . $directory;
            $value = $directory . '/' . fileUploader($request->back_page_of_documents, $path);
            $requestForm[] = [
                "name" => "Back Page of Documents",
                "type" => "file",
                "value" => $value
            ];
        } else {
            $requestForm[] = [
                "name" => "Back Page of Documents",
                "type" => "file",
                "value" => null
            ];
        }
        try {

            $user           = auth()->user();
            $user->kyc_data = $requestForm;
            $user->kv       = 2;
            $user->save();
            $payload = [
                'status'         => true,
                'app_message'  => 'Kyc form submitted successfully',
                'user_message' => 'Kyc form submitted successfully'
            ];
            return response()->json($payload, 200);
        } catch (\Exception $e) {
            $payload = [
                'status'         => false,
                'app_message'  => 'Kyc form submission unsuccessful',
                'user_message' => 'Kyc form submission unsuccessful'
            ];
            return response()->json($payload, 200);
        }
    }

    public function getTrampCard(){
        $tramcard = TramcardUser::with('tramcard')->where('user_id', auth()->user()->id)->first();
        $rules = ['rule_1', 'rule_2', 'rule_3', 'rule_4'];
        $progressBarValue = array_reduce($rules, function ($carry, $rule) use ($tramcard) {
            return $carry + (isset($tramcard->$rule) ? 1 : 0);
        }, 0);

        if($tramcard !== null){

        }
        $response = [
            'rules' => $tramcard ? $tramcard->tramcard->rules : null,
            'balance' => $tramcard ? $tramcard->amount.' '.$tramcard->currency : null,
            'duration_text' => $tramcard ? $tramcard->duration_text : null,
            'valid' => $tramcard ? $tramcard->valid_time : null,
            'rule_1' => $tramcard ? $tramcard->rule_1 : null,
            'rule_2' => $tramcard ? $tramcard->rule_2 : null,
            'rule_3' => $tramcard ? $tramcard->rule_3 : null,
            'rule_4' => $tramcard ? $tramcard->rule_4 : null,
            'is_win' => $tramcard ? $tramcard->is_win : null,
            'amount' => $tramcard ? $tramcard->amount : null,
            'currency' => $tramcard ? $tramcard->currency : null,
            'minimum_bet' => $tramcard ? $tramcard->tramcard->minimum_bet : null,
            'odds' => $tramcard ? $tramcard->tramcard->odds : null,
            'image' => $tramcard ? url('/')."/core/public".Storage::url('event/tramcard/' . $tramcard->tramcard->image) : null,
            'progress_bar_value' => $progressBarValue,
        ];
        $payload = [
            'status'         => true,
            'data' => $response,
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];

        return response()->json($payload, 200);
    }
    public function tramCardClaim(){
        try{
            DB::beginTransaction();
            $tramcard = TramcardUser::where('user_id', auth()->user()->id)->where('is_win', 1)->first();
            if($tramcard){
                $user = User::where('id', auth()->user()->id)->first();
                $user->withdrawal += $tramcard->amount;
                $user->save();

                $tramcard->amount = 0;
                $tramcard->is_win = 0;
                $tramcard->save();
                DB::commit();
                $payload = [
                    'status'         => true,
                    'app_message'  => 'Congratulations. You tramcard amount goes to withdrawal fund.',
                    'user_message' => 'Congratulations. You tramcard amount goes to withdrawal fund.'
                ];
                return response()->json($payload, 200);
            }else{
                $payload = [
                    'status'         => false,
                    'app_message'  => 'There are no active tramcard or You can not pass the all rules.',
                    'user_message' => 'There are no active tramcard or You can not pass the all rules.'
                ];
                return response()->json($payload, 200);
            }
        } catch(\Exception $e){
            DB::rollback();
            $payload = [
                'status'         => false,
                'app_message'  => 'Something went wrong.',
                'user_message' => 'Something went wrong.'
            ];
            return response()->json($payload, 200);
        }
    }
    public function affiliateApplicationFormSubmit(Request $request)
    {
        $request->validate([
            'description' => 'required',
        ]);

        DB::table('applicationforms')->insert([
            'user_id' => auth()->id(),
            'description' => $request->description,
            'website' => $request->website,
            'status' => 0,
            'is_approved' => 0,
            'created_at' => Carbon::now(),
        ]);

        $payload = [
            'status'            => true,
            'app_message'       => 'Successfully Retrieve Data',
            'user_message'      => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }

    public function affiliateApplyList(Request $request)
    {
        
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 10;
        $data = ApplicationForm::where('user_id', auth()->id())->orderBy('id', 'desc');
        if (is_numeric($page)) {
            $data = $data->paginate($perPage);
        } else {
            $data = $data->get();
        }

        return response()->json(
            responseBuilder(
                Response::HTTP_OK,
                "Success",
                [
                    'data'          => $data,
                    'per_page'      => $data->perPage() ?? 10,
                    'current_page'  => $data->currentPage() ?? 1,
                    'total'         => $data->total() ?? count($data),
                    'last_page'     => $data->lastPage() ?? count($data)
                ]
            ),
            Response::HTTP_OK
        );
       
    }

    public function setProfileMode(Request $request)
    {
        $user = User::find($request->id);
        $user->profile_mode = $request->mode;
        $user->update();

        $data = User::find(auth()->user()->id);
        if ($data->profile_mode == 'better') {
             $payload = [
                'status'            => true,
                'app_message'       => 'Bettor',
                'mode'      => '/',
            ];
            return response()->json($payload, 200);
        } elseif ($data->profile_mode == 'affiliate' && $data->is_affiliate == 1) {
            $payload = [
                'status'            => true,
                'app_message'       => 'Affiliate',
                'mode'      => '/affiliate',
            ];
            return response()->json($payload, 200);
        }
    }

    public function emailVerification(Request $request)
    {
        $request->validate([
            'code' => 'required'
        ]);

        $user = User::where('id',$request->user_id?? auth()->id())->first();
        if($user->is_one_click_user==1){
            if ($user->ver_code == $request->code) {
                $user->oev = Status::VERIFIED;
                $user->ver_code = null;
                $user->ver_code_send_at = null;
                $user->save();
                $payload = [
                    'status'         => true,
                    'data'          => $user,
                    'app_message'  => 'Email verification successful',
                    'user_message' => 'Email verification successful',
                    'route'=>'/user/profile'
                ];

                return response()->json($payload, 200);
            }
            throw ValidationException::withMessages(['code' => 'Verification code didn\'t match!']);
        }
        else{
            if ($user->ver_code == $request->code) {
                $user->ev = Status::VERIFIED;
                $user->ver_code = null;
                $user->ver_code_send_at = null;
                $user->save();
                $payload = [
                    'status'         => true,
                    'data'          => $user,
                    'app_message'  => 'Email verification successful',
                    'user_message' => 'Email verification successful'
                ];


                return response()->json($payload, 200);
            }
            throw ValidationException::withMessages(['code' => 'Verification code didn\'t match!']);
        }

    }
}
