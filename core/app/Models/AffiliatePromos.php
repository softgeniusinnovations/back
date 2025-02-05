<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliatePromos extends Model
{
    use HasFactory;
    protected $table = "affiliatepromos";

    protected $with = ['affiliateUser', 'betterUser', 'promo'];

    public function affiliateUser()
    {
        return $this->belongsTo(User::class, 'affliate_user_id');
    }

    public function betterUser()
    {
        return $this->belongsTo(User::class, 'better_user_id');
    }

    public function promo()
    {
        return $this->belongsTo(Promotion::class, 'promo_id');
    }

    // public function website()
    // {
    //     // return $this->belongsTo(AffiliateWebsite::class, 'affiliate_id', 'affiliate_user_id');
    //     return $this->belongsTo(AffiliateWebsite::class, 'affiliate_id', 'affliate_user_id');
    // }
}
