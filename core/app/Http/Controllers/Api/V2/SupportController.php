<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{

    public function openSupportTicket()
    {
        $user = Auth::user();
        return response()->json(['message' => 'openSupportTicket']);
    }
}
