<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransectionProviders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TransactionProvidersController extends Controller
{
    //index
    public function index()
    {
        $pageTitle = 'Transaction Providers';
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $providers = TransectionProviders::latest()->paginate(6);
        return view('admin.agent.providers', compact('pageTitle', 'providers', 'countries'));
    }

    //create
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:70|unique:transection_providers,name,except,id',
            'country_code' => 'required',
            'file' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'dep_min_am' => 'required|min:1',
            'dep_max_am' => 'required|min:1',
            'with_min_am' => 'required|min:1',
            'with_max_am' => 'required|min:1',
            'note_dep' => 'nullable',
            'note_with' => 'nullable',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            try {
                $file = $request->file('file');
                $fileName = time() . '.' . $file->extension();

                $provider = new TransectionProviders;
                $provider->name = $request->name;
                $provider->country_code = $request->country_code;
                $provider->file = $fileName;
                $provider->dep_min_am = $request->dep_min_am;
                $provider->dep_max_am = $request->dep_max_am;
                $provider->with_min_am = $request->with_min_am;
                $provider->with_max_am = $request->with_max_am;
                $provider->note_dep = $request->note_dep;
                $provider->note_with = $request->note_with;
                $provider->status = $request->status;
                if ($provider->save()) {
                    Storage::put('/public/providers/' . $fileName, file_get_contents($file));
                    $notify[] = ['success', 'A new provider add successfully completed.'];
                    return back()->withNotify($notify);
                }
            } catch (\Exception $e) {
                $notify[] = ['error', 'Something went wrong!'];
                return back()->withNotify($notify);
            }
        }
    }

    //edit
    public function edit($id)
    {
        $pageTitle = 'Transaction Providers Update';
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $provider = TransectionProviders::findOrFail($id);
        return view('admin.agent.providers-edit', compact('pageTitle', 'countries', 'provider'));
    }

    //update
    public function update(Request $request, $id)
    {
        $provider = TransectionProviders::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:70', Rule::unique('transection_providers')->ignore($id)],
            'country_code' => 'required',
            'file' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
            'status' => 'required',
            'dep_min_am' => 'required|min:1',
            'dep_max_am' => 'required|min:1',
            'with_min_am' => 'required|min:1',
            'with_max_am' => 'required|min:1',
            'note_dep' => 'nullable',
            'note_with' => 'nullable',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            try {
                if ($request->file) {
                    $file = $request->file('file');
                    $fileName = time() . '.' . $file->extension();
                    $previousFile = $provider->file;
                    $provider->file = $fileName;
                }
                $provider->name = $request->name;
                $provider->country_code = $request->country_code;
                $provider->dep_min_am = $request->dep_min_am;
                $provider->dep_max_am = $request->dep_max_am;
                $provider->with_min_am = $request->with_min_am;
                $provider->with_max_am = $request->with_max_am;
                $provider->note_dep = $request->note_dep;
                $provider->note_with = $request->note_with;
                $provider->status = $request->status;
                if ($provider->save()) {
                    if ($request->file && Storage::exists('/public/providers/' . $previousFile)) {
                        Storage::delete('/public/providers/' . $previousFile);
                        Storage::put('/public/providers/' . $fileName, file_get_contents($file));
                    }
                    // else{
                    //      Storage::put('/public/providers/' . $fileName, file_get_contents($file));
                    // }
                    $notify[] = ['success', 'Successfully updated.'];
                    return to_route('admin.agent.transaction.providers')->withNotify($notify);
                }
            } catch (\Exception $e) {
                $notify[] = ['error', $e->getMessage()];
                return back()->withNotify($notify);
            }
        }
    }

    //status
    public function status($id)
    {
        $provider = TransectionProviders::findOrFail($id);
        $provider->status = !$provider->status;
        $provider->save();
        $notify[] = ['success', 'Status successfully updated.'];
        return back()->withNotify($notify);
    }

    // delete
    public function delete($id)
    {
        $provider = TransectionProviders::findOrFail($id);
        $previousFile = $provider->file;
        if ($previousFile && Storage::exists('/public/providers/' . $previousFile)) {
            Storage::delete('/public/providers/' . $previousFile);
        }
        if ($provider->delete()) {
            $notify[] = ['success', 'Successfully deleted.'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Something went wrong.'];
            return back()->withNotify($notify);
        }
    }
}
