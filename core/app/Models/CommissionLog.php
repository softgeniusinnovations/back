<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class CommissionLog extends Model
{
    use Searchable;

    public function user(){
        return $this->belongsTo(User::class,'to_id','id');
    }

    public function byWho(){
        return $this->belongsTo(User::class,'from_id','id');
    }

    public function toUser(){
        return $this->belongsTo(User::class,'to_id','id');
    }
}
