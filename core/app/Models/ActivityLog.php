<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
//    protected $fillable = ['model', 'model_id','role_id', 'user_id', 'operation', 'changes', 'ip_address', 'user_agent', 'url', 'performed_at'];
    protected $guarded = [];
    protected $casts = [
        'changes' => 'array',
        'performed_at' => 'datetime',
    ];



    public $timestamps=false;
    protected $trackable = false;


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
