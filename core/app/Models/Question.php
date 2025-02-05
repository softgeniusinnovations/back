<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Question extends Model {
    use Searchable, GlobalStatus;

    public function game() {
        return $this->belongsTo(Game::class);
    }
    public function upcomingGame() {
        return $this->belongsTo(Game::class)->where('bet_start_time', '>=', now())->count();
    }

    public function options() {
        return $this->hasMany(Option::class);
    }
    public function winOption() {
        return $this->belongsTo(Option::class);
    }

    public function betDetails() {
        return $this->hasMany(BetDetail::class, 'question_id');
    }

    public function scopeResultDeclared($query) {
        return $query->where('result', Status::DECLARED);
    }

    public function scopeResultUndeclared($query) {
        return $query->where('result', Status::UNDECLARED);
    }

    public function scopeLocked($query) {
        return $query->where('locked', Status::QUESTION_LOCKED);
    }

    public function scopeUnLocked($query) {
        return $query->where('locked', Status::QUESTION_UNLOCKED);
    }

    public function scopeAmountRefunded($query) {
        return $query->where('amount_refunded', Status::YES);
    }

    public function scopeQuestionAvailable($query) {
        $query->unLocked()->resultUndeclared()->with('options.bets', 'betDetails.bet')->withWhereHas('game', function ($game) {
            $game->expired()->with([
                'teamOne',
                'teamTwo',
                'league' => function ($league) {
                    $league->active()->with(['category' => function ($category) {
                        $category->active();
                    }]);
                },
            ]);
        });
    }
}
