<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\Searchable;
use App\Traits\UserNotify;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use App\Models\Bet;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
class User extends Authenticatable
{
    use Searchable, UserNotify, HasApiTokens;
    use LogsActivity;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'ver_code', 'balance', 'kyc_data',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'User';

    protected $guarded  = [];
    // protected $with = ['currency'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'address'           => 'object',
        'kyc_data'          => 'object',
        'ver_code_send_at'  => 'datetime',
    ];

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
                $roleName = $this->getRoleName((integer) auth()->user()->type);
                $name=auth()->user()->name;
                return "User has been {$eventName} by {$name} with role: {$roleName}";
            });
    }

    protected function getRoleName(int $roleId): string
    {
        $roleName = DB::table('roles')
            ->where('id', $roleId)
            ->value('name');

        return $roleName ?? 'Unknown Role';
    }

    public function loginLogs()
    {
        return $this->hasMany(UserLogin::class);
    }
    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }
    public function loginLogsIp()
    {
        return $this->hasOne(UserLogin::class)->latest();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->orderBy('id', 'desc');
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class)->where('status', '!=', Status::PAYMENT_INITIATE);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class)->where('status', '!=', Status::PAYMENT_INITIATE);
    }

    public function refBy()
    {
        return $this->belongsTo(User::class, 'ref_by');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'ref_by');
    }

    public function allReferrals()
    {
        return $this->referrals()->with('refBy');
    }

    public function commissions()
    {
        return $this->hasMany(CommissionLog::class, 'to_id')->orderBy('id', 'desc');
    }

    // Attribute

    public function fullname(): Attribute
    {
        return new Attribute(
            get: fn () => $this->firstname . ' ' . $this->lastname,
        );
    }

    //Relationship with bet
    public function bets(){
        return $this->hasMany(Bet::class, 'user_id', 'id');
    }

    // SCOPES
    public function scopeActive($query)
    {
        return $query->where('status', Status::USER_ACTIVE)->where('ev', Status::VERIFIED)->where('sv', Status::VERIFIED);
    }

    public function scopeBanned($query)
    {
        return $query->where('status', Status::USER_BAN);
    }

    public function scopeEmailUnverified($query)
    {
        return $query->where('ev', Status::NO)->orWhere(function($query) {
                  $query->where('is_one_click_user', Status::YES)
                        ->where('oev', Status::NO);
              });
    }

    public function scopeMobileUnverified($query)
    {
        return $query->where('sv', Status::NO)->orWhere(function($query) {
                  $query->where('is_one_click_user', Status::YES)
                        ->where('omv', Status::NO);
              });
    }

    public function scopeKycUnverified($query)
    {
        return $query->where('kv', Status::KYC_UNVERIFIED);
    }

    public function scopeKycPending($query)
    {
        return $query->where('kv', Status::KYC_PENDING);
    }

    public function scopeEmailVerified($query)
    {
        return $query->where('ev', Status::VERIFIED);
    }

    public function scopeMobileVerified($query)
    {
        return $query->where('sv', Status::VERIFIED);
    }

    public function scopeWithBalance($query)
    {
        return $query->where('balance', '>', 0);
    }
}
