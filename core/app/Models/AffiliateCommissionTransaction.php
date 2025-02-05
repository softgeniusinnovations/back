<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateCommissionTransaction extends Model
{
    use HasFactory;
    protected $table = 'affiliatecommissiontransaction';

    protected $with = ['better_details', 'affiliate_details', 'promo_details'];

    public function better_details()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function affiliate_details()
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function promo_details()
    {
        return $this->belongsTo(Promotion::class, 'promo_id');
    }
}
