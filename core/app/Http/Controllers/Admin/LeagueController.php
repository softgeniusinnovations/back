<?php

namespace App\Http\Controllers\Admin;

use App\Models\{Category, League,};
use App\{
    Rules\FileTypeValidate,
    Http\Controllers\Controller,
};
use Illuminate\Http\Request;

class LeagueController extends Controller
{
    public function index()
    {
        $pageTitle  = 'All Leagues';
        $leagues    = League::searchable(['name', 'slug', 'category:name'])->with('category')->orderBy('id', 'desc')->paginate(getPaginate());
        $categories = Category::orderBy('id', 'desc')->get();
        return view('admin.league', compact('pageTitle', 'leagues', 'categories'));
    }

    public function store(Request $request, $id = 0)
    {

        $this->validation($request, $id);

        if ($id) {
            $league         = League::findOrFail($id);
            $notification   = 'League updated successfully';
        } else {
            $league       = new League();
            $notification = 'League added successfully';
        }

        if ($request->hasFile('image')) {
            $fileName      = fileUploader($request->image, getFilePath('league'), getFileSize('league'), @$league->image);
            $league->image = $fileName;
        }

        $league->category_id = $request->category_id;
        $league->name        = $request->name;
        $league->short_name  = $request->short_name;
        $league->slug        = $request->slug;
        $league->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return League::changeStatus($id);
    }

    protected function validation($request, $id)
    {
        $imageValidation = $id ? 'nullable' : 'required';

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|max:40',
            'short_name'  => 'required|max:40',
            'slug'        => 'required|alpha_dash|max:255|unique:leagues,slug,' . $id,
            'image'       => [$imageValidation, 'image', new FileTypeValidate(['jpeg', 'jpg', 'png'])],
        ], [
            'slug.alpha_dash' => 'Only alpha numeric value. No space or special character is allowed'
        ]);
    }
}
