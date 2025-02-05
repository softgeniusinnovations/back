<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

class AffiliateWithdrawalPermission
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        
        return $next($request);

        
    }
}
