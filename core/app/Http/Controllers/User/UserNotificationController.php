<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserNotification;
class UserNotificationController extends Controller
{
   public function notificationDetails(Request $request){
       $data = UserNotification::findOrFail($request->query('id'));
       $data->is_read = 1;
       if($data->save()){
           return redirect()->to($data->url);
       }else{
           $notify[] = ['error', 'Something went wrong!'];
           return back()->withNotify($notify);
       }
   }
}
