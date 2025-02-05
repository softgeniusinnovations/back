<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DeclareOutcomeController;

Route::namespace('Auth')->group(function () {
    Route::controller('LoginController')->group(function () {
        Route::get('/', 'showLoginForm')->name('login');
        Route::post('/', 'login')->name('login');
        Route::get('logout', 'logout')->middleware('admin')->name('logout');
    });




    // Admin Password Reset
    Route::controller('ForgotPasswordController')->prefix('password')->name('password.')->group(function () {
        Route::get('reset', 'showLinkRequestForm')->name('reset');
        Route::post('reset', 'sendResetCodeEmail');
        Route::get('code-verify', 'codeVerify')->name('code.verify');
        Route::post('verify-code', 'verifyCode')->name('verify.code');
    });

    Route::controller('ResetPasswordController')->group(function () {
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset.form');
        Route::post('password/reset/change', 'reset')->name('password.change');
    });
});


// Role Management

Route::middleware(['admin', 'auth:admin'])->group(function () {
    Route::controller('RolePermissionController')->group(function () {
        Route::get('role', 'rolesIndex')->name('role.list');
        Route::post('role/create', 'createRole')->name('role.create');
        Route::get('role/{roleId}/permissions', 'roleHasPermissions')->name('role.has.permissions');
        Route::post('role/{roleId}/permissions', 'roleHasPermissionsUpdate')->name('role.has.permission.update');
    });
});

Route::middleware(['admin', 'auth:admin'])->group(function () {

    Route::controller('ActivitylogController')->group(function () {
        Route::get('activity-logs', 'index')->name('activity');

    });


    // Transaction providers
    Route::controller('TransactionProvidersController')->group(function () {
        Route::get('transaction-providers', 'index')->name('agent.transaction.providers');
        Route::post('transaction-providers/create', 'create')->name('agent.transaction.providers.create');
        Route::get('transaction-providers/{id}/edit', 'edit')->name('agent.transaction.providers.edit');
        Route::put('transaction-providers/{id}/update', 'update')->name('agent.transaction.providers.update');
        Route::get('transaction-providers/{id}/status', 'status')->name('agent.transaction.providers.status');
        Route::delete('transaction-providers/{id}/delete', 'delete')->name('agent.transaction.providers.delete');
    });

    // Crypto wallets
    Route::controller('CriptoWalletController')->group(function () {
        Route::get('cripto-wallet', 'index')->name('agent.diposit.wallet.list');
        Route::post('cripto-wallet/store', 'store')->name('agent.diposit.wallet.store');
        Route::get('cripto-wallet/{id}/edit', 'edit')->name('agent.diposit.wallet.edit');
        Route::put('cripto-wallet/{id}/update', 'update')->name('agent.diposit.wallet.update');
    });

    // Agent Deposit
    Route::controller('AgentDepositController')->group(function () {
        Route::get('agent-deposit', 'index')->name('agent.deposit.list');
        Route::get('agent-deposit/create', 'create')->name('agent.deposit.create');
        Route::post('agent-deposit/store', 'store')->name('agent.deposit.store');
        Route::get('agent-deposit/{id}/edit', 'edit')->name('agent.deposit.edit');
        Route::put('agent-deposit/{id}/update', 'update')->name('agent.deposit.update');
        Route::get('agent-deposit/{id}/details', 'show')->name('agent.deposit.show');
        Route::post('agent-deposit/{id}/approve', 'approve')->name('agent.deposit.approve');
        Route::post('agent-deposit/reject', 'reject')->name('agent.deposit.reject');
        Route::post('agent-deposit/back', 'back')->name('agent.deposit.back');
        Route::get('agent-deposit-pending/{status}', 'depositStatus')->name('agent.deposit.status.pending');
        Route::get('agent-deposit-approve/{status}', 'depositStatus')->name('agent.deposit.status.approve');
        Route::get('agent-deposit-reject/{status}', 'depositStatus')->name('agent.deposit.status.reject');
        Route::get('agent-deposit-back/{status}', 'depositStatus')->name('agent.deposit.status.back');
    });

    // Password request process
     Route::controller('PasswordRequestController')->group(function () {
        Route::get('password-request', 'index')->name('agent.password.request.list');
        Route::get('password-request/{id}', 'show')->name('agent.password.request.edit');
        Route::post('password-request/{id}', 'update')->name('agent.password.request.update');
    });


    Route::controller('AdminController')->group(function () {

        // Agent area
        Route::get('agents/list', 'agentsList')->name('agent.list');
        Route::get('create', 'agentCreate')->name('agent.create');
        Route::post('admin-register', 'adminRegister')->name('agent.register');
        Route::get('agent/{id}/edit', 'agentEdit')->name('agent.edit');
        Route::put('agent/{id}/update', 'agentUpdate')->name('agent.update');
        Route::get('agent/{id}/details', 'agentDetails')->name('agent.details');
        Route::get('agent/{id}/status', 'agentStatusChange')->name('agent.status');
        Route::post('agent/provider/status', 'agentTransectionProviderStatusChange')->name('agent.provider.status');
        Route::get('agent-threshold', 'thresholdValue')->name('agent.threshold.index');
        Route::post('agent-threshold/{id}', 'agentThreshold')->name('agent.threshold');
        Route::get('agent-password-change/{id}', 'agentPasswordChange')->name('agent.password');
        Route::put('agent-password-change/{id}', 'agentPasswordChanged')->name('agent.password.change');
        Route::get('agent-dashboard/{id}', 'loginAgentDashboard')->name('agent.dashboard');
        Route::get('agent-change-amount/{id}', 'changeAgentAmount')->name('agent.change.amount');
        Route::post('agent-change-amount/{id}', 'changedAgentAmount')->name('agent.changed.amount');
        
        // Make Bettor Deposit
        Route::get('search-bettor', 'bettors')->name('search.bettor');
        Route::get('agent/make-bettor-deposit-page', 'makeBettorDepositPage')->name('agent.make.bettor.deposit.page');
        Route::post('agent/make-bettor-deposit', 'makeBettorDeposit')->name('agent.make.bettor.deposit');
        // Make Bettor Withdraw
        Route::get('agent/make-bettor-withdraw-page', 'makeBettorWithdrawPage')->name('agent.make.bettor.withdraw.page');
        Route::post('agent/make-bettor-withdraw', 'makeBettorWithdraw')->name('agent.make.bettor.withdraw');
        
        
        // Admin area
        Route::get('admins', 'adminLists')->name('admin.list');
        Route::post('admin/create', 'adminCreate')->name('admin.create');
        Route::get('admin/{id}/edit', 'adminEdit')->name('admin.edit');
        Route::put('admin/{id}/updated', 'adminUpdate')->name('admin.by.update');



        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('profile', 'profile')->name('profile');
        Route::post('profile', 'profileUpdate')->name('profile.update');
        Route::get('password', 'password')->name('password');
        Route::post('password', 'passwordUpdate')->name('password.update');

        //Notification
        Route::get('notifications', 'notifications')->name('notifications');
        Route::get('notification/read/{id}', 'notificationRead')->name('notification.read');
        Route::get('notifications/read-all', 'readAll')->name('notifications.readAll');

        //Report Bugs
        Route::get('request-report', 'requestReport')->name('request.report');
        Route::post('request-report', 'reportSubmit');

        Route::get('download-attachments/{file_hash}', 'downloadAttachment')->name('download.attachment');
    });

    // Affiliate
    Route::controller('AffiliateController')->group(function () {
        Route::get('/affiliate', 'affiliateList')->name('affiliate.list');
        Route::get('/affiliate/company-expenses', 'companyExpenses')->name('affiliate.company_expenses');
        Route::post('/affiliate/company-expenses/store', 'companyExpenseStore')->name('affiliate.company_expenses.store');

        Route::get('/affiliate/details/{id}', 'affiliateDetails')->name('affiliate.details');
        Route::get('/affiliate/promocode', 'promocodeList')->name('affiliate.promocode.list');
        Route::get('/affiliate/promocode/edit/{id}', 'promoCodeEdit')->name('affiliate.promoCodeEdit');
        Route::post('/affiliate/promocode/accept', 'promoCodeUpdate')->name('affiliate.promoCodeUpdate');
        Route::post('/affiliate/promocode/reject', 'promoCodeReject')->name('affiliate.promoCodeReject');

        Route::get('/affiliate/application/request', 'betterApplication')->name('affiliate.better.application');
        Route::get('/affiliate/application/edit/{id}', 'applicationForm')->name('affiliate.applicationForm');
        Route::post('/affiliate/application/reject', 'applicationreject')->name('affiliate.applicationreject');
        Route::post('/affiliate/application/approve', 'applicationapprove')->name('affiliate.applicationapprove');

        Route::get('/affiliate/withdraw/setting', 'affliateWithdrawsettingView')->name('affiliate.withdraw.setting');
        Route::post('/affiliate/withdraw/setting/store', 'affliateWithdrawsettingStore')->name('affiliate.withdraw.setting.store');
    });
    
    //News And Event
    Route::controller('EventController')->group(function () {
        Route::get('/events', 'index')->name('event.list');
        Route::get('/events/create', 'create')->name('event.create');
        Route::get('/events/send-user', 'eventSendUser')->name('event.send.user');
        Route::get('/events/user-search', 'userSearch')->name('event.user.search');
        Route::post('event/send/bonus/{id}', 'sendBonus')->name('event.send.bonus');
        Route::post('event/send/casino/bonus/{id}', 'sendCasinoBonus')->name('event.send.bonus');
        Route::post('/events/store', 'store')->name('event.store');
        Route::get('/events/edit/{id}', 'edit')->name('event.edit');
        Route::put('/events/update/{id}', 'update')->name('event.update');
        Route::delete('/events/delete/{id}', 'destroy')->name('event.delete');
        
        Route::get('/events/cashback-settings', 'cashbackSettings')->name('event.cashback.settings');
        Route::post('/events/cashback-settings', 'cashbackSettingsUpdate')->name('event.cashback.settings.update');
        Route::post('/events/cashback-casino-settings', 'cashbackSettingsCasinoUpdate')->name('event.cashback.settings.casino.update');
        Route::get('/events/cashback', 'cashbackCommand')->name('event.cashback');

        Route::get('/events/deposit-settings', 'depositSettings')->name('event.deposit.settings');
        Route::get('/events/deposit-bonus-edit/{id}', 'deositSettingsEdit')->name('event.deposit.settings.edit');
        Route::post('/events/deposit-bonus-create', 'deositSettingsCreate')->name('event.deposit.settings.create');
        Route::post('/events/deposit-bonus-update/{id}', 'depositSettingUpdate')->name('event.deposit.settings.update');
        Route::delete('/events/deposit-bonus-delete/{id}', 'deositSettingsDelete')->name('event.deposit.settings.delete');
        
        
        Route::post('/events/promo-banner', 'promoBannerCreate')->name('event.promo.banners');
        Route::get('/events/promo-banners', 'promoBannersList')->name('event.promo.banner.list');
        Route::delete('/events/promo-banner/{id}', 'promoBannerDelete')->name('event.promo.delete');
        
    });
    
    // Tramcard configure
    Route::controller('TramCardController')->prefix('events/tramcard')->name('event.tramcard.')->group(function () {
        Route::get('/', 'index')->name('list');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::get('/show/{id}', 'show')->name('show');
        Route::put('/update/{id}', 'update')->name('update');
        Route::delete('/delete/{id}', 'destroy')->name('delete');
        Route::get('/send/{id}', 'sendTramcard')->name('send');
        Route::post('/send-card/{id}', 'sendTramcardByUser')->name('send.user');
    });
    

    // Category Manager
    Route::controller('CategoryController')->name('category.')->prefix('categories')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('status/{id}', 'status')->name('status');
    });

    // League Manager
    Route::controller('LeagueController')->name('league.')->prefix('leagues')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('status/{id}', 'status')->name('status');
    });

    // Teams Manager
    Route::controller('TeamController')->name('team.')->prefix('teams')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('store/{id?}', 'store')->name('store');
    });

    // Game Manager
    Route::controller('GameController')->name('game.')->prefix('games')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/teams/{categoryId}', 'teamsByCategory')->name('teams');
        Route::get('running', 'running')->name('running');
        Route::get('upcoming', 'upcoming')->name('upcoming');
        Route::get('ended', 'ended')->name('ended');
        Route::get('create', 'create')->name('create');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('status/{id}', 'updateStatus')->name('status');
    });
    Route::controller('HomepageController')->group(function () {
        Route::get('manage-home-game', 'index')->name('managehomegame');
        Route::get('manage-home-game-up', 'upcomingGame')->name('managehomegameup');
        Route::get('manage-home-game-featured', 'featuredGame')->name('managehomegamefeatured');
        Route::post('manage-home-game/live', 'manageLiveGame')->name('manageLiveGame');
        Route::delete('/homepagegame/{id}', 'destroy')->name('homepagegame.destroy');
        Route::post('/homepagegameup/{id}', 'destroyup')->name('homepagegameup.destroy');
        Route::post('/homepagegamefeature/{id}', 'destroyfeature')->name('homepagegamefeature.destroy');

        Route::post('manage-home-game/upcoming', 'manageUpcomingGame')->name('manageUpcomingGame');
        Route::post('manage-home-game/feature', 'manageFeatureGame')->name('manageFeatureGame');
        Route::post('manage-home-game/store-live-games', 'storeLiveGame')->name('storeLiveGame');
        Route::post('manage-home-game/store-upcoming-games', 'storeUpcomingGame')->name('storeUpcomingGame');
        Route::post('manage-home-game/store-feature-games', 'storeFeatureGame')->name('storeFeatureGame');
    });

    // Question Manager
    Route::controller('QuestionController')->name('question.')->prefix('markets')->group(function () {
        Route::get('{id}', 'index')->name('index');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('status/{id}', 'status')->name('status');
        Route::post('locked/{id}', 'locked')->name('locked');
    });

    // Option Manager
    Route::controller('OptionController')->name('option.')->prefix('options')->group(function () {
        Route::get('{id}', 'index')->name('index');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('status/{id}', 'status')->name('status');
        Route::post('locked/{id}', 'locked')->name('locked');
    });

    // Question Manager
    Route::controller('DeclareOutcomeController')->name('outcomes.declare.')->prefix('match/market')->group(function () {
        Route::get('pending-outcomes/{id}', 'pendingOutcomes')->name('pending');
        Route::get('get-matchid/{id}', 'getMatchid')->name('match');
        Route::get('declared-outcomes', 'declaredOutcomes')->name('declared');
        Route::post('refund/{id}', 'refund')->name('refund');
        Route::post('select-winner/{id}', 'winner')->name('winner');
        Route::post('make-action', 'makeAction')->name('make.decision');
        Route::post('declared-question/{id}', 'questionDeclared')->name('question');
        Route::get('pending-game', 'pendingGame')->name('game');
        Route::get('pending-game-upcoming', 'pendingUpcomingGame')->name('game.upcoming');
        Route::post('game/{id}', 'gameEnd')->name('game.end');
        Route::get('upcoming-game-category', 'upcomingGameCategories')->name('upcoming.category');
        Route::get('upcoming-game-category/{name}/{type}', 'upcomingGameCategoryWiseResult')->name('upcoming.category.result');
        Route::get('upcoming-settlement/{id}', 'upcomingSettlement')->name('upcoming.settlement');
    });


    // Bets Manager
    Route::controller('BetController')->prefix('bet')->name('bet.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::get('pending', 'pending')->name('pending');
        Route::get('won', 'won')->name('won');
        Route::get('lose', 'lose')->name('lose');
        Route::get('refunded', 'refunded')->name('refunded');
        Route::get('market/{id}', 'getByQuestion')->name('question');
    });

    // Referral
    Route::controller('ReferralSettingsController')->name('referral.')->prefix('referral-setting')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::get('status/update/{type}', 'updateStatus')->name('status.update');
    });

    // Users Manager
    Route::controller('ManageUsersController')->name('users.')->prefix('bettors')->group(function () {
        Route::get('/', 'allUsers')->name('all');
        Route::get('current-week-active-bettors', 'currentWeekActiveUsers')->name('week.active');
        Route::get('active', 'activeUsers')->name('active');
        Route::get('banned', 'bannedUsers')->name('banned');
        Route::get('email-verified', 'emailVerifiedUsers')->name('email.verified');
        Route::get('email-unverified', 'emailUnverifiedUsers')->name('email.unverified');
        Route::get('mobile-unverified', 'mobileUnverifiedUsers')->name('mobile.unverified');
        Route::get('kyc-unverified', 'kycUnverifiedUsers')->name('kyc.unverified');
        Route::get('kyc-pending', 'kycPendingUsers')->name('kyc.pending');
        Route::get('mobile-verified', 'mobileVerifiedUsers')->name('mobile.verified');
        Route::get('with-balance', 'usersWithBalance')->name('with.balance');

        Route::get('detail/{id}', 'detail')->name('detail');
        Route::post('change-user-password/{user_id}', 'changeUserPassword')->name('password.change');
        
        
        Route::get('kyc-data/{id}', 'kycDetails')->name('kyc.details');
        Route::post('kyc-approve/{id}', 'kycApprove')->name('kyc.approve');
        Route::post('kyc-reject/{id}', 'kycReject')->name('kyc.reject');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('add-sub-balance/{id}', 'addSubBalance')->name('add.sub.balance');
        Route::get('send-notification/{id}', 'showNotificationSingleForm')->name('notification.single');
        Route::post('send-notification/{id}', 'sendNotificationSingle')->name('notification.single');
        Route::get('login/{id}', 'login')->name('login');
        Route::post('status/{id}', 'status')->name('status');

        Route::get('send-notification', 'showNotificationAllForm')->name('notification.all');
        Route::post('send-notification', 'sendNotificationAll')->name('notification.all.send');
        Route::get('list', 'list')->name('list');
        Route::get('notification-log/{id}', 'notificationLog')->name('notification.log');

        // User Bets
        Route::get('bets/{id}', 'bets')->name('bets');

        // User Referrals
        Route::get('refereed-users/{id}', 'refereedUsers')->name('refereed.users');
        Route::get('referral-commissions/{id}', 'referralCommissions')->name('referral.commissions');
    });

    // Deposit Gateway
    Route::name('gateway.')->prefix('gateway')->group(function () {
        // Automatic Gateway
        Route::controller('AutomaticGatewayController')->prefix('automatic')->name('automatic.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('edit/{alias}', 'edit')->name('edit');
            Route::post('update/{code}', 'update')->name('update');
            Route::post('remove/{id}', 'remove')->name('remove');
            Route::post('status/{id}', 'status')->name('status');
        });

        // Manual Methods
        Route::controller('ManualGatewayController')->prefix('manual')->name('manual.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('new', 'create')->name('create');
            Route::post('new', 'store')->name('store');
            Route::get('edit/{alias}', 'edit')->name('edit');
            Route::post('update/{id}', 'update')->name('update');
            Route::post('status/{id}', 'status')->name('status');
        });
    });

    // DEPOSIT SYSTEM
    Route::controller('DepositController')->prefix('deposit')->name('deposit.')->group(function () {
        Route::get('/', 'deposit')->name('list');
        Route::get('commission', 'commission')->name('commission');
        Route::get('pending', 'pending')->name('pending');
        Route::get('rejected', 'rejected')->name('rejected');
        Route::get('approved', 'approved')->name('approved');
        Route::get('successful', 'successful')->name('successful');
        Route::get('initiated', 'initiated')->name('initiated');
        Route::get('details/{id}', 'details')->name('details');
        Route::post('request-deposit', 'requestDeposit')->name('request');
        Route::post('reject', 'reject')->name('reject');
        Route::post('approve/{id}', 'approve')->name('approve');
        Route::post('refund/{id}', 'depositRefund')->name('refund');
        Route::post('agent-change-for-deposit', 'agentChangeForDeposit')->name('agent.change.for.deposit');
        Route::post('deposit-adjustment/{id}', 'depositAdjustment')->name('adjustment');
    });

    // WITHDRAW SYSTEM
    Route::name('withdraw.')->prefix('withdraw')->group(function () {
        Route::controller('WithdrawalController')->group(function () {
            Route::get('withdraw/commission', 'withdrawCommission')->name('commission');
            Route::get('pending', 'pending')->name('pending');
            Route::get('approved', 'approved')->name('approved');
            Route::get('rejected', 'rejected')->name('rejected');
            Route::get('log', 'log')->name('log');
            Route::get('details/{id}', 'details')->name('details');
            Route::post('approve', 'approve')->name('approve');
            Route::post('reject', 'reject')->name('reject');
            Route::get('agent-assign/{id}', 'agentAssignedWithDrawl')->name('agent.assign');
        });

        // Withdraw Method
        Route::controller('WithdrawMethodController')->prefix('method')->name('method.')->group(function () {
            Route::get('/', 'methods')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('create', 'store')->name('store');
            Route::get('edit/{id}', 'edit')->name('edit');
            Route::post('edit/{id}', 'update')->name('update');
            Route::post('status/{id}', 'status')->name('status');
        });
    });

    // Report
    Route::controller('ReportController')->prefix('report')->name('report.')->group(function () {
        Route::get('transaction', 'transaction')->name('transaction');
        Route::get('login/history', 'loginHistory')->name('login.history');
        Route::get('login/ipHistory/{ip}', 'loginIpHistory')->name('login.ipHistory');
        Route::get('notification/history', 'notificationHistory')->name('notification.history');
        Route::get('email/detail/{id}', 'emailDetails')->name('email.details');
        Route::get('abc', 'referralCommissions')->name('referral.commissions');
    });

    // Admin Support
    Route::controller('SupportTicketController')->prefix('ticket')->name('ticket.')->group(function () {
        Route::get('/', 'tickets')->name('index');
        Route::get('pending', 'pendingTicket')->name('pending');
        Route::get('closed', 'closedTicket')->name('closed');
        Route::get('answered', 'answeredTicket')->name('answered');
        Route::get('view/{id}', 'ticketReply')->name('view');
        Route::post('reply/{id}', 'replyTicket')->name('reply');
        Route::post('close/{id}', 'closeTicket')->name('close');
        Route::get('download/{ticket}', 'ticketDownload')->name('download');
        Route::post('delete/{id}', 'ticketDelete')->name('delete');
    });

    // Language Manager
    Route::controller('LanguageController')->prefix('language')->name('language.')->group(function () {
        Route::get('/', 'langManage')->name('manage');
        Route::post('/', 'langStore')->name('manage.store');
        Route::post('delete/{id}', 'langDelete')->name('manage.delete');
        Route::post('update/{id}', 'langUpdate')->name('manage.update');
        Route::get('edit/{id}', 'langEdit')->name('key');
        Route::post('import', 'langImport')->name('import.lang');
        Route::post('store/key/{id}', 'storeLanguageJson')->name('store.key');
        Route::post('delete/key/{id}', 'deleteLanguageJson')->name('delete.key');
        Route::post('update/key/{id}', 'updateLanguageJson')->name('update.key');
        Route::get('get-keys', 'getKeys')->name('get.key');
    });

    Route::controller('GeneralSettingController')->group(function () {
        // General Setting
        Route::get('general-setting', 'index')->name('setting.index');
        Route::post('general-setting', 'update')->name('setting.update');

        //configuration
        Route::get('setting/system-configuration', 'systemConfiguration')->name('setting.system.configuration');
        Route::post('setting/system-configuration', 'systemConfigurationSubmit');

        // Logo-Icon
        Route::get('setting/logo-icon', 'logoIcon')->name('setting.logo.icon');
        Route::post('setting/logo-icon', 'logoIconUpdate')->name('setting.logo.icon');

        //Custom CSS
        Route::get('custom-css', 'customCss')->name('setting.custom.css');
        Route::post('custom-css', 'customCssSubmit');

        //Cookie
        Route::get('cookie', 'cookie')->name('setting.cookie');
        Route::post('cookie', 'cookieSubmit');

        //maintenance_mode
        Route::get('maintenance-mode', 'maintenanceMode')->name('maintenance.mode');
        Route::post('maintenance-mode', 'maintenanceModeSubmit');
    });

    //KYC setting
    Route::controller('KycController')->group(function () {
        Route::get('kyc-setting', 'setting')->name('kyc.setting');
        Route::post('kyc-setting', 'settingUpdate');
    });

    //Notification Setting
    Route::name('setting.notification.')->controller('NotificationController')->prefix('notification')->group(function () {
        //Template Setting
        Route::get('global', 'global')->name('global');
        Route::post('global/update', 'globalUpdate')->name('global.update');
        Route::get('templates', 'templates')->name('templates');
        Route::get('template/edit/{id}', 'templateEdit')->name('template.edit');
        Route::post('template/update/{id}', 'templateUpdate')->name('template.update');

        //Email Setting
        Route::get('email/setting', 'emailSetting')->name('email');
        Route::post('email/setting', 'emailSettingUpdate');
        Route::post('email/test', 'emailTest')->name('email.test');

        //SMS Setting
        Route::get('sms/setting', 'smsSetting')->name('sms');
        Route::post('sms/setting', 'smsSettingUpdate');
        Route::post('sms/test', 'smsTest')->name('sms.test');
    });

    // Plugin
    Route::controller('ExtensionController')->prefix('extensions')->name('extensions.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('status/{id}', 'status')->name('status');
    });
    
    // Odds API
    Route::controller('ExtensionController')->prefix('odds-api')->name('odds.')->group(function(){
       Route::get('/fetch', 'fetchOdds')->name('fetch'); 
    });

    //System Information
    Route::controller('SystemController')->name('system.')->prefix('system')->group(function () {
        Route::get('info', 'systemInfo')->name('info');
        Route::get('server-info', 'systemServerInfo')->name('server.info');
        Route::get('optimize', 'optimize')->name('optimize');
        Route::get('optimize-clear', 'optimizeClear')->name('optimize.clear');
        Route::get('system-update', 'systemUpdate')->name('update');
        Route::post('update-upload', 'updateUpload')->name('update.upload');
    });

    // SEO
    Route::get('seo', 'FrontendController@seoEdit')->name('seo');

    // Frontend
    Route::name('frontend.')->prefix('frontend')->group(function () {
        Route::controller('FrontendController')->group(function () {
            Route::get('templates', 'templates')->name('templates');
            Route::post('templates', 'templatesActive')->name('templates.active');
            Route::get('frontend-sections/{key}', 'frontendSections')->name('sections');
            Route::post('frontend-content/{key}', 'frontendContent')->name('sections.content');
            Route::get('frontend-element/{key}/{id?}', 'frontendElement')->name('sections.element');
            Route::post('remove/{id}', 'remove')->name('remove');
        });
    });
    
    
    // Goal Serve API
    Route::name('goal.')->prefix('goal')->controller('GoalServeController')->group(function(){
       Route::get('category', 'categoryList')->name('category'); 
       Route::get('category/{id}/team', 'teamsList')->name('category.teams'); 
       Route::get('category/{id}', 'subCategoryList')->name('sub.category'); 
       Route::get('league-import', 'subCategoryImport')->name('league.import'); 
       Route::get('league-image-import/{id}', 'logoUpdate')->name('league.image'); 
       Route::get('game-import/{id}', 'gameImport')->name('game.import'); 
       Route::get('teams-image-import/{id}', 'teamImageImport')->name('game.teams.logo'); 
       Route::get('team-image-uplaod', 'teamImageUploadPage')->name('team.image.upload.page'); 
       Route::post('team-image-uplaod', 'teamImageUpload')->name('team.image.upload'); 
 
       
       
       // Response Routes
       Route::get('game-response/{id}/{league}', 'gameResponse')->name('game.response'); 
       Route::get('category-response', 'categoryResponse')->name('category.response'); 
       
       
       // Websocket
       Route::get('ws', 'getToken')->name('ws.token'); 
       Route::get('daa', 'daa'); 
    });
    

    // Domain Controller API
    Route::name('domain.')->prefix('domain')->controller('DomainController')->group(function(){
       Route::get('/', 'index')->name('list'); 
       Route::get('/create', 'create')->name('create'); 
       Route::post('/store', 'store')->name('store'); 
       Route::get('/edit/{id}', 'edit')->name('edit'); 
       Route::put('/update/{id}', 'update')->name('update'); 
       Route::delete('/delete/{id}', 'destroy')->name('delete'); 
    });
    
    
    
    
    
    
    
});
