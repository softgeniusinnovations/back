<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TramcardUser extends Model
{
    use HasFactory;

    
    public function tramcard(){
        return $this->hasOne(Tramcard::class, 'id','tramcard_id');
    }
}
