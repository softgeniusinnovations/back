<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasswordRequest;
use App\Models\Admin;
use Illuminate\Http\Request;

class PasswordRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pageTitle = 'Paword Change Request';
        $data = PasswordRequest::with('agents')->where('status', 0)->orWhere('is_mail_send', 0)->paginate(getPaginate(10));
        return view('admin.agent.password-request', compact('pageTitle', 'data'));
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pageTitle = 'Change Password';
        $user = PasswordRequest::with('agents')->findOrFail($id);
        return view('admin.agent.password-reset', compact('pageTitle', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
       $passwordRequest = PasswordRequest::with('agents')->findOrFail($id);
       $request->validate([
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required_with:new_password|same:new_password',
        ]);
        $user = $passwordRequest->agents;
        $user->password = bcrypt($request->new_password); 
        $user->save();

        $passwordRequest->status = 2; 
        $passwordRequest->save();

        notify($user, 'DEFAULT', [
            'subject' => 'Password Changed',
            'message' => 'Your password has been successfully updated. Your new password is: ' . $request->new_password
        ]);

        $passwordRequest->is_mail_send = 1;
        $passwordRequest->save();

        $notify[] = ['success', 'Password has been successfully changed'];
        return to_route('admin.agent.password.request.list')->withNotify($notify);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

}
