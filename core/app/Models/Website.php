<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;
    protected $table = 'websitelink';
    protected $with = ['user','web'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function web()
    {
        return $this->belongsTo(AffiliateWebsite::class, 'website_link');
    }

}
