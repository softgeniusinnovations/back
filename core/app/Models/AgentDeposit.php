<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentDeposit extends Model
{
    use HasFactory;
    protected $fillable = ['status'];

    public function agent()
    {
        return $this->belongsTo(Admin::class, 'agent_id', 'id');
    }

    public function wallet()
    {
        return $this->belongsTo(CriptoWallet::class, 'wallet_id', 'id');
    }
}
