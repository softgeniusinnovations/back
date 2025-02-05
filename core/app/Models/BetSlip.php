<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class BetSlip extends Model {
    use GlobalStatus;

    public function option() {
        return $this->belongsTo(Option::class);
    }
    // Scope
    public function scopeSingleBet($query) {
        return $query->where('type', Status::SINGLE_BET);
    }

    public function scopeMultiBets($query) {
        return $query->where('type', Status::MULTI_BET);
    }

    public function scopeRelationalData($query) {
        $query->withWhereHas('option', function ($option) {
            $option->withWhereHas('question', function ($question) {
                $question->withWhereHas('game', function ($game) {
                    $game->running()->with(['teamOne', 'teamTwo', 'league' => function ($league) {
                        $league->with('category');
                    }]);
                });
            });
        });
    }
}
