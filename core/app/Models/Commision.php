<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commision extends Model
{
    use HasFactory;
    protected $fillable = ['amount', 'final_amount'];

    public function agent()
    {
        return $this->belongsTo(Admin::class, 'agent_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function deposit()
    {
        return $this->belongsTo(Deposit::class, 'deposit_id', 'id');
    }

    public function withdrawl()
    {
        return $this->belongsTo(Withdrawal::class, 'withdraw_id', 'id');
    }
}
