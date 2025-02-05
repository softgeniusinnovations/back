<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OptionController extends Controller {

    public function index($id) {
        $question  = Question::with('game')->findOrFail($id);
        $pageTitle = "Options for - $question->title";
        $options   = $question->options()->latest()->withCount('bets')->paginate(getPaginate());
        return view('admin.game.option', compact('pageTitle', 'question', 'options'));
    }

    public function store(Request $request, $id = 0) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'odds' => 'required|numeric|gt:1|regex:/^\d+(\.\d{1,2})?$/',
        ], [
            'odds.regex' => 'Only two digits are allowed as fractional number',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        if ($id) {
            $option = Option::where('id', $id)->first();
            if (!$option) {
                return response()->json(['error' => 'Option not found']);
            }
            $notification = 'Option updated successfully';
        } else {
            $option              = new Option();
            $option->question_id = $request->question_id;
            $notification        = 'Option added successfully';
        }

        $option->name = $request->name;
        $option->odds = $request->odds;
        $option->save();

        $question = $option->question;
        $options  = $question->options()->withCount('bets')->get();
        return response()->json(['success' => $notification, 'options' => $options]);
    }

    public function status($id) {
        $option = Option::where('id', $id)->with('question')->first();
        if (!$option) {
            return response()->json(['success' => 'Option not found']);
        }
        if ($option->status == Status::ENABLE) {
            $option->status = Status::DISABLE;
        } else {
            $option->status = Status::ENABLE;
        }
        $option->save();
        $question = $option->question;
        $options  = $question->options()->withCount('bets')->get();
        return response()->json(['success' => 'Status changed successfully', 'options' => $options]);
    }
    public function locked($id) {
        $option = Option::where('id', $id)->with('question')->first();
        if (!$option) {
            return response()->json(['success' => 'Option not found']);
        }
        if ($option->locked == Status::ENABLE) {
            $option->locked = Status::DISABLE;
        } else {
            $option->locked = Status::ENABLE;
        }
        $option->save();
        $question = $option->question;
        $options  = $question->options()->withCount('bets')->get();
        return response()->json(['success' => 'Lock status changed successfully', 'options' => $options]);
    }
}
