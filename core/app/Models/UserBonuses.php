<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBonuses extends Model
{
    use HasFactory;
    protected $table = 'userbonuses';

    protected $with = ['user', 'news'];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function news(){
        return $this->belongsTo(News::class, 'news_id');
    }
}
