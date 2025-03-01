<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function profile()
    {
        $pageTitle = "Profile Setting";
        $user      = auth()->user();
        return view($this->activeTemplate . 'user.profile_setting', compact('pageTitle', 'user'));
    }

    public function submitProfile(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string',
            'lastname'  => 'required|string',
            'dob'       => 'date|nullable',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
        ], [
            'firstname.required' => 'First name field is required',
            'lastname.required'  => 'Last name field is required',

        ]);

        $user = auth()->user();

        $user->firstname = $request->firstname;
        $user->lastname  = $request->lastname;
        if ($user->mobile == null || $user->mobile == "") {
            $user->mobile = $request->mobile;
        }
        if ($user->email == null || $user->email == "") {
            $user->email    = $request->email;
        }

        $user->dob       = $request->dob;
        if ($request->hasFile('image')) {
            if ($user->profile_photo != null) {
                unlink('assets/profile/user/' . $user->profile_photo);

                $image = $request->file('image');
                $name = time() . '.' . $image->getClientOriginalExtension();
                $image->move('assets/profile/user/', $name);
                $user->profile_photo = $name;
            } else {
                $image = $request->file('image');
                $name = time() . '.' . $image->getClientOriginalExtension();
                $image->move('assets/profile/user/', $name);
                $user->profile_photo = $name;
            }
        }
        $user->address = [
            'address' => $request->address,
            'state'   => $request->state,
            'zip'     => $request->zip,
            'country' => @$user->address->country,
            'city'    => $request->city,
        ];

        $user->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return back()->withNotify($notify);
    }

    public function changePassword()
    {
        $pageTitle = 'Change Password';
        return view($this->activeTemplate . 'user.password', compact('pageTitle'));
    }

    public function submitPassword(Request $request)
    {
        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $this->validate($request, [
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', $passwordValidation],
        ]);

        $user = auth()->user();
        if (Hash::check($request->current_password, $user->password)) {
            $password       = Hash::make($request->password);
            $user->password = $password;
            $user->save();
            $notify[] = ['success', 'Password changes successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'The password doesn\'t match!'];
            return back()->withNotify($notify);
        }
    }
}
