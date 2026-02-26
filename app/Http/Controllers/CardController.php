<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $cards = Card::search($request->all())
                    ->orderBy('name', 'asc')
                    ->paginate(32)
                    ->appends($request->all());

        return view('cards.index', compact('cards'));
    }
}