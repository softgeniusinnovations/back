<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model {

    use Searchable, GlobalStatus;
    // protected $fillable = ['status'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function bets() {
        return $this->hasMany(BetDetail::class);
    }

    public function betTypeBadge(): Attribute {
        return new Attribute(function () {
            $html = '';

            if ($this->type == Status::SINGLE_BET) {
                $html = '<span class="badge badge--success">' . trans('Single') . '</span>';
            } else {
                $html = '<span><span class="badge badge--primary">' . trans('Multi') . '</span></span>';
            }

            return $html;
        });
    }

    // Scope
    public function scopeSingleBet($query) {
        return $query->where('type', Status::SINGLE_BET);
    }
    public function scopeWin($query) {
        return $query->where('status', Status::BET_WIN);
    }
    public function scopeLose($query) {
        return $query->where('status', Status::BET_LOSE);
    }

    public function scopeRefunded($query) {
        return $query->where('status', Status::BET_REFUNDED);
    }

    public function scopeMultiBets($query) {
        return $query->where('type', Status::MULTI_BET);
    }

    public function scopeAmountReturnable($query) {
        return $query->where('amount_returned', Status::YES);
    }
}
