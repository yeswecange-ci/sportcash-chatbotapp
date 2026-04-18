<?php

namespace App\Http\Controllers\Kash;

use App\Http\Controllers\Controller;
use App\Models\KashBotMessage;
use Illuminate\Http\Request;

class HistoriqueController extends Controller
{
    public function index(Request $request)
    {
        $sender = $request->get('sender');

        $conversations = KashBotMessage::selectRaw('sender, MAX(created_at) as last_at, COUNT(*) as total')
            ->groupBy('sender')
            ->orderByDesc('last_at')
            ->paginate(30)
            ->withQueryString();

        $messages = $sender
            ? KashBotMessage::where('sender', $sender)->orderBy('created_at')->get()
            : collect();

        return view('kash.historique', compact('conversations', 'messages', 'sender'));
    }
}
