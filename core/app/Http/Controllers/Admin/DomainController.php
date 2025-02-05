<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
class DomainController extends Controller {
    private $pageTitle;

    public function index() {
        $pageTitle = 'Domains';
        $domains = Domain::latest()->paginate(getPaginate(10));
        return view('admin.domain.list', compact('pageTitle', 'domains'));
    }

    public function create() {
        $pageTitle = 'Domain Add';
        return view('admin.domain.create', compact('pageTitle'));
    }
    public function store(Request $request) {
      $validate     = Validator::make($request->all(), [
            'name'         => 'required|string|unique:domains,domain_name',
            'page' => 'required|string',
            'title' => 'required|string',
            'subtitle' => 'required|string',
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }else{
            $logoPath = null;
            if ($request->hasFile('file')) {
                  // Store the file in the 'storage/app/domains' directory
                  $logoPath = $request->file('file')->store('domains', 'public');
            }

             // Prepare the contents JSON data
            $contents = [
                  $request->input('page') => [
                     'title' => $request->input('title'),
                     'subtitle' => $request->input('subtitle'),
                  ],
            ];
            // Create the domain record
            $domain = Domain::create([
                  'domain_name' => $request->input('name'),
                  'logo' => $logoPath,
                  'contents' => json_encode($contents),
            ]);
            if($domain)
            {
               $notify[] = ['success', "New Domain successfully added"];
               return to_route('admin.domain.list')->withNotify($notify);
            }else{
               $notify[] = ['error', "Something went wrong"];
               return back()->withNotify($notify);
            }
            
            
        }
    }
    public function edit($id)
   {
      $domain = Domain::findOrFail($id);
      
      $pageTitle = $domain->domain_name;
      $contents = json_decode($domain->contents, true);

      return view('admin.domain.edit', compact('domain', 'contents','pageTitle'));
   }


    public function update(Request $request, $id)
   {
      // Find the domain record to update
      $domain = Domain::findOrFail($id);

      // Validation rules
      $validate = Validator::make($request->all(), [
         'name'    => 'required|string|unique:domains,domain_name,' . $domain->id, // Ignore current record
         'page'    => 'required|string',
         'title'   => 'required|string',
         'subtitle' => 'required|string',
         'file'    => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048', // File is optional for update
      ]);

      // If validation fails, return back with errors
      if ($validate->fails()) {
         return back()->withErrors($validate)->withInput();
      }

      // Handle file upload
      $logoPath = $domain->logo; // Retain the existing logo by default
      if ($request->hasFile('file')) {
         // Delete the old logo file if it exists
         if ($domain->logo && Storage::disk('public')->exists($domain->logo)) {
            Storage::disk('public')->delete($domain->logo);
         }

         // Store the new file in the 'storage/app/public/domains' directory
         $logoPath = $request->file('file')->store('domains', 'public');
      }

      // Prepare the contents JSON data
      $contents = [
         $request->input('page') => [
               'title'    => $request->input('title'),
               'subtitle' => $request->input('subtitle'),
         ],
      ];

      // Update the domain record
      $updated = $domain->update([
         'domain_name' => $request->input('name'),
         'logo'        => $logoPath,
         'contents'    => json_encode($contents),
         'status'      => $request->input('status')
      ]);

      // Check if the update was successful
      if ($updated) {
         $notify[] = ['success', "Domain updated successfully"];
         return to_route('admin.domain.list')->withNotify($notify);
      } else {
         $notify[] = ['error', "Something went wrong"];
         return back()->withNotify($notify);
      }
   }

   public function destroy($id)
    {
        // Find the domain by ID
        $domain = Domain::findOrFail($id);

        // Check if there is an associated logo and delete it
        if ($domain->logo && Storage::disk('public')->exists($domain->logo)) {
            Storage::disk('public')->delete($domain->logo);
        }

        // Delete the domain record itself
        $domain->delete();

        // Redirect with a success message
        $notify[] = ['success', "Domain deleted successfully"];
        return to_route('admin.domain.list')->withNotify($notify);
    }

}
