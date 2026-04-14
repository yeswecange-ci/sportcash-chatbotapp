<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\Contact;
use App\Services\Campaign\CampaignService;
use App\Services\Campaign\DeliveryTrackingService;
use App\Services\Twilio\TwilioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function __construct(
        private CampaignService $campaignService,
        private DeliveryTrackingService $trackingService,
        private TwilioService $twilio,
    ) {}

    // ── Pages ────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Campaign::with('creator')->withCount('contacts', 'messages');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $campaigns = $query->latest()->paginate(10);

        return view('campagnes.index', compact('campaigns'));
    }

    public function create(): View
    {
        $templates = $this->twilio->isConfigured() ? $this->twilio->getContentTemplates() : [];
        return view('campagnes.create', compact('templates'));
    }

    public function show(Request $request, Campaign $campaign): View
    {
        $campaign->load('creator', 'messages.contact');
        $stats = $this->campaignService->stats($campaign);
        $templates = $this->twilio->isConfigured() ? $this->twilio->getContentTemplates() : [];
        $totalTargetContacts = $campaign->contacts()->count();

        return view('campagnes.show', compact('campaign', 'stats', 'templates', 'totalTargetContacts'));
    }

    public function edit(Campaign $campaign): View
    {
        $templates = $this->twilio->isConfigured() ? $this->twilio->getContentTemplates() : [];
        return view('campagnes.edit', compact('campaign', 'templates'));
    }

    // ── AJAX Actions ─────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'template_sid'       => 'required|string',
            'template_name'      => 'required|string',
            'template_body'      => 'nullable|string',
            'template_variables' => 'nullable|array',
        ]);

        $campaign = $this->campaignService->create($data);

        return response()->json(['success' => true, 'campaign' => $campaign, 'redirect' => route('campagnes.show', $campaign)]);
    }

    public function update(Request $request, Campaign $campaign): JsonResponse
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'template_sid'       => 'required|string',
            'template_name'      => 'required|string',
            'template_body'      => 'nullable|string',
            'template_variables' => 'nullable|array',
        ]);

        $campaign = $this->campaignService->update($campaign, $data);

        return response()->json(['success' => true, 'campaign' => $campaign]);
    }

    public function destroy(Campaign $campaign): JsonResponse
    {
        $this->campaignService->delete($campaign);
        return response()->json(['success' => true]);
    }

    public function sendPush(Campaign $campaign): JsonResponse
    {
        if ($campaign->contacts()->count() === 0) {
            return response()->json(['success' => false, 'message' => 'Aucun contact dans cette campagne'], 422);
        }

        $this->campaignService->sendNow($campaign);

        return response()->json(['success' => true, 'message' => 'Envoi en cours...']);
    }

    public function schedulePush(Request $request, Campaign $campaign): JsonResponse
    {
        $data = $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        if ($campaign->contacts()->count() === 0) {
            return response()->json(['success' => false, 'message' => 'Aucun contact dans cette campagne'], 422);
        }

        $this->campaignService->schedule($campaign, $data['scheduled_at']);

        return response()->json(['success' => true, 'message' => 'Campagne planifiee']);
    }

    public function cancelSchedule(Campaign $campaign): JsonResponse
    {
        if ($campaign->status !== 'scheduled') {
            return response()->json(['success' => false, 'message' => 'Aucune planification active'], 422);
        }

        $this->campaignService->cancelSchedule($campaign);

        return response()->json(['success' => true, 'message' => 'Planification annulee']);
    }

    public function reopen(Campaign $campaign): JsonResponse
    {
        if ($campaign->status !== 'completed') {
            return response()->json(['success' => false, 'message' => 'Seules les campagnes terminees peuvent etre reouvertes'], 422);
        }

        $campaign->update([
            'status'       => 'draft',
            'scheduled_at' => null,
            'sent_at'      => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Campagne repassee en brouillon']);
    }

    public function sendSingle(Request $request, Campaign $campaign): JsonResponse
    {
        $data = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
        ]);

        $contact = Contact::findOrFail($data['contact_id']);
        $this->campaignService->sendSingle($campaign, $contact);

        return response()->json(['success' => true, 'message' => 'Message en cours d\'envoi']);
    }

    public function attachContacts(Request $request, Campaign $campaign): JsonResponse
    {
        $data = $request->validate([
            'contact_ids'   => 'required|array',
            'contact_ids.*' => 'exists:contacts,id',
        ]);

        $this->campaignService->attachContacts($campaign, $data['contact_ids']);
        $campaign->loadCount('contacts');

        return response()->json(['success' => true, 'contacts_count' => $campaign->contacts_count]);
    }

    public function detachContacts(Request $request, Campaign $campaign): JsonResponse
    {
        $data = $request->validate([
            'contact_ids'   => 'required|array',
            'contact_ids.*' => 'exists:contacts,id',
        ]);

        $this->campaignService->detachContacts($campaign, $data['contact_ids']);
        $campaign->loadCount('contacts');

        return response()->json(['success' => true, 'contacts_count' => $campaign->contacts_count]);
    }

    public function listContacts(Request $request, Campaign $campaign): JsonResponse
    {
        $query = $campaign->contacts();

        if ($q = $request->get('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('phone_number', 'like', "%{$q}%");
            });
        }

        $contacts = $query->orderBy('name')->paginate(10);
        return response()->json($contacts);
    }

    public function listMessages(Campaign $campaign): JsonResponse
    {
        $messages = $campaign->messages()
            ->with('contact')
            ->latest()
            ->paginate(50);

        return response()->json($messages);
    }

    public function stats(Campaign $campaign): JsonResponse
    {
        return response()->json($this->campaignService->stats($campaign));
    }

    public function searchAvailableContacts(Request $request, Campaign $campaign): JsonResponse
    {
        $q = $request->get('q', '');
        $campaignContactIds = $campaign->contacts()->pluck('contacts.id')->toArray();

        $query = Contact::whereNotIn('id', $campaignContactIds);

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('phone_number', 'like', "%{$q}%");
            });
        }

        $contacts = $query->orderBy('name')->limit(15)->get(['id', 'name', 'phone_number']);

        return response()->json($contacts);
    }

    public function refreshStatuses(Campaign $campaign): JsonResponse
    {
        $result = $this->trackingService->refreshCampaignStatuses($campaign->id);

        return response()->json([
            'success' => true,
            'updated' => $result['updated'],
            'errors'  => $result['errors'],
        ]);
    }

    // ── Stats par periode (AJAX) ──────────────────────────

    public function periodStats(Request $request, Campaign $campaign): JsonResponse
    {
        $period = $request->get('period', 'all');
        $query = $campaign->messages();

        $from = match ($period) {
            'today' => Carbon::today(),
            'week'  => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            default => null,
        };

        if ($from) {
            $query->where('sent_at', '>=', $from);
        }

        $statuses = ['queued', 'sent', 'delivered', 'read', 'failed', 'undelivered'];
        $counts = [];
        $total = 0;
        foreach ($statuses as $s) {
            $c = (clone $query)->where('status', $s)->count();
            $counts[$s] = $c;
            $total += $c;
        }
        $counts['total'] = $total;

        return response()->json($counts);
    }

    // ── Dashboard campagnes ───────────────────────────────

    public function dashboard(Request $request): View
    {
        $period = $request->get('period', 'week');
        $data = $this->buildDashboardData($period);

        return view('campagnes.dashboard', array_merge($data, ['period' => $period]));
    }

    public function dashboardData(Request $request): JsonResponse
    {
        $period = $request->get('period', 'week');
        return response()->json($this->buildDashboardData($period));
    }

    private function buildDashboardData(string $period): array
    {
        $from = match ($period) {
            'today' => Carbon::today(),
            'week'  => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            default => Carbon::now()->startOfWeek(),
        };

        // KPIs globaux
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::where('status', 'active')->count();
        $totalContacts = Contact::count();

        $messagesQuery = CampaignMessage::where('created_at', '>=', $from);
        $totalMessages = (clone $messagesQuery)->count();
        $delivered = (clone $messagesQuery)->where('status', 'delivered')->count();
        $read = (clone $messagesQuery)->where('status', 'read')->count();
        $sent = (clone $messagesQuery)->where('status', 'sent')->count();
        $failed = (clone $messagesQuery)->where('status', 'failed')->count();
        $queued = (clone $messagesQuery)->where('status', 'queued')->count();

        $failRate = $totalMessages > 0 ? round(($failed / $totalMessages) * 100, 1) : 0;
        $deliverRate = $totalMessages > 0 ? round((($delivered + $read) / $totalMessages) * 100, 1) : 0;

        // Tendance par jour
        $trend = CampaignMessage::where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status IN ('delivered','read') THEN 1 ELSE 0 END) as delivered")
            ->selectRaw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed")
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get();

        // Repartition statuts
        $statusDistribution = [
            'sent' => $sent,
            'delivered' => $delivered,
            'read' => $read,
            'failed' => $failed,
            'queued' => $queued,
        ];

        // Top campagnes avec stats
        $campaigns = Campaign::withCount('contacts')
            ->with(['messages' => function ($q) use ($from) {
                $q->where('created_at', '>=', $from);
            }])
            ->latest()
            ->get()
            ->map(function ($c) {
                $msgs = $c->messages;
                return [
                    'id'           => $c->id,
                    'name'         => $c->name,
                    'status'       => $c->status,
                    'status_label' => $c->statusLabel(),
                    'status_class' => $c->statusBadgeClass(),
                    'contacts'     => $c->contacts_count,
                    'sent'         => $msgs->whereIn('status', ['sent', 'delivered', 'read'])->count(),
                    'delivered'    => $msgs->whereIn('status', ['delivered', 'read'])->count(),
                    'failed'       => $msgs->where('status', 'failed')->count(),
                    'total'        => $msgs->count(),
                    'deliver_rate' => $msgs->count() > 0
                        ? round($msgs->whereIn('status', ['delivered', 'read'])->count() / $msgs->count() * 100, 1)
                        : 0,
                ];
            });

        return [
            'kpis' => [
                'total_campaigns'  => $totalCampaigns,
                'active_campaigns' => $activeCampaigns,
                'total_contacts'   => $totalContacts,
                'total_messages'   => $totalMessages,
                'delivered'        => $delivered + $read,
                'failed'           => $failed,
                'fail_rate'        => $failRate,
                'deliver_rate'     => $deliverRate,
            ],
            'trend'              => $trend,
            'statusDistribution' => $statusDistribution,
            'campaigns'          => $campaigns,
        ];
    }
}
