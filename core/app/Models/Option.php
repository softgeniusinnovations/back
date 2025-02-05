<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Option extends Model {
    use GlobalStatus;

    public function question() {
        return $this->belongsTo(Question::class);
    }
    public function bets() {
        return $this->hasMany(BetDetail::class);
    }

    public function scopeLocked($query) {
        return $query->where('locked', Status::OPTION_LOCKED);
    }

    public function scopeUnLocked($query) {
        return $query->where('locked', Status::OPTION_UNLOCKED);
    }

    public function scopeAvailableForBet($query) {
        return $query->active()->unLocked()->whereHas('question', function ($question) {
            $question->active()->unLocked()->resultUndeclared()
                ->whereHas('game', function ($game) {
                    // $game->active()->running()->hasActiveCategory()->hasActiveLeague();
                    $game->active()->hasActiveCategory()->hasActiveLeague();
                });
        });
    }
    public function scopeAvailableForWinner($query) {
        return $query->where('status', Status::ENABLE)->where('winner', 0)->where('losser', 0)->where('refund', 0);
    }
    
    public function scopeAvailableForLosser($query) {
        return $query->where('status', Status::ENABLE)->where('losser', 0)->where('winner', 0)->where('refund', 0);
    }
    
    public function scopeAvailableForRefund($query) {
        return $query->where('status', Status::ENABLE)->where('refund', 0)->where('winner', 0)->where('losser', 0);
    }
}
