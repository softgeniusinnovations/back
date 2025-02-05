<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TransectionProviders extends Model
{
    use HasFactory;

    // Agents

    public function agents()
    {
        return $this->belongsToMany(
            Admin::class,
            'admin_transection_providers',
            'transection_provider_id',
            'admin_id'
        )->where('country_code', Auth::user()->country_code)->select('name', 'country_code', 'address', 'type', 'phone')->inRandomOrder();
    }
    
    // call for deposit pending, approve and regected in deposit details 
    public function agentFounds()
    {
        return $this->belongsToMany(
            Admin::class,
            'admin_transection_providers',
            'transection_provider_id',
            'admin_id'
        );
    }
}
