<?php

namespace App\Providers;

use App\Models\News;
use URL;
use App\Models\Bet;
use App\Models\Game;
use App\Models\User;
use App\Models\Deposit;
use App\Models\Frontend;
use App\Constants\Status;
use App\Models\Withdrawal;
use App\Models\UserBonusList;
use App\Models\TramcardUser;
use App\Models\UserNotification;
use App\Models\SupportTicket;
use App\Models\Domain;
use App\Models\AdminNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Observers\GlobalObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        $modelPath = app_path('Models');
        $modelNamespace = 'App\Models';

        if (File::exists($modelPath)) {
            foreach (File::allFiles($modelPath) as $file) {
                $modelClass = $modelNamespace . '\\' . $file->getFilenameWithoutExtension();

                if (class_exists($modelClass) && is_subclass_of($modelClass, Model::class)) {
                    $modelClass::observe(GlobalObserver::class);
                }
            }
        }

//        News::observe(GlobalObserver::class);




        if (!cache()->get('SystemInstalled')) {
            $envFilePath = base_path('.env');
            $envContents = file_get_contents($envFilePath);
            if (empty($envContents)) {
                header('Location: install');
                exit;
            } else {
                cache()->put('SystemInstalled', true);
            }
        }
        

        // pass domains
        $this->app->singleton('domainCheckList', function($app) {
            $domain = Domain::where('status', 1)->where('domain_name', request()->getHost())->first();
            return $domain;
        });
        
        // User notification data show
        $this->app->singleton('userNotificationData', function ($app) {
            if (auth()->check()) {
            return [
                    'userNotificationsData' =>  UserNotification::latest()->where('user_id', auth()->user()->id)->limit(5)->get(),
                    'unreadNotificationCountData' => UserNotification::latest()->where('user_id', auth()->user()->id)->take(1)->where('is_read', 0)->count(),
                 ];
            }else{
                return [
                    'userNotificationsData' => [],
                    'unreadNotificationCountData'=> 0
                ];
            }
        });
        
        // All Balance
        $this->app->singleton('userBalance', function($app){
            if (auth()->check()) {
                $tramcardData = TramcardUser::where('user_id', auth()->user()->id)->first();
                return [
                    'deposit' => auth()->user()->balance > 0 ? auth()->user()->balance : 0,
                    'withdrawal' => auth()->user()->withdrawal > 0 ? auth()->user()->withdrawal : 0,
                    'bonus' => auth()->user()->bonus_account > 0 ? auth()->user()->bonus_account : 0,
                    'tramcard' => $tramcardData ? $tramcardData->amount > 0 ? $tramcardData->amount : 0 : 0,
                    'kyc' => auth()->user()->kv == 1 ? true : false,
                    'is_welcome_message'=> !auth()->user()->is_welcome_message 
                ];
            } else {
                return [
                    'deposit' => 0,
                    'withdrawal' => 0,
                    'bonus' => 0,
                    'tramcard' => 0,
                    'kyc' => false,
                    'is_welcome_message' => false
                ];
            }
        });
        
        
        //Check user active bonus fron user_bonus_lists
        $this->app->singleton('userActiveBonus', function($app) {
            if(auth()->check()){
                $activeBonus = UserBonusList::where('user_id', auth()->user()->id)->first();
                if($activeBonus){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        });
        
        
        

        $general                         = gs();
        $activeTemplate                  = activeTemplate();
        $viewShare['general']            = $general;
        $viewShare['activeTemplate']     = $activeTemplate;
        $viewShare['activeTemplateTrue'] = activeTemplate(true);
        $viewShare['emptyMessage']       = 'Data not found';
        view()->share($viewShare);

        view()->composer('admin.partials.sidenav', function ($view) {
            $view->with([
                'bannedUsersCount'           => User::banned()->count(),
                'emailUnverifiedUsersCount'  => User::emailUnverified()->count(),
                'mobileUnverifiedUsersCount' => User::mobileUnverified()->count(),
                'kycUnverifiedUsersCount'    => User::kycUnverified()->count(),
                'kycPendingUsersCount'       => User::kycPending()->count(),
                'pendingTicketCount'         => SupportTicket::whereIN('status', [Status::TICKET_OPEN, Status::TICKET_REPLY])->count(),
                'pendingDepositsCount'       => Auth::user()->type > 0 ? Deposit::pending()->where('agent_id', Auth::user()->id)->count() : Deposit::pending()->count(),
                'pendingWithdrawCount'       => Auth::user()->type > 0 ? Withdrawal::pending()->where('agent_id', Auth::user()->id)->where('assign_agent', 1)->count() : Withdrawal::pending()->count(),
                'pendingBetCount'            => Bet::pending()->count(),
                'pendingGameCount'            => Game::where('source', 'goalserve')->where('game_end', 0)->where('game_type', 'LIVE')->count(),
                'pendingUpcomingGameCount'            => Game::where('source', 'goalserve')->where('game_end', 0)->where('game_type', 'UPCOMING')->count(),
            ]);
        });

        view()->composer('admin.partials.topnav', function ($view) {
            if(auth()->user()->hasRole('super-admin')){
                $adminNotification = AdminNotification::where('is_read', Status::NO)->with('user')->orderBy('id', 'desc')->take(10)->get();
            }else{
                $adminNotification = AdminNotification::where('is_read', Status::NO)->with('user')->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->take(10)->get();
            }
            
            if(auth()->user()->hasRole('super-admin')){
                $adminNotificationCount = AdminNotification::where('is_read', Status::NO)->count();
            }else{
                $adminNotificationCount = AdminNotification::where('is_read', Status::NO)->where('user_id', auth()->user()->id)->count();
            }
            
            
            $view->with([
                'adminNotifications'     => $adminNotification,
                'adminNotificationCount' => $adminNotificationCount,
            ]);
        });

        view()->composer('partials.seo', function ($view) {
            $seo = Frontend::where('data_keys', 'seo.data')->first();
            $view->with([
                'seo' => $seo ? $seo->data_values : $seo,
            ]);
        });

        if ($general->force_ssl) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFour();
    }
}
