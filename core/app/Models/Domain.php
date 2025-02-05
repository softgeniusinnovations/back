<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model {
   protected $table ="domains";
    // Add 'domain_name' to the fillable array
    protected $fillable = [
        'domain_name', // Add this line
        'logo',
        'contents',
        'status',
    ];

    // Optional: Cast 'contents' to an array
    protected $casts = [
        'contents' => 'array',
    ];
}
