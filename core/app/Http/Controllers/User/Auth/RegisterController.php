<?php

namespace App\Http\Controllers\User\Auth;

use App\Models\User;
use App\Models\Currency;
use App\Constants\Status;
use App\Models\Promotion;
use App\Models\Website;
use App\Models\UserBonusList;
use App\Models\AffiliateWebsite;
use App\Models\UserLogin;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use App\Models\AffiliatePromos;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Auth\RegistersUsers;
use Carbon\Carbon;
use App\Notifications\TramcardSendNotification;
class RegisterController extends Controller
{

    use RegistersUsers;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest');
        $this->middleware('registration.status')->except('registrationNotAllowed');
        // $this->middleware('registration.status')->except('registrationNotAllowed');
    }

    public function showRegistrationForm($reference = null)
    {
        $pageTitle = "Register";
        if ($reference) {
            session()->put('reference', $reference);
        }
        $info       = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = @implode(',', $info['code']);
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $currencies = Currency::get();
        return view($this->activeTemplate . 'user.auth.register', compact('pageTitle', 'mobileCode', 'countries', 'currencies'));
    }

    protected function validator(array $data)
    {
        $general            = gs();
        $passwordValidation = Password::min(6);
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }
        $agree = 'nullable';
        if ($general->agree) {
            $agree = 'required';
        }
        $countryData  = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));
        $validate     = Validator::make($data, [
            'email'        => 'required|string|email|unique:users',
            'mobile'       => 'required|regex:/^([0-9]*)$/',
            'password'     => ['required', 'confirmed', $passwordValidation],
            // 'username'     => 'required|unique:users|min:6',
            'captcha'      => 'sometimes|required',
            'mobile_code'  => 'required|in:' . $mobileCodes,
            'country_code' => 'required|in:' . $countryCodes,
            'currencies'   => 'required',
            'country'      => 'required|in:' . $countries,
            'agree'        => $agree,
        ]);
        return $validate;
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        $request->session()->regenerateToken();

        if (preg_match("/[^a-z0-9_]/", trim($request->username))) {
            $notify[] = ['info', 'Username can contain only small letters, numbers and underscore.'];
            $notify[] = ['error', 'No special character, space or capital letters in username.'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $exist = User::where('mobile', $request->mobile_code . $request->mobile)->first();
        if ($exist) {
            $notify[] = ['error', 'The mobile number already exists'];
            return back()->withNotify($notify)->withInput();
        }

        event(new Registered($user = $this->create($request->all())));
        // Welcome bonus
        $this->welcomeBonus($user);

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    protected function create(array $data)
    {

        $general = gs();

        $referBy = session()->get('reference');
        $referUser  = $referBy ? User::where('referral_code', $referBy)->first() : null;
        $userId = User::latest()->first() ? User::latest()->first()->user_id + 1 : 100001;
        //User Create
        $user                = new User();
        $user->user_id       = $userId;
        $user->email         = strtolower($data['email']);
        $user->password      = Hash::make($data['password']);
        $user->username      = $userId;
        $user->ref_by        = $referUser ? $referUser->id : 0;
        $user->referral_code = getTrx();
        $user->country_code  = $data['country_code'];
        $user->mobile        = $data['mobile_code'] . $data['mobile'];
        $user->currency      = $data['currencies'];
        $user->address       = [
            'address' => '',
            'state'   => '',
            'zip'     => '',
            'country' => isset($data['country']) ? $data['country'] : null,
            'city'    => '',
        ];
        $user->kv = $general->kv ? Status::NO : Status::YES;
        $user->ev = $general->ev ? Status::NO : Status::YES;
        $user->sv = $general->sv ? Status::NO : Status::YES;
        $user->ts = 0;
        $user->tv = 1;
        $user->save();

        if($data['promo'] != null){
            $this->promotion($data['promo'], $user->id);
        }
        $this->createAdminNotification($user->id);

        //Login Log Create
        $this->createUserLogin($user->id);

        return $user;
    }

    public function checkUser(Request $request)
    {
        $exist['data'] = false;
        $exist['type'] = null;
        if ($request->email) {
            $exist['data'] = User::where('email', $request->email)->exists();
            $exist['type'] = 'email';
        }
        if ($request->mobile) {
            $exist['data'] = User::where('mobile', $request->mobile)->exists();
            $exist['type'] = 'mobile';
        }
        if ($request->username) {
            $exist['data'] = User::where('username', $request->username)->exists();
            $exist['type'] = 'username';
        }
        return response($exist);
    }

    public function showOneClickRegistrationForm($reference = null)
    {
        $ip = file_get_contents('https://api.ipify.org?format=json');
        $ip = json_decode($ip, true);
        $currency = null;
        $country  = null;
        $countryCd = null;
        // if ($ip && isset($ip['ip'])) {
        //     $locationData = file_get_contents('http://ip-api.com/json/' . $ip['ip'] . '?fields=status,continent,continentCode,country,countryCode,region,regionName,city,zip,lat,lon,timezone,currency,query');
        //     $locationData = json_decode($locationData, true);

        //     if ($locationData && $locationData['status'] === 'success') {
        //         $currency = $locationData['currency'];
        //         $country = $locationData['country'];
        //         $countryCd = $locationData['countryCode'];
        //     } else {
        //         echo "Unable to fetch location data";
        //     }
        // }
        $pageTitle = "Register";
        if ($reference) {
            session()->put('reference', $reference);
        }
        $info       = json_decode(json_encode(getIpInfo()), true);
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        $currencies = Currency::get();
        return view($this->activeTemplate . 'user.auth.oneclick', compact('pageTitle', 'countries', 'currencies', 'currency', 'country', 'countryCd'));
    }

    protected function oneClickvalidator(array $data)
    {
        $general            = gs();
        $agree              = 'nullable';
        if ($general->agree) {
            $agree = 'required';
        }
        $countryData        = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes       = implode(',', array_keys($countryData));
        $countries          = implode(',', array_column($countryData, 'country'));
        $validate           = Validator::make($data, [
            'email'         => 'nullable|email|unique:users',
            'captcha'       => 'sometimes|required',
            'country_code'  => 'required|in:' . $countryCodes,
            'country'       => 'required|in:' . $countries,
            'currencies'    => 'required',
            'agree'         => $agree,
            'promo'         => 'nullable|exists:promotions,promo_code,status,1,is_admin_approved,1',
        ]);
        return $validate;
    }

    public function oneClickRegister(Request $request)
    {
        $this->oneClickvalidator($request->all())->validate();
        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }
        event(new Registered($user = $this->oneclickcreate($request->all())));
        // Welcome bonus
        $this->welcomeBonus($user);

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    protected function oneclickcreate(array $data)
    {
        return DB::transaction(function () use ($data) {
        $general    = gs();
        $referBy    = session()->get('reference');
        $referUser  = $referBy ? User::where('referral_code', $referBy)->first() : null;

        // $username = $this->generateRandomString('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 6) .
        //     $this->generateRandomString('0123456789', 3) .
        //     $this->generateRandomString('!@#$%&_', 1);
        $password = $this->generateRandomString('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&_=','8');
        $userId = User::latest()->first() ? User::latest()->first()->user_id + 1 : 100001;
        //User Create
        $user                   = new User();
        $user->user_id          = $userId;
        $user->password         = Hash::make($password);
        $user->username         = $userId;
        $user->one_time_pass    = $password;
        $user->ref_by           = $referUser ? $referUser->id : 0;
        $user->referral_code    = getTrx();
        $user->country_code     = $data['country_code'];
        $user->currency         = $data['currencies'];
        $user->address          = [
                                    'address' => '',
                                    'state'   => '',
                                    'zip'     => '',
                                    'country' => isset($data['country']) ? $data['country'] : null,
                                    'city'    => '',
                                ];
        $user->kv               = $general->kv ? Status::NO : Status::YES;
        $user->ev               = 1;
        $user->sv               = $general->sv ? Status::NO : Status::YES;
        $user->profile_complete = 1;
        $user->ts               = 0;
        $user->tv               = 1;
        $user->save();

        if($data['promo'] != null){
            $this->promotion($data['promo'], $user->id);
        }
        $this->createAdminNotification($user->id);
        //Login Log Create
        $this->createUserLogin($user->id);

        return $user;
    });
    }

    public function registered()
    {
        if (auth()->user()->is_affiliate == 1 || auth()->user()->email != null) {
            return to_route('user.home');
        } else {
            return to_route('home');
        }
    }


    public function showAffiliateRegistrationForm($reference = null)
    {
        $pageTitle = "Affiliate Register";
        if ($reference) {
            session()->put('reference', $reference);
        }
        $info       = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = @implode(',', $info['code']);
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $currencies = Currency::get();
        return view($this->activeTemplate . 'user.auth.affiliateregister', compact('pageTitle', 'mobileCode', 'countries', 'currencies'));
    }
    public function registerAffiliate(Request $request)
    {
        $this->validator($request->all())->validate();
        $request->session()->regenerateToken();

        if (preg_match("/[^a-z0-9_]/", trim($request->username))) {
            $notify[] = ['info', 'Username can contain only small letters, numbers and underscore.'];
            $notify[] = ['error', 'No special character, space or capital letters in username.'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $exist = User::where('mobile', $request->mobile_code . $request->mobile)->first();
        if ($exist) {
            $notify[] = ['error', 'The mobile number already exists'];
            return back()->withNotify($notify)->withInput();
        }

        event(new Registered($user = $this->createAffiliate($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }
    protected function createAffiliate(array $data)
    {
        $general    = gs();
        $referBy    = session()->get('reference');
        $referUser  = $referBy ? User::where('referral_code', $referBy)->first() : null;

        // Begin transaction
        DB::beginTransaction();

        try {
            $user = DB::transaction(function () use ($data, $general, $referUser) {
                $userId = User::latest()->first() ? User::latest()->first()->user_id + 1 : 100001;
                $user                = new User();
                $user->user_id       = $userId;
                $user->email         = strtolower($data['email']);
                $user->password      = Hash::make($data['password']);
                $user->username      = $userId;
                $user->ref_by        = $referUser ? $referUser->id : 0;
                $user->referral_code = getTrx();
                $user->country_code  = $data['country_code'];
                $user->mobile        = $data['mobile_code'] . $data['mobile'];
                $user->currency   = $data['currencies'];
                $user->address       = [
                    'address' => '',
                    'state'   => '',
                    'zip'     => '',
                    'country' => isset($data['country']) ? $data['country'] : null,
                    'city'    => '',
                ];
                $user->kv = $general->kv ? Status::NO : Status::YES;
                $user->ev = $general->ev ? Status::NO : Status::YES;
                $user->sv = $general->sv ? Status::NO : Status::YES;
                $user->ts = 0;
                $user->tv = 1;
                $user->is_affiliate = 1;
                $user->profile_mode = 'affiliate';
                $user->save();

                $this->createPromoCode($user->id);
                $this->createAdminNotification($user->id);
                $this->createUserLogin($user->id);

                return $user;
            });
            DB::commit();
            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    private function generateRandomString($characters, $length) {
        return substr(str_shuffle($characters), 0, $length);
    }
    private function createPromoCode($userId){
        $promoCode                  = $this->generateRandomString('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 6);
        $promo                      = new Promotion();
        $promo->user_id             = $userId;
        $promo->title               = "Affiliate Promo Code";
        $promo->promo_code          = $promoCode;
        $promo->is_admin_approved   = 0;
        $promo->status              = 1;
        $promo->save();
    }
    private function createAdminNotification($userId)
    {
        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $userId;
        $adminNotification->title     = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail', $userId);
        $adminNotification->save();
    }
    private function createUserLogin($userId)
    {
        $ip        = getRealIP();
        $exist     = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();

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
    }
    private function promotion($promoCode, $userId)
    {
        $promo = Promotion::where('promo_code', $promoCode)
        ->where('status', 1)
        ->where('is_admin_approved', 1)
        ->first();
        $website1 = Website::where('webId', session()->get('linkId'))->first();
        $website = AffiliateWebsite::where('affiliate_id', $promo->user_id)->first();
        if($promo){
            $affiliatePromo = new AffiliatePromos();
            $affiliatePromo->affliate_user_id = $promo->user_id;
            $affiliatePromo->better_user_id = $userId;
            $affiliatePromo->promo_id = $promo->id;
            $affiliatePromo->website = $website1->aff_website;
            $affiliatePromo->websiteId = $website->websiteId;
            $affiliatePromo->save();
        } else {
            $notify[] = ['error', 'Promo code not found'];
            return back()->withNotify($notify);
        }
    }
    // Welcome bonus
    public function welcomeBonus($user){
        try{
            DB::beginTransaction();

            $totalSeconds = 24 * 60 * 60;
            $futureDateTime = Carbon::now()->addSeconds($totalSeconds);

            $bonus = new UserBonusList;
            $bonus->user_id = $user->id;
            $bonus->type = 'welcome';
            $bonus->initial_amount = 300;
            $bonus->currency = $user->currency;
            $bonus->valid_time = $futureDateTime;
            $bonus->duration = 1;
            $bonus->duration_text = '24 hours';
            $bonus->save();

            $user->increment('bonus_account', 300);


            // Notify to user
            $userNotify = new UserNotification;
            $userNotify->user_id = $user->id;
            $userNotify->title = "Congratulations! You have to got 300 ".$user->currency." welcome bonus for 24 hours";
            $userNotify->url = "/user/bonus";
            $userNotify->save();

            $userNotify->notify(new TramcardSendNotification($userNotify));


            DB::commit();
        } catch(\Exception $e){
             DB::rollback();
             $notify[] = ['error', 'something went wrong'];
             return back()->withNotify($notify);
        }
    }
}
