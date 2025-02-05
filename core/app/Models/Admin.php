<?php

namespace App\Models;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\AgentDeposit;
use App\Models\TransectionProviders;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Admin extends Authenticatable implements MustVerifyEmail
{
    use HasRoles, Notifiable;
    use LogsActivity;
    protected $guard = 'admin';
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'Admin';
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    protected $hidden = [
        'password', 'remember_token', 'ver_code',
    ];
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('Bonus')
//            ->withProperties([
//                'role_name' => $this->getRoleName((integer) auth()->user()->type)
//            ])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = auth()->user();
                if($user){
                	$roleName = $this->getRoleName((int) @$user->type); 
		            $name = $user->name;
		            
	                return "Bonus has been {$eventName} by {$name} with role: {$roleName}";
                }
                return "Bonus has been {$eventName} but user not authenticated.";
	            
            });
    }

    protected function getRoleName(int $roleId): string
    {
        $roleName = DB::table('roles')
            ->where('id', $roleId)
            ->value('name');

        return $roleName ?? 'Unknown Role';
    }

    // Email send
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail($this));
    }


    // belongsTOMany with transection Providers
    public function transectionProviders()
    {
        return $this->belongsToMany(
            TransectionProviders::class,
            'admin_transection_providers',
            'admin_id',
            'transection_provider_id'
        )->withPivot('mobile', 'wallet_name', 'status', 'id');
    }
    
    // Self Deposit
    public function agentDeposit(){
        return $this->hasMany(AgentDeposit::class, 'agent_id', 'id');
    }
    
    // Bettor Deposit
    public function deposit($status = null){
        return $this->hasMany(Deposit::class, 'agent_id', 'id')->when($status, function($query) use ($status) {
            return $query->where('status', $status);
        });
    }
    
    // Bettor Withdraw
    public function withdraw($status = null){
        return $this->hasMany(Withdrawal::class, 'agent_id', 'id')->when($status, function($query) use ($status) {
            return $query->where('status', $status);
        });
    }
}
