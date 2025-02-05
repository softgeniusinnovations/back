<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class League extends Model {
    use Searchable, GlobalStatus;

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function games() {
        return $this->hasMany(Game::class);
    }

    public function runningGame() {
        return $this->hasMany(Game::class)->where('bet_end_time', '>', now())->where('bet_start_time', '<', now());
    }
    public function upcomingGame() {
        return $this->hasMany(Game::class)->where('bet_start_time', '>', now());
    }

}
