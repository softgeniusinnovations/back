<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{

    public function index($id)
    {
        $pageTitle = "All Markets";
        $game      = Game::findOrFail($id);
        $questions = $game->questions()->orderBy('id', 'desc')->with(['options' => function ($q) {
            $q->withCount('bets');
        }])->paginate(getPaginate());
        return view('admin.game.question', compact('pageTitle', 'game', 'questions'));
    }

    public function store(Request $request, $id = 0)
    {

        $request->validate([
            'game_id'     => 'required|exists:games,id',
            'title'        => 'required|max:255',
        ]);

        if ($id) {
            $question     = Question::findOrFail($id);
            $notification = 'Market updated successfully';
        } else {
            $question          = new Question();
            $question->game_id = $request->game_id;
            $notification      = 'Market added successfully';
        }

        $question->title = $request->title;
        $question->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return Question::changeStatus($id);
    }
    public function locked($id)
    {
        return Question::changeStatus($id, 'locked');
    }
}
