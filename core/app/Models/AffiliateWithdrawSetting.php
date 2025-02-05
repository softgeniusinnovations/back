<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateWithdrawSetting extends Model
{
    use HasFactory;
    protected $table = 'affiliate_withdraw_setting';
    public $timestamps = false;

//    protected $fillable = ['id', 'withdraw_date'];
    protected $guarded=[];
}
