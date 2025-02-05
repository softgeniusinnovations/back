<?php

namespace App\Http\Controllers\Api\V2;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ResetPasswordReset;
use App\Http\Requests\Api\SendPasswordResetCode;
use App\Http\Requests\Api\UserLogin;
use App\Http\Requests\Api\UserNormalRegistration;
use App\Http\Requests\Api\UserOneClickRegistration;
use App\Http\Requests\Api\VerifyPasswordResetCode;
use App\Http\Resources\UserCollection;
use App\Models\PasswordReset;
use App\Models\User;
use App\Models\Bet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\AffiliatePromos;
use App\Models\AffiliateWebsite;
use App\Models\Promotion;
use App\Models\UserBonusList;
use App\Models\UserNotification;
use App\Models\Website;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Notifications\TramcardSendNotification;
use App\Models\GeneralSetting;
use App\Models\UserLogin as ModelsUserLogin;
use Illuminate\Validation\ValidationException;
use App\Notify\Email;

class AuthController extends Controller
{

    public function signup(UserNormalRegistration $request)
    {

        if (preg_match("/[^a-z0-9_]/", trim($request->username))) {
            return response()->json([
                'message'  => 'No special character, space or capital letters in username',
                'errors' => [
                    'username' => 'No special character, space or capital letters in username'
                ],
            ], 422);
        }
        if (!verifyCaptcha()) {
            return response()->json([
                'message'  => 'Invalid captcha provided',
                'errors' => [
                    'captcha' => 'Invalid captcha provided'
                ],
            ], 422);
        }
        $exist = User::where('mobile', $request->mobile_code . $request->mobile)->first();
        if ($exist) {
            return response()->json([
                'message'  => 'The mobile number already exists',
                'errors' => [
                    'username' => 'The mobile number already exists'
                ],
            ], 422);
        }
        $general = gs();

        $referBy = $request->reference;
        $referUser  = $referBy ? User::where('referral_code', $referBy)->first() : null;
        $userId = User::latest()->first() ? User::latest()->first()->user_id + 1 : 100001;

        $data = [
            'user_id' => $userId,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'username' => $userId,
            'ref_by' => $referUser ? $referUser->id : 0,
            'referral_code' => getTrx(),
            'country_code' => $request->country_code,
            'mobile' => $request->mobile_code . $request->mobile,
            'currency' => $request->currencies,
            'dob'=>$request->dob,
            'address' => [
                'address' => '',
                'state'   => '',
                'zip'     => '',
                'country' => $request->country,
                'city'    => '',
            ],
            'kv' => $general->kv ? Status::NO : Status::YES,
            'is_affiliate' => $request->has('is_affiliate') ? 1 : 0,
            'is_better' => $request->has('is_affiliate') ? 0 : 1,
            'profile_mode'=>$request->has('is_affiliate') ? 'affiliate' : 'better',
            'ev' => $general->ev ? Status::NO : Status::YES,
            'sv' => $general->sv ? Status::NO : Status::YES,
            'ts' => 0,
            'tv' => 1,
            'ver_code' => verificationCode(6),
            'ver_code_send_at' => Carbon::now(),
            'first_bonus_check'=>$request->firstBonusCheck==true?1:0,
            'is_one_click_user'=>0
        ];
        $user = User::create($data);

        if ($user) {
            $user = User::find($user->id);
            if ($request->filled('websiteLink')) {
                $this->websiteAddinRegistration($request->websiteLink, $user->id);
            }
            if($user->is_affiliate==1){
                $this->autoGeneratePromo($user->id);
            }

            if ($request->filled('promo')) {
                $this->promotion($request->promo, $user->id);
            }
            $this->sendVerifyCode('email', $user);
            $this->createAdminNotification($user->id);
//            $this->createUserLogin($user->id);
            if($user->is_affiliate!=1&&$request->welcomeCheck==true){
                $this->welcomeBonus($user);
            }

            $token =  $user->createToken('TramBet')->accessToken;
            $payload = [
                'status'         => true,
                'app_message'  => 'Login successful, credentials matched.',
                'user_message' => 'Login successful.',
                'access_token' => $token,
                'data'      => new UserCollection($user)
            ];
            return response()->json($payload, 200);
        } else {
            $payload = [
                'status'         => false,
                'app_message'  => 'Registration Unsuccessful',
                'user_message' => 'Registration Unsuccessful',
            ];
            return response()->json($payload, 500);
        }
    }
    protected function checkCodeValidity($user,$addMin = 2)
    {
        if (!$user->ver_code_send_at){
            return false;
        }
        if ($user->ver_code_send_at->addMinutes($addMin) < Carbon::now()) {
            return false;
        }
        return true;
    }
    public function reSendVerifyCode($type)
    {
        $user = auth()->user();
        if ($this->checkCodeValidity($user)) {
            $targetTime = $user->ver_code_send_at->addMinutes(2)->timestamp;
            $delay = $targetTime - time();
            $payload = [
                'status'         => true,
                'app_message'  => 'Please try after ' . $delay . ' seconds',
                'user_message' => 'Please try after ' . $delay . ' seconds',
            ];
            return response()->json($payload, 200);
        }

        $user->ver_code = verificationCode(6);
        $user->ver_code_send_at = Carbon::now();
        $this->sendVerifyCode('email', $user);
        $user->save();

        if ($type == 'email') {
            $type = 'email';
            $notifyTemplate = 'EVER_CODE';
        } else {
            $type = 'sms';
            $notifyTemplate = 'SVER_CODE';
        }

        notify($user, $notifyTemplate, [
            'code' => $user->ver_code
        ],[$type]);
        $payload = [
            'status'         => true,
            'app_message'  => 'Verification code sent successfully',
            'user_message' => 'Verification code sent successfully'
        ];
        return response()->json($payload, 200);
    }
    private function findFieldType() {
        $input = request()->input('value');

        $fieldType = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$fieldType => $input]);
        return $fieldType;
    }
    public function sendResetCode(SendPasswordResetCode $request)
    {

        if (!verifyCaptcha()) {
            return response()->json([
                'message'  => 'Invalid captcha provided',
                'errors' => [
                    'captcha' => 'Invalid captcha provided'
                ],
            ], 422);
        }
        $fieldType = $this->findFieldType();
        $user      = User::where($fieldType, $request->value)->first();

        if (!$user) {
            $payload = [
                'status'         => false,
                'app_message'  => 'Couldn\'t find any account with this information',
                'user_message' => 'Couldn\'t find any account with this information',
            ];
            return response()->json($payload, 500);
        }
        PasswordReset::where('email', $user->email)->delete();
        $code                 = verificationCode(6);
        $password             = new PasswordReset();
        $password->email      = $user->email;
        $password->token      = $code;
        $password->created_at = \Carbon\Carbon::now();
        $password->save();

        $userIpInfo      = getIpInfo();
        $userBrowserInfo = osBrowser();
        notify($user, 'PASS_RESET_CODE', [
            'code'         => $code,
            'operating_system' => @$userBrowserInfo['os_platform'],
            'browser'      => @$userBrowserInfo['browser'],
            'ip'           => @$userIpInfo['ip'],
            'time'         => @$userIpInfo['time'],
        ], ['email']);

        $payload = [
            'status'         => true,
            'app_message'  => 'Password reset email sent successfully.',
            'user_message' => 'Password reset email sent successfully.'
        ];
        return response()->json($payload, 200);
    }
    public function verifyResetCode(VerifyPasswordResetCode $request)
    {
        $code = str_replace(' ', '', $request->code);

        if (PasswordReset::where('token', $code)->where('email', $request->email)->count() != 1) {
            $payload = [
                'status'         => false,
                'app_message'  => 'Verification code doesn\'t match',
                'user_message' => 'Verification code doesn\'t match',
            ];
            return response()->json($payload, 500);
        }
        $payload = [
            'status'         => true,
            'app_message'  => 'Code verified successfully.',
            'user_message' => 'Code verified successfully.'
        ];
        return response()->json($payload, 200);
    }
    public function resetPassword(ResetPasswordReset $request)
    {
        $reset = PasswordReset::where('token', $request->token)->orderBy('created_at', 'desc')->first();
        if (!$reset) {
            $payload = [
                'status'         => false,
                'app_message'  => 'Invalid verification code',
                'user_message' => 'Invalid verification code',
            ];
            return response()->json($payload, 500);
        }

        $user           = User::where('email', $reset->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        $userIpInfo  = getIpInfo();
        $userBrowser = osBrowser();
        notify($user, 'PASS_RESET_DONE', [
            'operating_system' => @$userBrowser['os_platform'],
            'browser'      => @$userBrowser['browser'],
            'ip'           => @$userIpInfo['ip'],
            'time'         => @$userIpInfo['time'],
        ], ['email']);


        $payload = [
            'status'         => true,
            'app_message'  => 'Password changed successfully.',
            'user_message' => 'Password changed successfully.'
        ];
        return response()->json($payload, 200);
    }
    private function sendVerifyCode($type, $user)
    {
        if ($type == 'email') {
            $type = 'email';
            $notifyTemplate = 'EVER_CODE';
        } else {
            $type = 'sms';
            $notifyTemplate = 'SVER_CODE';
        }

        notify($user, $notifyTemplate, [
            'code' => $user->ver_code
        ],[$type]);

        return true;
    }
    public function otpForForgotPassword(Request $request){
        $user = User::where('email', $request->username)
            ->orWhere('user_id', $request->username)
            ->first();
        if($user){
            $user->ver_code =verificationCode(6);
            $user->ver_code_send_at = Carbon::now();
            $user->save();
            $this->sendVerifyCode('email', $user);
            $payload = [
                'status' => true,
                'app_message' => 'Verification code sent successfully',
                'user_message' => 'Verification code sent successfully',
                'user' => $user,
            ];

        }
        else{
            $payload = [
                'status' => false,
                'app_message' => 'User not found',
                'user_message' => 'user not found',

            ];
        }
        return response()->json($payload, 200);
    }
    public function sendOtpEmailOnetime()
    {

        $user = User::find(auth()->id());

        if ($user) {

            $user->ver_code =verificationCode(6);
            $user->ver_code_send_at = Carbon::now();
            $user->save();


            $this->sendVerifyCode('email', $user);

            $payload = [
                'status' => true,
                'app_message' => 'Verification code sent successfully',
                'user_message' => 'Verification code sent successfully',
                'user' => $user,
            ];
        } else {
            $payload = [
                'status' => false,
                'app_message' => 'User not found',
                'user_message' => 'User not found',
            ];
        }

        return response()->json($payload, 200);
    }


    public function OneClickSignup(UserOneClickRegistration $request)
    {
        $general    = gs();
        $referBy    = $request->reference;
        $referUser  = $referBy ? User::where('referral_code', $referBy)->first() : null;
        $password = $this->generateRandomString('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&_=', '8');
        $userId = User::latest()->first() ? User::latest()->first()->user_id + 1 : 100001;
        $is_one_click_user = 0;
        if(isset($request->type)){
            if($request->type === "ONE"){
                $is_one_click_user = 1;
            }
        }

        $data = [
            'user_id' => $userId,
            'password' => Hash::make($password),
            'username' => $userId,
            'one_time_pass' => $password,
            'ref_by' => $referUser ? $referUser->id : 0,
            'referral_code' => getTrx(),
            'country_code' => $request->country_code,
            'currency' => $request->currencies,
            'address' => [
                'address' => '',
                'state'   => '',
                'zip'     => '',
                'country' => $request->country,
                'city'    => '',
            ],
            'kv' => $general->kv ? Status::NO : Status::YES,
            'ev' => 1,
            'sv' => $general->sv ? Status::NO : Status::YES,
            'profile_complete' => 1,
            'ts' => 0,
            'tv' => 1,
            'is_one_click_user' => $is_one_click_user,
            'first_bonus_check'=>$request->firstBonusCheck==true?1:0
        ];

        $user = User::create($data);
        if ($user) {
            $user = User::find($user->id);
            if($request->welcomeCheck==true){
                $this->welcomeBonus($user);
            }


            if ($request->filled('promo')) {
                $this->promotion($request->promo, $user->id);
            }
            $this->createAdminNotification($user->id);

            $this->createUserLogin($user->id);


            $token =  $user->createToken('TramBet')->accessToken;


            $payload = [
                'status'         => true,
                'app_message'  => 'Login successful, credentials matched.',
                'user_message' => 'Login successful.',
                'access_token' => $token,
                'data'      => new UserCollection($user)
            ];
            return response()->json($payload, 200);
        } else {
            $payload = [
                'status'         => false,
                'app_message'  => 'Registration Unsuccessful',
                'user_message' => 'Registration Unsuccessful',
            ];
            return response()->json($payload, 500);
        }


        return $user;
    }
    public function login(UserLogin $request)
    {
        $firstCredentialValue = $request->username;
        $firstCredentialValueType = filter_var($firstCredentialValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $firstCredentialValidateType = $firstCredentialValueType == 'email' ? 'email' : 'string';

        $user = User::with('userNotifications')->where($firstCredentialValueType, '=', trim($request->username))->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $user = User::find($user->id);
                $token =  $user->createToken('TramBet')->accessToken;
                $payload = [
                    'status'         => true,
                    'app_message'  => 'Login successful, credentials matched.',
                    'user_message' => 'Login successful.',
                    'access_token' => $token,
                    'data'      => new UserCollection($user)
                ];


                return response()->json($payload, 200);
            } else {
                $payload = [
                    'code'         => 401,
                    'app_message'  => 'Credentials didn\'t validate.',
                    'user_message' => 'login unsuccessful, password mismatch',
                ];
                return response()->json($payload, 401);
            }
        } else {
            $payload = [
                'code'         => 401,
                'app_message'  => 'Credentials didn\'t validate.',
                'user_message' => 'login unsuccessful, password mismatch',
            ];
            return response()->json($payload, 401);
        }
    }
    public function logout(Request $request)
    {
        if (Auth::user()) {
            $token = $request->user()->token();
            $token->revoke();
            $payload = [
                'code'         => 200,
                'app_message'  => 'You have been successfully logged out!',
                'user_message' => 'You have been successfully logged out!'
            ];
            return response()->json($payload, 200);
        } else {
            $payload = [
                'code'         => 401,
                'app_message'  => 'not found',
                'user_message' => 'Invalid user.'
            ];
            return response()->json($payload, 401);
        }
    }
    private function websiteAddinRegistration($website_link,$user_id)
    {
        try {
            DB::beginTransaction();

            $website = new AffiliateWebsite();
            $website->affiliate_id = $user_id;
            $website->website = $website_link;
            $website->webtype = strpos($website_link, 'youtube') !== false ? 'youtube' : 'website';
            $website->status = 1;
            $website->websiteId = AffiliateWebsite::latest()->first() ? AffiliateWebsite::latest()->first()->websiteId + 1 : 100001;
            $website->save();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return false;
        }
    }
    public function autoGeneratePromo($user_id){
        try {
            DB::beginTransaction();

            $promo                      = new Promotion();
            $promo->title               = 'AutoGenerated Promotion';
            $promo->promo_code          = substr(strtoupper(bin2hex(random_bytes(3))), 0, 6);
            $promo->status              = 1;
            $promo->details             = 'AutoGenerated Promotion details';

            $promo->slug                = slug('AutoGenerated Promotion');
            $promo->is_admin_approved   = 0;

            $promo->user_id = $user_id;
            $promo->save();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return false;
        }

    }
    private function promotion($promoCode, $userId)
    {
        $aff_website = null;
        $promo = Promotion::where('promo_code', $promoCode)
            ->where('status', 1)
            ->where('is_admin_approved', 1)
            ->first();

        // $website1 = Website::where('webId', session()->get('linkId'))->first();

        // add new code 
        if(session()->get('linkId')){
            $website1 = Website::where('webId', session()->get('linkId'))->first();
            if($website1){
                $aff_website = $website1->aff_website;
            }
        }
        $website = AffiliateWebsite::where('affiliate_id', $promo->user_id??'')->first();
        if ($promo) {
            $affiliatePromo = new AffiliatePromos();
            $affiliatePromo->affliate_user_id = $promo->user_id??'';
            $affiliatePromo->better_user_id = $userId;
            $affiliatePromo->promo_id = $promo->id??'';
            $affiliatePromo->website = $aff_website;
            $affiliatePromo->websiteId = $website->websiteId??'';
            $affiliatePromo->save();
        }
    }

    public function rejectWelcomeBonus(){

        $user = auth()->user();

        if ($user) {
//            return $user;
            try {
                $userBonus = UserBonusList::where('user_id', $user->id)->first();
                DB::beginTransaction();
                $user->bonus_account = max(0, $user->bonus_account - $userBonus->initial_amount);
                $user->save();

                if ($userBonus) {
                    $userBonus->delete();
                }
                DB::commit();
                return response()->json([
                    'message' => 'welcome Bonus updated successfully',
                    'success'=>true,
                    'status'=>200
                    ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Failed to update bonus',
                    'message' => $e->getMessage(),
                    'success'=>false,
                    'status'=>500
                ]);
            }
        } else {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
    }
    public function rejectBonusForWithdraw(){
        $user = auth()->user();
        if ($user) {
            $check_bet_using_bonus = Bet::where('user_id', $user->id)
                ->where('amount_type', 2)
                ->where('status', 2)
                ->exists();
            if($check_bet_using_bonus==true){
                return response()->json([
                    'message' => 'You cant delete bonus',
                    'check_bet_using_bonus'=>$check_bet_using_bonus,
                    'success'=>true,
                    'status'=>200
                ],200);
            }
            try {

                DB::beginTransaction();
                $user->bonus_account = 0;
                $user->save();
                $userBonus = UserBonusList::where('user_id', $user->id)->first();
                if ($userBonus) {
                    $userBonus->initial_amount = 0;
                    $userBonus->save();
                }
                DB::commit();
                return response()->json([
                    'message' => 'Bonus amount deleted successfully',
                    'success'=>true,
                    'status'=>200
                    ],200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Failed to update bonus',
                    'message' => $e->getMessage(),
                    'success'=>false,
                    'status'=>500
                ],500);
            }
        } else {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
    }



    public function checkActiveBonus(){
        $user = auth()->user();
        if ($user) {
            try {
               $bonus=$user->bonus_account;
                $check_bet_using_bonus = Bet::where('user_id', $user->id)
                    ->where('amount_type', 2)
                    ->where('status', 2)
                    ->exists();

                return response()->json([
                    'message' => 'user found successfully',
                    'user'=>$user,
                    'check_bet_using_bonus'=>$check_bet_using_bonus,
                    'bonus'=>(float) $bonus,
                    'success'=>true,
                    'status'=>200
                ],200);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Failed get bonus',
                    'message' => $e->getMessage(),
                    'success'=>false,
                    'status'=>500
                ]);
            }
        } else {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
    }
    private function welcomeBonus($user)
    {
        try {
            DB::beginTransaction();

            $totalSeconds = 24 * 60 * 60;
            $futureDateTime = Carbon::now()->addSeconds($totalSeconds);

            $bonus = new UserBonusList();
            $bonus->user_id = $user->id;
            $bonus->type = 'welcome';
            $bonus->initial_amount = 300;
            $bonus->wager = 5;
            $bonus->rollover_limit = 3;
            $bonus->min_bet_multi = 3;
            $bonus->minimum_odd = 1.6;
            $bonus->currency = $user->currency;
            $bonus->valid_time = $futureDateTime;
            $bonus->duration = 1;
            $bonus->duration_text = '24 hours';
            $bonus->save();

            $user->increment('bonus_account', 300);


            // Notify to user
            $userNotify = new UserNotification();
            $userNotify->user_id = $user->id;
            $userNotify->title = "Congratulations! You have to got 300 " . $user->currency . " welcome bonus for 24 hours";
            $userNotify->url = "/user/bonus";
            $userNotify->save();

            $userNotify->notify(new TramcardSendNotification($userNotify));


            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }
    private function createAdminNotification($userId)
    {
        try {
            AdminNotification::create([
                'user_id' => $userId,
                'title' => 'New member registered',
                'click_url' => urlPath('admin.users.detail', $userId),
            ]);
        } catch (\Exception $e) {
        }
    }
    private function createUserLogin($userId)
    {
        $ip        = getRealIP();
        $exist     = ModelsUserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();
        try {
            //Check exist or not
            if ($exist) {
                $userLogin->longitude    = $exist->longitude;
                $userLogin->latitude     = $exist->latitude;
                $userLogin->city         = $exist->city;
                $userLogin->country_code = $exist->country_code;
                $userLogin->country      = $exist->country;
            } else {
                $info                    = json_decode(json_encode(getIpInfo()), true);
                $userLogin->longitude    = @implode(',', $info['long']);
                $userLogin->latitude     = @implode(',', $info['lat']);
                $userLogin->city         = @implode(',', $info['city']);
                $userLogin->country_code = @implode(',', $info['code']);
                $userLogin->country      = @implode(',', $info['country']);
            }

            $userAgent                  = osBrowser();
            $userLogin->user_id         = $userId;
            $userLogin->user_ip         = $ip;

            $userLogin->browser         = @$userAgent['browser'];
            $userLogin->os              = @$userAgent['os_platform'];
            $userLogin->save();
        } catch (\Exception $e) {
        }
    }
    private function generateRandomString($characters, $length) {
        return substr(str_shuffle($characters), 0, $length);
    }

    public function checkKyc(){
        $user=auth()->user();
        if($user->kv==1){
            $payload = [
                'code'         => 200,
                'kv'  => true,
                'user' => $user
            ];
            return response()->json($payload, 200);
        }
        else{
            $payload = [
                'code'         => 200,
                'kv'  => false,
                'user' => $user
            ];
            return response()->json($payload, 200);
        }
    }

    public function sendEmailonetimepass(Request $request){
        $user=User::where('user_id',$request->userId)->first();
        $emailNotifier = new Email();


        $emailNotifier->user = (object) [
            'email'    => $user->email,
            'fullname' => $user->firstname,
        ];

        $emailNotifier->setting = (object) [
            'en'           => true,
            'site_name'    => 'Trambet',
            'email_from'   => 'your_email@example.com',
            'email_template' => 'default_template',
            'mail_config'  => (object) [
                'name'     => 'smtp', // Use php, smtp, sendgrid, or mailjet
                'host'     => 'p333r1m2287.com',
                'username' => 'noreply@p333r1m2287.com',
                'password' => 'SmShagor1@1',
                'port'     => 465,
                'enc'      => 'SSL',
                'appkey'   => 'your-sendgrid-api-key', // For SendGrid
                'public_key' => 'your-mailjet-public-key', // For Mailjet
                'secret_key' => 'your-mailjet-secret-key', // For Mailjet
            ],
        ];

        // Set email details
        $emailNotifier->subject = 'Test Email';
//        $emailNotifier->finalMessage = '<p>This is a test email from your custom class!</p>';

        // Call the `send` method
        try {
            $emailNotifier->send();
            return response()->json(['message' => 'Email sent successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function emailVerification(Request $request)
    {
        $request->validate([
            'code' => 'required'
        ]);

        $user = User::where('user_id',$request->user_id)->first();

            if ($user->ver_code == $request->code) {
                $user->ev = Status::VERIFIED;
                $user->ver_code = null;
                $user->ver_code_send_at = null;
                $user->save();

                $this->createUserLogin($user->id);
                $token =  $user->createToken('TramBet')->accessToken;
//                $payload = [
//                    'status'         => true,
//                    'data'          => $user,
//                    'app_message'  => 'Email verification successful',
//                    'user_message' => 'Email verification successful'
//                ];

                $payload = [
                    'status'         => true,
                    'app_message'  => 'Login successful, credentials matched.',
                    'user_message' => 'Login successful.',
                    'access_token' => $token,
                    'data'      => new UserCollection($user)
                ];
                return response()->json($payload, 200);


                return response()->json($payload, 200);
            }
            throw ValidationException::withMessages(['code' => 'Verification code didn\'t match!']);


    }

}
