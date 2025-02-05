<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordRequest extends Model {
   protected $table ="password_requests";
   protected $fillable = ['email', 'user_id', 'status', 'is_mail_send'];


   // relationship with admin
   public function agents() {
    return $this->belongsTo(Admin::class, 'user_id');
   }
}
