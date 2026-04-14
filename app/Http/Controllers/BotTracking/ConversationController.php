<?php

namespace App\Http\Controllers\BotTracking;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationEvent;
use App\Models\DailyStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    /**
     * Display the main dashboard
     */
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Add time to dates to include full day
        $dateFromFull = $dateFrom . ' 00:00:00';
        $dateToFull = $dateTo . ' 23:59:59';

        // Get overall statistics - ALL filtered by date range for consistency
        $conversationsInRange = Conversation::whereBetween('started_at', [$dateFromFull, $dateToFull]);

        $stats = [
            'total_conversations'     => $conversationsInRange->count(),
            'active_conversations'    => (clone $conversationsInRange)->where('status', 'active')->count(),
            'completed_conversations' => (clone $conversationsInRange)->where('status', 'completed')->count(),
            // Clients/non-clients : source = conversations.is_client (mis à jour à chaque interaction bot)
            'total_clients'           => (clone $conversationsInRange)->where('is_client', true)->distinct()->count('phone_number'),
            'total_non_clients'       => (clone $conversationsInRange)->where('is_client', false)->distinct()->count('phone_number'),
            'avg_duration'            => (clone $conversationsInRange)->whereNotNull('ended_at')->avg('duration_seconds'),
            'total_duration'          => (clone $conversationsInRange)->whereNotNull('ended_at')->sum('duration_seconds'),
            'total_events'            => ConversationEvent::whereHas('conversation', function($q) use ($dateFromFull, $dateToFull) {
                $q->whereBetween('started_at', [$dateFromFull, $dateToFull]);
            })->count(),
            'total_messages'          => ConversationEvent::where('event_type', 'message_received')
                ->whereHas('conversation', function($q) use ($dateFromFull, $dateToFull) {
                    $q->whereBetween('started_at', [$dateFromFull, $dateToFull]);
                })->count(),
            'total_menu_choices'      => ConversationEvent::where('event_type', 'menu_choice')
                ->whereHas('conversation', function($q) use ($dateFromFull, $dateToFull) {
                    $q->whereBetween('started_at', [$dateFromFull, $dateToFull]);
                })->count(),
            'total_free_inputs'       => ConversationEvent::where('event_type', 'free_input')
                ->whereHas('conversation', function($q) use ($dateFromFull, $dateToFull) {
                    $q->whereBetween('started_at', [$dateFromFull, $dateToFull]);
                })->count(),
            'unique_clients'          => (clone $conversationsInRange)->distinct()->count('phone_number'),
            'new_clients'             => \App\Models\Client::whereBetween('created_at', [$dateFromFull, $dateToFull])->count(),
        ];

        // Get daily statistics for chart
        $dailyStats = DailyStatistic::whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date', 'asc')
            ->get();

        // Distribution dynamique des menus — s'adapte automatiquement à tout flow Twilio
        $menuStats = ConversationEvent::where('event_type', 'menu_choice')
            ->whereHas('conversation', fn($q) => $q->whereBetween('started_at', [$dateFromFull, $dateToFull]))
            ->whereNotNull('menu_name')
            ->select('menu_name', 'choice_label', 'user_input', DB::raw('count(*) as count'))
            ->groupBy('menu_name', 'choice_label', 'user_input')
            ->orderBy('menu_name')
            ->orderByDesc('count')
            ->get()
            ->groupBy('menu_name')
            ->map(fn($choices) => $choices->map(fn($c) => [
                'label' => $c->choice_label ?: $c->user_input ?: '?',
                'count' => $c->count,
            ])->values());

        // Recent conversations
        $recentConversations = Conversation::with('events')
            ->whereBetween('started_at', [$dateFromFull, $dateToFull])
            ->orderBy('started_at', 'desc')
            ->limit(10)
            ->get();

        return view('bot-tracking.conversations.index', compact('stats', 'dailyStats', 'menuStats', 'recentConversations', 'dateFrom', 'dateTo'));
    }

    /**
     * Display active conversations
     */
    public function active()
    {
        $activeConversations = Conversation::active()
            ->with('events')
            ->orderBy('last_activity_at', 'desc')
            ->get();

        return view('bot-tracking.conversations.active', compact('activeConversations'));
    }

    /**
     * Display conversations pending agent takeover
     */

    /**
     * Display all conversations list
     */
    public function conversations(Request $request)
    {
        $query = Conversation::with('events');

        // Date range filter — défaut : 30 derniers jours pour cohérence avec dashboard
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo   = $request->input('date_to',   now()->format('Y-m-d'));

        $dateFromFull = $dateFrom . ' 00:00:00';
        $dateToFull   = $dateTo   . ' 23:59:59';
        $query->whereBetween('started_at', [$dateFromFull, $dateToFull]);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Client type filter
        if ($request->filled('is_client')) {
            $query->where('is_client', $request->is_client);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                  ->orWhere('client_full_name', 'like', "%{$search}%")
                  ->orWhere('whatsapp_profile_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $conversations = $query->orderBy('started_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Build base query for stats with same filters as main query
        $baseStatsQuery = Conversation::query();

        if ($dateFrom && $dateTo) {
            $dateFromFull = $dateFrom . ' 00:00:00';
            $dateToFull = $dateTo . ' 23:59:59';
            $baseStatsQuery->whereBetween('started_at', [$dateFromFull, $dateToFull]);
        } elseif ($dateFrom) {
            $dateFromFull = $dateFrom . ' 00:00:00';
            $baseStatsQuery->where('started_at', '>=', $dateFromFull);
        } elseif ($dateTo) {
            $dateToFull = $dateTo . ' 23:59:59';
            $baseStatsQuery->where('started_at', '<=', $dateToFull);
        }

        if ($request->filled('is_client')) {
            $baseStatsQuery->where('is_client', $request->is_client);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $baseStatsQuery->where(function($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                  ->orWhere('client_full_name', 'like', "%{$search}%")
                  ->orWhere('whatsapp_profile_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Calculate total counts for the current filter
        $totalStats = [
            'total' => $conversations->total(),
            'active' => (clone $baseStatsQuery)->where('status', 'active')->count(),
            'completed' => (clone $baseStatsQuery)->where('status', 'completed')->count(),
        ];

        return view('bot-tracking.conversations.list', compact('conversations', 'totalStats', 'dateFrom', 'dateTo'));
    }

    /**
     * Display conversation detail
     */
    public function show($id)
    {
        $conversation = Conversation::with(['events' => function($query) {
            $query->orderBy('event_at', 'asc')->orderBy('created_at', 'asc');
        }])->findOrFail($id);

        // All conversations from this phone number (for the sessions list)
        $phoneConversations = Conversation::where('phone_number', $conversation->phone_number)
            ->orderBy('started_at', 'desc')
            ->get();

        // All events across ALL conversations for this phone number
        $allEvents = ConversationEvent::whereIn('conversation_id', $phoneConversations->pluck('id'))
            ->with('conversation')
            ->orderBy('event_at', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('bot-tracking.conversations.show', compact('conversation', 'phoneConversations', 'allEvents'));
    }

    /**
     * Display statistics page
     */
    public function statistics(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Add time to dates to include full day
        $dateFromFull = $dateFrom . ' 00:00:00';
        $dateToFull = $dateTo . ' 23:59:59';

        // Get overall statistics - CONSISTENT with dashboard
        $conversationsInRange = Conversation::whereBetween('started_at', [$dateFromFull, $dateToFull]);

        $stats = [
            'total_conversations'     => $conversationsInRange->count(),
            'active_conversations'    => (clone $conversationsInRange)->where('status', 'active')->count(),
            'completed_conversations' => (clone $conversationsInRange)->where('status', 'completed')->count(),
            // Clients/non-clients : source = conversations.is_client (mis à jour à chaque interaction bot)
            'total_clients'           => (clone $conversationsInRange)->where('is_client', true)->distinct()->count('phone_number'),
            'total_non_clients'       => (clone $conversationsInRange)->where('is_client', false)->distinct()->count('phone_number'),
            'avg_duration' => (clone $conversationsInRange)->whereNotNull('ended_at')->avg('duration_seconds'),
            'total_duration' => (clone $conversationsInRange)->whereNotNull('ended_at')->sum('duration_seconds'),
            'total_events' => ConversationEvent::whereHas('conversation', function($q) use ($dateFromFull, $dateToFull) {
                $q->whereBetween('started_at', [$dateFromFull, $dateToFull]);
            })->count(),
            'total_messages' => ConversationEvent::where('event_type', 'message_received')
                ->whereHas('conversation', function($q) use ($dateFromFull, $dateToFull) {
                    $q->whereBetween('started_at', [$dateFromFull, $dateToFull]);
                })->count(),
            'total_menu_choices' => ConversationEvent::where('event_type', 'menu_choice')
                ->whereHas('conversation', function($q) use ($dateFromFull, $dateToFull) {
                    $q->whereBetween('started_at', [$dateFromFull, $dateToFull]);
                })->count(),
            'total_free_inputs' => ConversationEvent::where('event_type', 'free_input')
                ->whereHas('conversation', function($q) use ($dateFromFull, $dateToFull) {
                    $q->whereBetween('started_at', [$dateFromFull, $dateToFull]);
                })->count(),
            'unique_clients' => (clone $conversationsInRange)->distinct()->count('phone_number'),
            'new_clients' => \App\Models\Client::whereBetween('created_at', [$dateFromFull, $dateToFull])->count(),
        ];

        // Get daily statistics for charts
        $dailyStats = DailyStatistic::whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date', 'asc')
            ->get();

        // Distribution dynamique des menus — s'adapte automatiquement à tout flow Twilio
        $menuStats = ConversationEvent::where('event_type', 'menu_choice')
            ->whereHas('conversation', fn($q) => $q->whereBetween('started_at', [$dateFromFull, $dateToFull]))
            ->whereNotNull('menu_name')
            ->select('menu_name', 'choice_label', 'user_input', DB::raw('count(*) as count'))
            ->groupBy('menu_name', 'choice_label', 'user_input')
            ->orderBy('menu_name')
            ->orderByDesc('count')
            ->get()
            ->groupBy('menu_name')
            ->map(fn($choices) => $choices->map(fn($c) => [
                'label' => $c->choice_label ?: $c->user_input ?: '?',
                'count' => $c->count,
            ])->values());

        // Status distribution
        $statusStats = Conversation::whereBetween('started_at', [$dateFromFull, $dateToFull])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Popular paths
        $popularPaths = Conversation::whereBetween('started_at', [$dateFromFull, $dateToFull])
            ->whereNotNull('menu_path')
            ->select('menu_path', DB::raw('count(*) as count'))
            ->groupBy('menu_path')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Peak hours
        $peakHours = Conversation::whereBetween('started_at', [$dateFromFull, $dateToFull])
            ->select(DB::raw('HOUR(started_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();

        // Event type breakdown
        $eventStats = ConversationEvent::whereHas('conversation', function($q) use ($dateFromFull, $dateToFull) {
                $q->whereBetween('started_at', [$dateFromFull, $dateToFull]);
            })
            ->select('event_type', DB::raw('count(*) as count'))
            ->groupBy('event_type')
            ->orderBy('count', 'desc')
            ->get();

        // Widget usage statistics
        $widgetStats = ConversationEvent::where('event_type', 'free_input')
            ->whereHas('conversation', function($q) use ($dateFromFull, $dateToFull) {
                $q->whereBetween('started_at', [$dateFromFull, $dateToFull]);
            })
            ->whereNotNull('widget_name')
            ->select('widget_name', DB::raw('count(*) as count'))
            ->groupBy('widget_name')
            ->orderBy('count', 'desc')
            ->get();

        return view('bot-tracking.analytics.index', compact('stats', 'dailyStats', 'menuStats', 'statusStats', 'popularPaths', 'peakHours', 'eventStats', 'widgetStats', 'dateFrom', 'dateTo'));
    }

    /**
     * Display search page for free inputs
     */
    public function search(Request $request)
    {
        $query = ConversationEvent::with('conversation')
            ->where('event_type', 'free_input');

        if ($request->filled('search')) {
            $query->where('user_input', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('event_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('event_at', '<=', $request->date_to);
        }

        $freeInputs = $query->orderBy('event_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('bot-tracking.analytics.search', compact('freeInputs'));
    }
}
