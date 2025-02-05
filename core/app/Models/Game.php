<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Game extends Model {
    use Searchable, GlobalStatus;

    protected $with = ['league', 'questions', 'teamOne', 'teamTwo'];
    public function league() {
        return $this->belongsTo(League::class);
    }

    public function questions() {
        return $this->hasMany(Question::class);
    }

    public function teamOne() {
        return $this->belongsTo(Team::class, 'team_one_id');
    }

    public function teamTwo() {
        return $this->belongsTo(Team::class, 'team_two_id');
    }

    // Scopes
    public function scopeRunning($query) {
        return $query->where('bet_start_time', '<=', now())->where('bet_end_time', '>=', now());
    }

    public function scopeUpcoming($query) {
        return $query->where('bet_start_time', '>=', now());
    }

    public function scopeExpired($query) {
        return $query->where('bet_end_time', '<', now());
    }

    public function getIsUpcomingAttribute() {
        return now()->lt($this->bet_start_time);
    }

    public function getIsRunningAttribute() {
        return now()->gte($this->bet_start_time) && now()->lte($this->bet_end_time);
    }

    public function getIsExpiredAttribute() {
        return now()->gt($this->bet_end_time);
    }

    public function scopeHasActiveCategory($query) {
        return $query->whereHas('league.category', function ($category) {
            $category->active();
        });
    }

    public function scopeHasActiveLeague($query) {
        return $query->whereHas('league', function ($league) {
            $league->active();
        });
    }

    public function scopeDateTimeFilter($query, $column) {
        if (!request()->$column) {
            return $query;
        }

        try {
            $date      = explode('-', request()->$column);
            $startDate = Carbon::parse(trim($date[0]))->format('Y-m-d');
            $endDate = @$date[1] ? Carbon::parse(trim(@$date[1]))->format('Y-m-d') : $startDate;
        } catch (\Exception $e) {
            throw ValidationException::withMessages(['error' => 'Invalid date provided']);
        }

        return  $query->whereDate($column, '>=', $startDate)->whereDate($column, '<=', $endDate);
    }
}
