<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Category extends Model {
    use Searchable, GlobalStatus;

    protected $with =['leagues'];
    
    public function leagues() {
        return $this->hasMany(League::class);
    }

    public function teams() {
        return $this->hasMany(Team::class);
    }

    public function games() {
        return $this->hasManyThrough(Game::class, League::class);
    }

    public static function getGames($type) {
        return self::active()
            ->withCount(['games' => function ($game) use ($type) {
                $game->where('games.status', Status::ENABLE)->$type()->hasActiveLeague()->hasActiveCategory();
            }])
            ->orderBy('name')
            ->get();

    }
}
