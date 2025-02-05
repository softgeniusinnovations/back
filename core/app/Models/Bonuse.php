<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonuse extends Model
{
    use HasFactory;
    protected $table = "userbonuses";
    protected $with = ['user', 'deposit', 'event'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deposit()
    {
        return $this->belongsTo(Deposit::class, 'deposit_id');
    }

    public function event()
    {
        return $this->belongsTo(News::class, 'news_id');
    }




}
