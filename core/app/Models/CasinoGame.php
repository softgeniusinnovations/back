<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CasinoGame extends Model
{
    use HasFactory;
    protected $table='casino_games';
    Protected $guarded=[];
    public $timestamps = false;
}
