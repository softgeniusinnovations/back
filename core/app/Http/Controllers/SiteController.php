<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Category;
use App\Models\Frontend;
use App\Models\Game;
use App\Models\Extension;
use App\Models\Language;
use App\Models\League;
use App\Models\ClickLog;
use App\Models\Option;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\Website;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class SiteController extends Controller {

    public function __construct() {
        parent::__construct();
    }
    
    public function demoHome($categorySlug = null, $leagueSlug = null){
        
        $reference = @$_GET['reference'];
        $aff_link = @$_GET['affi_link'];
        $linkId = @$_GET['ad'];
        if ($reference) {
            session()->put('reference', $reference);
            return redirect(route('user.register'));
        } else if ($aff_link) {
            session()->put('promo', $aff_link);
            session()->put('linkId', $linkId);
            $data = new ClickLog();
            $data->promoCode = $aff_link;
            $data->save();

            return redirect(route('user.register'));
        }

        $pageTitle  = 'Home';
        $gameType   = session('game_type', 'running');

        $games      = Game::with(['teamOne', 'teamTwo'])->active()->$gameType();
        $categories = Category::getGames($gameType);

        if ($categorySlug) {
            $activeCategory = $categories->where('slug', $categorySlug)->first();
        } else {
            $activeCategory = $categories->where('games_count', $categories->max('games_count'))->first();
        }

        $leagues      = [];
        $activeLeague = null;

        if ($leagueSlug) {
            $activeLeague = League::where('slug', $leagueSlug)->active()->whereHas('category', function ($q) {
                $q->active();
            })->firstOrFail();

            $activeCategory = $activeLeague->category;
        }

        if ($activeCategory && $activeCategory->leagues->count()) {
            $leagues = $this->filterByLeagues($activeCategory, $gameType);
            if (!$leagueSlug) {
                $activeLeague = $leagues->first();
            }
        }

        $games = $games->where('league_id', @$activeLeague->id)->with(['teamOne', 'teamTwo'])->with(['questions' => function ($q) {
            $q->active()
                ->resultUndeclared()->select('id', 'game_id', 'title', 'locked')
                ->withCount('betDetails')
                ->with('options', function ($option) {
                    $option->active();
                });
        }])->orderBy('id', 'desc')->get();
        
        
        $liveGames =  Game::active()->running()->with(['teamOne', 'teamTwo'])->with(['questions' => function ($q) {
            $q->active()
                ->resultUndeclared()->select('id', 'game_id', 'title', 'locked')
                ->withCount('betDetails')
                ->with('options', function ($option) {
                    $option->active();
                });
        }])->orderBy('id', 'desc')->limit(60)->get();
        
        $upcommingGames =  Game::active()->upcoming()->with(['teamOne', 'teamTwo'])->with(['questions' => function ($q) {
            $q->active()
                ->resultUndeclared()->select('id', 'game_id', 'title', 'locked')
                ->withCount('betDetails')
                ->with('options', function ($option) {
                    $option->active();
                });
        }])->orderBy('id', 'desc')->limit(60)->get();
        
        
        
         return view($this->activeTemplate . 'demo_home', compact('pageTitle', 'categories', 'leagues', 'games', 'activeCategory', 'activeLeague','liveGames', 'upcommingGames'));

    }

    public function index($categorySlug = null, $leagueSlug = null) {
        $reference = @$_GET['reference'];
        if ($reference) {
            session()->put('reference', $reference);
        }

        $pageTitle  = 'Home';
        $gameType   = session('game_type', 'running');

        $games      = Game::active()->$gameType();
        $categories = Category::getGames($gameType);

        if ($categorySlug) {
            $activeCategory = $categories->where('slug', $categorySlug)->first();
        } else {
            $activeCategory = $categories->where('games_count', $categories->max('games_count'))->first();
        }

        $leagues      = [];
        $activeLeague = null;

        if ($leagueSlug) {
            $activeLeague = League::where('slug', $leagueSlug)->active()->whereHas('category', function ($q) {
                $q->active();
            })->firstOrFail();

            $activeCategory = $activeLeague->category;
        }

        if ($activeCategory && $activeCategory->leagues->count()) {
            $leagues = $this->filterByLeagues($activeCategory, $gameType);
            if (!$leagueSlug) {
                $activeLeague = $leagues->first();
            }
        }

        $games = $games->where('league_id', @$activeLeague->id)->with(['teamOne', 'teamTwo'])->with(['questions' => function ($q) {
            $q->active()
                ->resultUndeclared()->select('id', 'game_id', 'title', 'locked')
                ->withCount('betDetails')
                ->with('options', function ($option) {
                    $option->active();
                });
        }])->orderBy('id', 'desc')->get();

        return view($this->activeTemplate . 'home', compact('pageTitle', 'categories', 'leagues', 'games', 'activeCategory', 'activeLeague'));
    }

    public function gamesByLeague($slug) {
        return $this->index(leagueSlug: $slug);
    }
    public function gamesByCategory($slug) {
        return $this->index(categorySlug: $slug);
    }

    public function switchType($type) {
        $url = url()->previous() ?? '/';
        session()->put('game_type', $type == 'live' ? 'running' : 'upcoming');
        return redirect($url);
    }

    public function oddsType($type) {
        session()->put('odds_type', $type);
        return to_route('home');
    }

    public function markets($gameSlug) {
        $gameType = session()->get('game_type', 'running');

        $game     = Game::active()->$gameType()->where('slug', $gameSlug)->hasActiveCategory()->hasActiveLeague()
            ->with([
                'league',
                'questions'         => function ($question) {
                    $question->active()->limit(request()->more)->orderBy('id', 'desc')->resultUndeclared();
                },
                'questions.options' => function ($option) {
                    $option->active();
                },
            ])->firstOrFail();

        $categories     = Category::getGames($gameType);
        $activeCategory = $game->league->category;
        $activeLeague   = $game->league;
        $leagues        = $this->filterByLeagues($activeCategory, $gameType);
        $pageTitle      = "$game->slug - odds";
        return view($this->activeTemplate . 'markets', compact('pageTitle', 'categories', 'leagues', 'game', 'activeCategory', 'activeLeague'));
    }
    
    // Fetch game scores 
    public function fetchGameScores(Request $r){
        $game = Game::where('game_id', $r->game)->running()->with('league')->first();
        if($game){
             $extension = Extension::where('act', 'odds')->where('status', 1)->first();
             if($extension){
                 $shortcode = json_decode(json_encode($extension->shortcode), true);
                 $apiKey = $shortcode['app_key']['value'];
                 if($apiKey){
                     $responseData = [];
                     $host = 'https://api.the-odds-api.com/v4/sports/';
                     $url = $host.$game->league->slug.'/scores?apiKey='.$apiKey.'&eventIds='.$game->game_id;
                    // return $url;
                     try{
                            $client = new \GuzzleHttp\Client();
                            $response = $client->request('GET', $url);
                            $responseData = $response->getBody();
                            $data = json_decode($responseData, true);
                        
                     } catch(\Exception $e){
                            $data = json_encode(['error' => 'Something went wrong']);
                     }
                     return $data;
                 }
             }
             
        }
        
         
    }
    
    public function getOdds($id) {
        $options = Option::query();
        if (session('game_type') == 'running') {
            $options->availableForBet();
        }
        $options = $options->where('question_id', $id)->with('question')->get();
        return view($this->activeTemplate . 'partials.odds_by_question', compact('options'));
    }

    private function filterByLeagues($activeCategory, $gameType) {
        $leagues = $activeCategory->leagues();
        $gameType .= 'Game';
        return $leagues->withCount("$gameType as game_count")->orderBy('game_count', 'desc')->active()->get();
    }

    public function contact() {
        $pageTitle = "Contact Us";
        $user      = auth()->user();
        return view($this->activeTemplate . 'contact', compact('pageTitle', 'user'));
    }

    public function blog() {
        $pageTitle = "News and Updates";
        $content   = getContent('blog.content', true);
        $blogs     = Frontend::where('data_keys', 'blog.element')->orderBy('id', 'desc')->paginate(getPaginate());
        return view($this->activeTemplate . 'blog', compact('pageTitle', 'blogs', 'content'));
    }

    public function blogDetails($slug, $id) {
        $blog                              = Frontend::where('id', $id)->where('data_keys', 'blog.element')->firstOrFail();
        $pageTitle                         = 'Read Full News';
        $latestBlogs                       = Frontend::where('id', '!=', $id)->where('data_keys', 'blog.element')->orderBy('id', 'desc')->limit(10)->get();
        $customPageTitle                   = $blog->data_values->title;
        $seoContents['keywords']           = $blog->meta_keywords ?? [];
        $seoContents['social_title']       = $blog->data_values->title;
        $seoContents['description']        = strLimit(strip_tags($blog->data_values->description), 150);
        $seoContents['social_description'] = strLimit(strip_tags($blog->data_values->description), 150);
        $seoContents['image']              = getImage('assets/images/frontend/blog/' . @$blog->data_values->image, '830x500');
        $seoContents['image_size']         = '830x500';
        return view($this->activeTemplate . 'blog_details', compact('blog', 'pageTitle', 'customPageTitle', 'latestBlogs', 'seoContents'));
    }

    public function contactSubmit(Request $request) {
        $this->validate($request, [
            'name'    => 'required',
            'email'   => 'required',
            'subject' => 'required|string|max:255',
            'message' => 'required',
        ]);

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $request->session()->regenerateToken();

        $random = getNumber();

        $ticket           = new SupportTicket();
        $ticket->user_id  = auth()->id() ?? 0;
        $ticket->name     = $request->name;
        $ticket->email    = $request->email;
        $ticket->priority = Status::PRIORITY_MEDIUM;

        $ticket->ticket     = $random;
        $ticket->subject    = $request->subject;
        $ticket->last_reply = Carbon::now();
        $ticket->status     = Status::TICKET_OPEN;
        $ticket->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = auth()->user() ? auth()->user()->id : 0;
        $adminNotification->title     = 'A new contact message has been submitted';
        $adminNotification->click_url = urlPath('admin.ticket.view', $ticket->id);
        $adminNotification->save();

        $message                    = new SupportMessage();
        $message->support_ticket_id = $ticket->id;
        $message->message           = $request->message;
        $message->save();

        $notify[] = ['success', 'Ticket created successfully!'];

        return to_route('ticket.view', [$ticket->ticket])->withNotify($notify);
    }

    public function policyPages($slug, $id) {
        $policy    = Frontend::where('id', $id)->where('data_keys', 'policy_pages.element')->firstOrFail();
        $pageTitle = $policy->data_values->title;

        return view($this->activeTemplate . 'policy', compact('policy', 'pageTitle'));
    }

    public function changeLanguage($lang = null) {
        $language = Language::where('code', $lang)->first();
        if (!$language) {
            $lang = 'en';
        }

        session()->put('lang', $lang);
        return back();
    }

    public function cookieAccept() {
        Cookie::queue('gdpr_cookie', gs('site_name'), 43200);
    }

    public function cookiePolicy() {
        $pageTitle = 'Cookie Policy';
        $cookie    = Frontend::where('data_keys', 'cookie.data')->first();

        return view($this->activeTemplate . 'cookie', compact('pageTitle', 'cookie'));
    }

    public function placeholderImage($size = null) {
        $imgWidth  = explode('x', $size)[0];
        $imgHeight = explode('x', $size)[1];
        $text      = $imgWidth . '×' . $imgHeight;
        $fontFile  = realpath('assets/font/RobotoMono-Regular.ttf');
        $fontSize  = round(($imgWidth - 50) / 8);
        if ($fontSize <= 9) {
            $fontSize = 9;
        }
        if ($imgHeight < 100 && $fontSize > 30) {
            $fontSize = 30;
        }

        $image     = imagecreatetruecolor($imgWidth, $imgHeight);
        $colorFill = imagecolorallocate($image, 100, 100, 100);
        $bgFill    = imagecolorallocate($image, 175, 175, 175);
        imagefill($image, 0, 0, $bgFill);
        $textBox    = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth  = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $textX      = ($imgWidth - $textWidth) / 2;
        $textY      = ($imgHeight + $textHeight) / 2;
        header('Content-Type: image/jpeg');
        imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
        imagejpeg($image);
        imagedestroy($image);
    }

    public function maintenance() {
        $pageTitle = 'Maintenance Mode';
        if (gs('maintenance_mode') == Status::DISABLE) {
            return to_route('home');
        }
        $maintenance = Frontend::where('data_keys', 'maintenance.data')->first();
        return view($this->activeTemplate . 'maintenance', compact('pageTitle', 'maintenance'));
    }
}
