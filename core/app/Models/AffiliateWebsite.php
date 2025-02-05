<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class AffiliateWebsite extends Model
{
    use HasFactory;
    protected $table = 'affiliate_website';

    protected $with = ['affiliate','affiliatePromos'];

    public function affiliate()
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }
    public function affiliatePromos()
    {
        return $this->hasMany(AffiliatePromos::class, 'affliate_user_id', 'affiliate_id');
    }
}
