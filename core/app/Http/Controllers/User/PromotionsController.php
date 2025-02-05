<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\AffiliatePromos;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $promotions = Promotion::where('user_id', auth()->id())->latest()->paginate(getPaginate(10));
        $promo = Promotion::where('user_id', auth()->id())->first();
        $pageTitle = 'Promotions';
        return view($this->activeTemplate . 'user.affiliate.promotion.index', compact('promotions', 'pageTitle','promo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageTitle = 'Create Promotion';
        return view($this->activeTemplate . 'user.affiliate.promotion.create', compact('pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required',
            'details' => 'nullable|string',
            'promo_code' => 'required|unique:promotions'
        ]);

        $promo                      = new Promotion();
        $promo->title               = $request->title;
        $promo->promo_code          = $request->promo_code;
        $promo->status              = $request->status;
        $promo->details             = $request->details;
        $promo->promo_percentage    = $request->promo_percentage;
        $promo->slug                = slug($request->title);
        $promo->is_admin_approved   = 0;

        if ($request->hasFile('attachments')) {
            $image = $request->file('attachments');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move('assets/promotion/', $filename);
            $promo->image = $filename;
        }
        $promo->user_id = auth()->id();
        $promo->save();
        $notify[] = ['success', "Promotion Created Successfully"];
        return redirect()->route('user.promotions.index')->withNotify($notify);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $promo = Promotion::findOrFail($id);
        if ($promo->user_id != auth()->id()) {
            abort(404);
        }
        $pageTitle = 'Edit Promotion';
        return view($this->activeTemplate . 'user.affiliate.promotion.update', compact('pageTitle', 'promo'));
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
        $request->validate([
            'title' => 'required',
            'details' => 'required',
            'promo_code' => 'required|unique:promotions,promo_code,' . $id,
        ]);

        $promo                  = Promotion::findOrFail($id);
        $promo->title           = $request->title;
        $promo->promo_code      = $request->promo_code;
        $promo->status          = $request->status;
        $promo->start_date      = $request->start_date;
        $promo->end_date        = $request->end_date;
        $promo->details         = $request->details;
        $promo->learn_more_link = $request->learn_more_link;
        $promo->slug            = slug($request->title);

        if ($request->hasFile('attachments')) {
            $image_path = public_path() . '/assets/promotion/' . $promo->image;

            if ($promo->image && file_exists($image_path)) {
                unlink($image_path);
            }

            $image = $request->file('attachments');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move('assets/promotion/', $filename);
            $promo->image = $filename;
        }

        $promo->save();
        $notify[] = ['success', "Promotion Updated Successfully"];
        return redirect()->route('user.promotions.index')->withNotify($notify);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $promo = Promotion::findOrFail($id);
        if ($promo->user_id != auth()->id()) {
            abort(404);
        }
        if ($promo->image) {
            $image_path = public_path() . '/assets/promotion/' . $promo->image;
            unlink($image_path);
        }
        $promo->delete();
        return response()->json(['success' => 'Promotion Successfully Deleted']);
    }

}
