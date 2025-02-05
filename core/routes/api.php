<?php

use App\Http\Controllers\Admin\EventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V2\BetController;
use App\Http\Controllers\Api\V2\SupportController;
use App\Http\Controllers\Api\V2\HomepageController;
use App\Http\Controllers\Api\V2\AuthController as AuthController;
use App\Http\Controllers\Api\V2\HomepageGameController;
use App\Http\Controllers\Api\V2\GameController as GameController;
use App\Http\Controllers\Api\V2\UserController as UserController;
use App\Http\Controllers\Api\V2\CasinoController as CasinoController;
use App\Http\Controllers\Api\V2\CasinoBonusController as CasinoBonusController;
use App\Http\Controllers\Api\V2\CommonController as CommonController;
use App\Http\Controllers\Api\V2\TicketController as TicketController;
use App\Http\Controllers\Api\V2\DepositController as DepositController;
use App\Http\Controllers\Api\V2\PaymentController as PaymentController;
use App\Http\Controllers\Api\V2\WithdrawController as WithdrawController;
use App\Http\Controllers\Api\V2\AffiliateController as AffiliateController;
use App\Http\Controllers\Api\V2\ExternalApiController;
use App\Http\Controllers\Api\V2\NewsController as NewsController;
use App\Http\Controllers\Api\V2\PromotionController;
use App\Http\Controllers\Api\V2\CricketController;


// Route::get('/hel', function(){

    // $data = \App\Models\Game::where('game_id', '108049171')->get();
    // return response()->json(['data' => $data], 200);
// });
Route::get('/hel', function(){
    return response()->json(['data' => []], 200)
        ->header('Connection', 'keep-alive');
});



Route::any('/get-casino-data', [App\Http\Controllers\Api\CasinoPlayerController::class, 'casinoPlayer'])->name('casino.data.from.server');
Route::group(['prefix' => 'v2', 'as' => 'api.', 'namespace' => 'Api'], function () {
    Route::get('/games', [CricketController::class, 'games'])->name('bet.games.data');

    Route::get('/homepagegamesodds', [HomepageGameController::class, 'filteredCricketOdds']);
    
    Route::get('/check-response', [ExternalApiController::class, 'checkResponse']);
    Route::get('/response', [ExternalApiController::class, 'getResponse']);
    Route::get('/categories', [ExternalApiController::class, 'categoryResponse']);
    Route::get('/games/{id}/{league}', [ExternalApiController::class, 'gameResponse']);
    Route::get('/logo/{name}', [ExternalApiController::class, 'logoResponse']);
    Route::get('/logo/{name}', [ExternalApiController::class, 'logoResponse']);
    
    Route::get('/category-data/{id}/{page?}', [ExternalApiController::class, 'categoryData']);
    
    Route::get('/ws/{name}', [ExternalApiController::class, 'getWSData']);
    Route::get('/in-play-games/{in}', [ExternalApiController::class, 'inPlayGames']);
    Route::get('/in-play-games-details/{in}/{match}', [ExternalApiController::class, 'inPlayGameDetails']);
    Route::get('/in-play-matches/{in}', [ExternalApiController::class, 'inPlayMatches']);
    
    
    
    Route::post('login', [AuthController::class, 'login']);
    Route::post('signup', [AuthController::class, 'signup']);
    Route::post('send-reset-code', [AuthController::class, 'sendResetCode']);
    Route::post('otp-send-for-forgot-password', [AuthController::class, 'otpForForgotPassword']);
    Route::post('verify-reset-code', [AuthController::class, 'verifyResetCode']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('oneclick-signup', [AuthController::class, 'OneClickSignup']);
    Route::get('get-privacy-policy', [CommonController::class, 'getPrivacyPolicy']);
    Route::get('terms-of-service', [CommonController::class, 'getTermsOfService']);
    Route::get('refund-policy', [CommonController::class, 'getRefundPolicy']);
    Route::get('news/{page}', [CommonController::class, 'getAllNews']);
    Route::get('news-details/{id}', [CommonController::class, 'getAllNewsDetails']);
    Route::get('kycform', [CommonController::class, 'kycForm']);
    Route::get('sport/category/{type}', [GameController::class, 'getGameCategory']);
    Route::post('verify-email', [UserController::class, 'emailVerification']);
    Route::post('verify-email-forgot-password', [UserController::class, 'emailVerification']);
    Route::post('change-forgot-password', [UserController::class, 'changeForgotPassword']);






    Route::get('leaugeLogo/{match}/{id}', [ExternalApiController::class, 'getLeaugeImage']);
    
   Route::prefix('casino')->name('casino')->group(function () {
        Route::get('live', [CasinoController::class, 'getLiveCasino']);

    });

    Route::prefix('casinobonus')->name('casino')->group(function () {
        Route::get('live', [CasinoBonusController::class, 'getLiveCasinobonus']);
    });

    Route::get('homepagelivegames', [HomepageGameController::class, 'getLivegames']);
    Route::get('homepageupcominggames', [HomepageGameController::class, 'getUpcominggames']);
    Route::get('homepagefeaturegames', [HomepageGameController::class, 'getfeatureGames']);

    Route::post('verify-email-registration', [AuthController::class, 'emailVerification']);


    Route::middleware(['auth:api'])->group(function () {
        Route::post('verify-email', [UserController::class, 'emailVerification']);
        Route::get('resend-verify/{type}', [AuthController::class, 'reSendVerifyCode']);
        Route::post('profile/mode/change', [UserController::class, 'setProfileMode'])->name('profile.mode');
        Route::get('welcome-bonus-reject', [AuthController::class, 'rejectWelcomeBonus']);
        Route::get('check-active-bonus', [AuthController::class, 'checkActiveBonus']);
        Route::get('reject-bonus-for-withdraw', [AuthController::class, 'rejectBonusForWithdraw']);
        Route::get('check-kyc', [AuthController::class, 'checkKyc']);
        Route::post('sendotp-email-onetime-user', [AuthController::class, 'sendOtpEmailOnetime']);
        Route::post('send-email-to-oneclick', [AuthController::class, 'sendEmailonetimepass']);
    });

    Route::middleware(['auth:api'])->group(function () {
        Route::get('casino/favorites', [CasinoController::class, 'getFavorites']);
        Route::post('casino/favorites/toggle', [CasinoController::class, 'toggleFavorite']);
        Route::post('casino/addresentplayed', [CasinoController::class, 'addRecentlyPlayed']);
        Route::get('casino/getrecentplayed', [CasinoController::class, 'getRecentlyPlayed']);
    });


    Route::middleware(['auth:api','check.status'])->group(function () {
        Route::post('bet-store', [BetController::class, 'betStore']);
        // Route::get('bet-history', [BetController::class, 'betHistory']);
        Route::get('bet-history', [BetController::class, 'betHistory']);
        Route::post('/change-password', [UserController::class, 'changePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('my-profile', [UserController::class, 'getProfile']);
        Route::get('notifications', [UserController::class, 'getNotifications']);
        Route::get('notifications-all', [UserController::class, 'getAllNotifications']);
        Route::post('notifications-update', [UserController::class, 'updateNotifications']);
        Route::post('user-profile-update', [UserController::class, 'profileUpdate']);

        Route::prefix('casino')->name('casino')->group(function () {
            Route::get('game/open', [CasinoController::class, 'openCasinoGame']);
            Route::get('history/{page?}/{per_page?}', [CasinoController::class, 'casinoHistory']);
            Route::get('session/{session}', [CasinoController::class, 'casinoSession']);
        });

        Route::prefix('casinobonus')->name('casino')->group(function () {
            Route::get('game/open', [CasinoBonusController::class, 'openCasinoGame']);
            Route::get('history/{page?}/{per_page?}', [CasinoBonusController::class, 'casinoHistory']);
            Route::get('session/{session}', [CasinoBonusController::class, 'casinoSession']);
        });

        Route::post('affiliate-application-submit', [UserController::class, 'affiliateApplicationFormSubmit']);
        Route::get('affiliate-application-list', [UserController::class, 'affiliateApplyList']);



        //TrampCard
        Route::get('tram-card', [UserController::class, 'getTrampCard']);
        Route::get('tram-card-claim', [UserController::class, 'tramCardClaim']);
        Route::get('casino', [GameController::class, 'getCasinoData']);


        // Bonus
        Route::get('bonus-log', [NewsController::class, 'bonusLog']);
        Route::get('bonus-claim', [NewsController::class, 'bonusClaim']);
        Route::get('referral-claim/{id}', [NewsController::class, 'referralClaim']);


        Route::post('kyc-submit', [UserController::class, 'kycSubmit']);
     
        //2FA
        Route::prefix('twofactor')->name('twofactor')->group(function () {
            Route::get('/', [UserController::class, 'getTwoFactorData']);
            Route::post('enable', [UserController::class, 'createTwoFactor']);
            Route::post('disable', [UserController::class, 'disableTwoFactor']);
        });
        Route::get('referrals', [UserController::class, 'getReferralsData']);
        //2FA
        Route::prefix('deposit')->name('deposit')->group(function () {
            Route::get('list', [DepositController::class, 'getDepositPaymentData']);
            Route::post('deposit-store', [DepositController::class, 'depositStore']);
            Route::get('history/{page?}/{per_page?}', [DepositController::class, 'getDepositHistory']);
            Route::get('getprovider', [DepositController::class, 'getAgentByProvider']);
            Route::get('mob-cash/agent', [PaymentController::class, 'mobCashAgents']);


        });

        Route::prefix('withdraw')->name('withdraw')->group(function () {
            Route::get('list', [WithdrawController::class, 'getWithdrawPaymentData']);
            Route::post('withdraw-store', [WithdrawController::class, 'withdrawStore']);
            Route::post('withdraw-submit', [WithdrawController::class, 'withdrawSubmit']);
            Route::get('history/{page?}/{per_page?}', [WithdrawController::class, 'getWithdrawHistory']);
        });
        Route::prefix('affiliate')->name('affiliate')->group(function () {
            Route::get('/', [AffiliateController::class, 'getDashboardData']);
            Route::get('promotions', [AffiliateController::class, 'getPromotionsData']);
            Route::post('promo/create', [AffiliateController::class, 'createPromo']);
            Route::post('promo/update/{id}', [AffiliateController::class, 'updatePromo']);
            Route::delete('promo/delete/{id}', [AffiliateController::class, 'destroyPromo']);
            Route::post('link/create', [AffiliateController::class, 'linkGenerate']);
            Route::get('promo_user/{page?}/{per_page?}', [AffiliateController::class, 'getPromoUsersData']);
            Route::post('websites-create', [AffiliateController::class, 'websiteAdd']);
            Route::get('websites', [AffiliateController::class, 'getWebsitesData']);
            Route::post('websites-update/{id}', [AffiliateController::class, 'websiteUpdate']);
            Route::post('websites-delete/{id}', [AffiliateController::class, 'websiteDelete']);
            Route::prefix('report')->name('report')->group(function () {
                Route::get('common', [AffiliateController::class, 'getCommonData']);
                Route::get('links', [AffiliateController::class, 'getAffiliateLinkData']);
                Route::post('link/generate', [AffiliateController::class, 'affiliateLinkGenerate']);
                Route::get('details', [AffiliateController::class, 'getDetailsReportData']);
                Route::get('player', [AffiliateController::class, 'getPlayerReportData']);
                Route::get('summery', [AffiliateController::class, 'getSummeryData']);
            });
        });
        Route::prefix('ticket')->name('ticket')->group(function () {
            Route::get('all/{page?}/{per_page?}', [TicketController::class, 'supportTicket']);
            Route::get('bets', [TicketController::class, 'getBetsData']);
            Route::get('view/{ticketId}', [TicketController::class, 'viewTicket']);
            Route::get('download/{ticketId}', [TicketController::class, 'ticketDownload']);

            Route::post('store', [TicketController::class, 'storeSupportTicket']);
            Route::post('reply/{ticket}', [TicketController::class, 'replyTicket']);
            Route::post('close/{ticket}', [TicketController::class, 'closeTicket']);
        });
    });

    Route::get('/promotions', [HomepageController::class, 'getPromotion']);
    Route::get('/promotions/{id}', [HomepageController::class, 'getPromotionDetails']);
    Route::get('/frontend/{type}', [HomepageController::class, 'getFrontendData']);
    Route::get('/frontend/{type}/{id}', [HomepageController::class, 'getNewsDetails']);
    Route::get('/content/{data}', [HomepageController::class, 'getContent']);
    Route::get('/currency', [HomepageController::class, 'currencylist']);
    Route::get('/country', [HomepageController::class, 'countrylist']);
    Route::get('/language', [HomepageController::class, 'languagelist']);
    Route::get('/privacy_policy', [HomepageController::class, 'policypage']);
    Route::get('/terms_of_service', [HomepageController::class, 'termspage']);
    Route::get('/refund', [HomepageController::class, 'refundpage']);

    Route::get('deposit-bonus', [PromotionController::class, 'index']);
    
    Route::get('front/promotions',[HomepageController::class, 'promoBanner']);

});
