<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;


Route::namespace('User\Auth')->name('user.')->group(function () {

    Route::controller('LoginController')->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
        Route::get('logout', 'logout')->middleware('auth')->name('logout');
    });

    Route::controller('RegisterController')->group(function () {
        Route::get('register', 'showRegistrationForm')->name('register');
        Route::post('register', 'register')->middleware('registration.status');

        Route::get('oneclick/register', 'showOneClickRegistrationForm')->name('oneclick.register');
        Route::post('oneclick/register', 'oneClickRegister')->middleware('registration.status');

        Route::get('affiliate/register', 'showAffiliateRegistrationForm')->name('affiliate.register');
        Route::post('affiliate/register', 'registerAffiliate')->middleware('registration.status');

        Route::post('check-mail', 'checkUser')->name('checkUser');
    });

    Route::controller('ForgotPasswordController')->prefix('password')->name('password.')->group(function () {
        Route::get('reset', 'showLinkRequestForm')->name('request');
        Route::post('email', 'sendResetCodeEmail')->name('email');
        Route::get('code-verify', 'codeVerify')->name('code.verify');
        Route::post('verify-code', 'verifyCode')->name('verify.code');
    });

    Route::controller('ResetPasswordController')->group(function () {
        Route::post('password/reset', 'reset')->name('password.update');
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset');
    });
});


Route::middleware('auth')->name('user.')->group(function () {
    //authorization
    Route::namespace('User')->controller('AuthorizationController')->group(function () {
        Route::get('authorization', 'authorizeForm')->name('authorization');
        Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
        Route::post('verify-email', 'emailVerification')->name('verify.email');
        Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
        Route::post('verify-g2fa', 'g2faVerification')->name('go2fa.verify');
    });


    // Notification
    Route::namespace('User')->controller('UserNotificationController')->prefix('notifications')->name('notify.')->group(function () {
        Route::get('/', 'notificationDetails')->name('url');
    });

    // Tramcard
     Route::namespace('User')->controller('UserController')->prefix('tramcards')->name('tram.')->group(function () {
        Route::get('/', 'allTramcards')->name('card')->middleware('kyc');
        Route::get('/claim', 'tramCardClaim')->name('card.claim')->middleware('kyc');
    });



    Route::middleware(['check.status'])->group(function () {

        Route::get('user-data', 'User\UserController@userData')->name('data');
        Route::post('user-data-submit', 'User\UserController@userDataSubmit')->name('data.submit');

        Route::middleware('registration.complete')->namespace('User')->group(function () {

            Route::controller('UserController')->group(function () {
                Route::get('dashboard', 'home')->name('home');
                Route::post('one/time/pass', 'oneTimePassDismiss')->name('one.time.pass');

                Route::get('/affiliate/application/form', 'affiliateApplicationForm')->name('affiliate.application.form');
                Route::post('/affiliate/application/submit', 'affiliateApplicationFormSubmit')->name('affiliate.application.submit');

                //2FA
                Route::prefix('twofactor')->name('twofactor')->group(function () {
                    Route::get('/', 'show2faForm');
                    Route::post('enable', 'create2fa')->name('.enable');
                    Route::post('disable', 'disable2fa')->name('.disable');
                });

                //KYC
                Route::prefix('kyc')->name('kyc.')->group(function () {
                    Route::get('form', 'kycForm')->name('form');
                    Route::get('data', 'kycData')->name('data');
                    Route::post('submit', 'kycSubmit')->name('submit');
                });

                //Report
                Route::any('deposit/history', 'depositHistory')->name('deposit.history');
                Route::get('transactions', 'transactions')->name('transactions');

                // Referral
                Route::get('referral/commissions', 'referralCommissions')->name('referral.commissions');
                Route::get('referred/users', 'myRef')->name('referral.users');
                Route::get('referred/myRefLink', 'myRefLink')->name('referral.myRefLink');

                // Attachment Download
                Route::get('attachment-download/{fil_hash}', 'attachmentDownload')->name('attachment.download');
            });

            //Profile setting
            Route::controller('ProfileController')->group(function () {
                Route::get('profile-setting', 'profile')->name('profile.setting');
                Route::post('profile-setting', 'submitProfile');
                Route::get('change-password', 'changePassword')->name('change.password');
                Route::post('change-password', 'submitPassword');
            });

            // Withdraw
            Route::controller('WithdrawController')->prefix('withdraw')->name('withdraw')->group(function () {
                Route::middleware(['kyc', 'affiliate.withdrawal'])->group(function () {
                    Route::get('/', 'withdrawMoney');
                    Route::post('/', 'withdrawStore')->name('.money');
                    Route::get('preview', 'withdrawPreview')->name('.preview');
                    Route::post('preview', 'withdrawSubmit')->name('.submit');
                });
                Route::get('history', 'withdrawLog')->name('.history');
            });

            // User Bets
            Route::controller('BetController')->prefix('bet')->name('bet.')->group(function () {
                Route::post('place-bet', 'placeBet')->name('place');
            });


            Route::resource('news', 'NewsController');
            Route::get('events', 'NewsController@events')->name('events');

            Route::resource('promotions', 'PromotionsController');


            Route::get('my-bets/{type?}', 'BetLogController@index')->name('bets');
            Route::get('/bonus', 'NewsController@bonuseLog')->name('bonus.log')->middleware('kyc');
            Route::get('/bonus-claim', 'NewsController@bonusClaim')->name('bonus.claim')->middleware('kyc');
            Route::get('/referral-claim/{id}', 'NewsController@referralClaim')->name('bonus.referral.claim')->middleware('kyc');
        });

        // Payment
        Route::middleware('registration.complete')->prefix('deposit')->name('deposit.')->controller('Gateway\PaymentController')->group(function () {
            Route::any('/', 'deposit')->name('index');
            Route::get('/mob-cash', 'mobCash')->name('mobcash');
            Route::get('/withdraw/mob-cash', 'withdrawMobCash')->name('withdraw.mobcash');
            Route::get('/mob-cash/agent', 'mobCashAgents')->name('mobcash.agents');
            Route::post('insert', 'depositInsert')->name('insert');
            Route::get('confirm', 'depositConfirm')->name('confirm');
            Route::get('manual', 'manualDepositConfirm')->name('manual.confirm');
            Route::post('manual', 'manualDepositUpdate')->name('manual.update');
            Route::get('get-agents-by-provider', 'getAgentByProvider')->name('agent');
        });
        Route::middleware('registration.complete')->prefix('withdraw')->name('withdraw.')->controller('Gateway\PaymentController')->group(function () {
            Route::get('/withdraw/mob-cash', 'withdrawMobCash')->name('mobcash');
        });
    });

    Route::post('profile/mode/change', [UserController::class, 'setProfileMode'])->name('profile.mode'); // Use the UserController class with the ::class syntax
});

//Casino
Route::controller('CasinoController')->prefix('casino')->name('casino.')->group(function () {
    Route::get('live-casino', 'liveCasino')->name('live');
    Route::get('get-casino', 'getCasinoData')->name('data');
    Route::get('open-game', 'openCasinoGame')->name('open.game');
    Route::get('close-game', 'closeCasinoGame')->name('close.game');
    Route::get('casino-game-open', 'casinoGameOpen')->name('game.open');
    Route::get('casino-history', 'casinoHistory')->name('history')->middleware('auth');
    Route::get('casino-session/{session}', 'casinoSession')->name('session')->middleware('auth');
});



Route::middleware('auth')->name('affiliate.')->group(function () {
    Route::middleware(['check.status'])->group(function () {
        Route::prefix('affiliate')->group(function () {
            Route::get('/promo/register/user', 'User\AffiliateController@affiliatepromo')->name('promos.register.user');
            Route::get('/transaction/history', 'User\AffiliateController@transactionDetails')->name('transaction.history');
            Route::get('/report/summery', 'User\AffiliateController@summery')->name('report.summery');
            Route::get('/player/reports', 'User\AffiliateController@playerreport')->name('report.playerreport');
            Route::get('/full/report', 'User\AffiliateController@fullreport')->name('report.fullreport');
            Route::get('/link/report', 'User\AffiliateController@affiliatelink')->name('report.affiliatelink');
            Route::post('/link/genarate', 'User\AffiliateController@affiliatelinkgenarate')->name('report.affiliatelinkgenarate');
            Route::get('/website/list', 'User\AffiliateController@websiteList')->name('website.list');
            Route::post('/website/create', 'User\AffiliateController@websiteAdd')->name('website.create');
            Route::delete('/website/delete', 'User\AffiliateController@websiteDelete')->name('website.delete');
            Route::get('/website/edit/{id}', 'User\AffiliateController@websiteEdit')->name('website.edit');
            Route::post('/website/update', 'User\AffiliateController@websiteUpdate')->name('website.update');

        });
    });
});
