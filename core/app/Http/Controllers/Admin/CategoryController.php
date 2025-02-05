<?php

namespace App\Http\Controllers\Admin;

use App\{
    Models\Category,
    Http\Controllers\Controller,
};
use Illuminate\Http\Request;

class CategoryController extends Controller {
    public function index() {
        $pageTitle  = 'All Categories';
        $categories = Category::searchable(['name', 'slug'])->withCount(['teams', 'leagues'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.category', compact('pageTitle', 'categories'));
    }

    public function store(Request $request, $id = 0) {
        $request->validate([
            'name' => 'required|max:40',
            'icon' => 'required|max:255',
            'slug' => 'required|alpha_dash|max:255|unique:categories,slug,' . $id,
        ], [
            'slug.alpha_dash' => 'Only alpha numeric value. No space or special character is allowed'
        ]);

        if ($id) {
            $category         = Category::findOrFail($id);
            $notification     = 'Category updated successfully';
        } else {
            $category     = new Category();
            $notification = 'Category added successfully';
        }

        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->icon = $request->icon;
        $category->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function status($id) {
        return Category::changeStatus($id);
    }
}
