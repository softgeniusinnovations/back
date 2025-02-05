<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class BetDetail extends Model {
    protected $fillable = ['status'];

    use GlobalStatus;

    public function bet() {
        return $this->belongsTo(Bet::class);
    }

    public function question() {
        return $this->belongsTo(Question::class);
    }

    public function option() {
        return $this->belongsTo(Option::class);
    }

    public function betData() {
        return $this->hasOne(Bet::class);
    }

    // Scope
    public function scopeHasSingleBet($query) {
        return $query->whereHas('bet', function ($q) {
            $q->where('type', Status::SINGLE_BET)->pending();
        });
    }

    public function scopeHasMultiBet($query) {
        return $query->whereHas('bet', function ($q) {
            $q->where('type', Status::MULTI_BET)->pending();
        });
    }

    public function scopePending($query) {
        return $query->where('status', Status::BET_PENDING);
    }

    public function scopeRelationalData($query) {
        $query->with([
            'option' => function ($option) {
                $option->active()->with([
                    'question' => function ($question) {
                        $question->active()->with([
                            'game' => function ($game) {
                                $game->active()->with([
                                    'teamOne',
                                    'teamTwo',
                                    'league' => function ($league) {
                                        $league->active()->with('category');
                                    },
                                ]);
                            },
                        ]);
                    },
                ]);
            },
        ]);
    }
}
