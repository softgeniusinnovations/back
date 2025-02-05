<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class HomepageGame extends Model {

    use Searchable, GlobalStatus;
    protected $table='homepage_games';
    protected $guarded=[];
    public $timestamps=false;

}
