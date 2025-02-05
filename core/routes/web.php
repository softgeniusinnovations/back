<?php

use App\Http\Controllers\Admin\DeclareOutcomeController;
use Illuminate\Support\Facades\Route;


Route::get('/clear', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});

Route::get('cron/win', 'CronController@win')->name('win.cron');
Route::get('cron/lose', 'CronController@lose')->name('lose.cron');
Route::get('cron/refund', 'CronController@refund')->name('refund.cron');
Route::get('download-pdf', [DeclareOutcomeController::class,'downloadPdf'])->name('download.pdf');

// Goal serve cron



// User Support Ticket
Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group(function () {
    Route::get('/', 'supportTicket')->name('index');
    Route::get('new', 'openSupportTicket')->name('open');
    Route::post('create', 'storeSupportTicket')->name('store');
    Route::get('view/{ticket}', 'viewTicket')->name('view');
    Route::post('reply/{ticket}', 'replyTicket')->name('reply');
    Route::post('close/{ticket}', 'closeTicket')->name('close');
    Route::get('download/{ticket}', 'ticketDownload')->name('download');
});

Route::get('app/deposit/confirm/{hash}', 'Gateway\PaymentController@appDepositConfirm')->name('deposit.app.confirm');

Route::controller('BetSlipController')->prefix('bet')->name('bet.')->group(function () {
    Route::get('add-to-bet-slip', 'addToBetSlip')->name('slip.add');
    Route::post('remove/{id}', 'remove')->name('slip.remove');
    Route::post('remove-all', 'removeAll')->name('slip.remove.all');
    Route::post('update', 'update')->name('slip.update');
    Route::post('update-all', 'updateAll')->name('slip.update.all');
});

Route::controller('SiteController')->group(function () {
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactSubmit');

    Route::get('/news', 'blog')->name('blog');
    Route::get('news/{slug}/{id}', 'blogDetails')->name('blog.details');

    Route::get('/change/{lang?}', 'changeLanguage')->name('lang');
    Route::get('cookie-policy', 'cookiePolicy')->name('cookie.policy');
    Route::get('/cookie/accept', 'cookieAccept')->name('cookie.accept');
    Route::get('policy/{slug}/{id}', 'policyPages')->name('policy.pages');
    Route::get('placeholder-image/{size}', 'placeholderImage')->name('placeholder.image');

    // Games

    Route::get('odds-by-market/{id}', 'getOdds')->name('market.odds');
    Route::get('markets/{gameSlug}', 'markets')->name('game.markets');
    Route::get('league/{slug}', 'gamesByLeague')->name('league.games');
    Route::get('category/{slug}', 'gamesByCategory')->name('category.games');
    Route::get('switch-to/{type}', 'switchType')->name('switch.type');
    Route::get('odds-type/{type}', 'oddsType')->name('odds.type');
    Route::get('/demo', 'index')->name('home.demo');
    Route::get('/sports', 'index')->name('home.sports');
    Route::get('/', 'demoHome')->name('home');
    Route::get('scores', 'fetchGameScores')->name('market.scores');
});

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
Route::get('currency-store', 'CurrencyController@index')->name('currency.store');

Route::get('/events', 'PageController@events')->name('events');
Route::get('/event/{id}', 'PageController@eventDetails')->name('event.details');
Route::get('/live/game', 'PageController@livegame')->name("live.game");
Route::get('/upcomming/game', 'PageController@upcomming')->name("upcomming.game");

//test edit
