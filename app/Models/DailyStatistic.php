<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DailyStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'total_conversations',
        'unique_users',
        'new_users',
        'returning_users',
        'completed_conversations',
        'timeout_conversations',
        'abandoned_conversations',
        'menu_informations',
        'menu_demandes',
        'menu_paris',
        'menu_encaissement',
        'menu_reclamations',
        'menu_plaintes',
        'menu_conseiller',
        'menu_faq',
        'submenu_stats',
        'menu_stats',
        'avg_session_duration_seconds',
        'avg_response_time_ms',
        'clients_count',
        'non_clients_count',
        'invalid_inputs_count',
        'errors_count',
    ];

    protected $casts = [
        'date'          => 'date',
        'submenu_stats' => 'array',
        'menu_stats'    => 'array',
    ];

    /**
     * Récupérer ou créer les stats d'aujourd'hui
     */
    public static function today(): self
    {
        return self::firstOrCreate(
            ['date' => today()],
            ['submenu_stats' => []]
        );
    }

    /**
     * Incrémenter un compteur
     */
    public function incrementField(string $field, int $amount = 1): self
    {
        $this->$field = ($this->$field ?? 0) + $amount;
        $this->save();
        return $this;
    }

    /**
     * Incrémenter les stats de menu dynamiquement.
     * Fonctionne pour n'importe quel menu/flow Twilio.
     *
     * @param string $menuName   Nom du widget/menu (ex: "menu_principal", "sous_menu_info")
     * @param string $choiceLabel Label du choix (ex: "Informations", "1")
     */
    public function incrementMenuStats(string $menuName, string $choiceLabel): self
    {
        $stats = $this->menu_stats ?? [];
        $stats[$menuName][$choiceLabel] = ($stats[$menuName][$choiceLabel] ?? 0) + 1;
        $this->menu_stats = $stats;
        $this->save();
        return $this;
    }

    /**
     * @deprecated Utiliser incrementMenuStats() à la place
     */
    public function incrementMainMenu(string $menuChoice): self
    {
        return $this->incrementMenuStats('menu_principal', $menuChoice);
    }

    /**
     * Incrémenter les stats de sous-menu
     */
    public function incrementSubmenu(string $menuPath): self
    {
        $stats = $this->submenu_stats ?? [];
        $stats[$menuPath] = ($stats[$menuPath] ?? 0) + 1;
        $this->submenu_stats = $stats;
        $this->save();
        return $this;
    }

    /**
     * Recalculer les statistiques pour une date
     */
    public static function recalculateForDate(\DateTime $date): self
    {
        $stat = self::firstOrNew(['date' => $date->format('Y-m-d')]);

        // Requêtes de base
        $conversations = Conversation::whereDate('started_at', $date);
        $events = ConversationEvent::whereDate('event_at', $date);

        // Compteurs globaux
        $stat->total_conversations = $conversations->count();
        $stat->unique_users = $conversations->distinct('phone_number')->count();

        // Statuts
        $stat->completed_conversations = (clone $conversations)->where('status', 'completed')->count();
        $stat->timeout_conversations = (clone $conversations)->where('status', 'timeout')->count();
        $stat->abandoned_conversations = (clone $conversations)->where('status', 'abandoned')->count();

        // Clients vs Non-clients
        $stat->clients_count = (clone $conversations)->where('is_client', true)->count();
        $stat->non_clients_count = (clone $conversations)->where('is_client', false)->count();

        // Erreurs
        $stat->invalid_inputs_count = (clone $events)->where('event_type', 'invalid_input')->count();
        $stat->errors_count = (clone $events)->where('event_type', 'error')->count();

        // Durée moyenne
        $avgDuration = Conversation::whereDate('started_at', $date)
            ->whereNotNull('ended_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, ended_at)) as avg_duration')
            ->value('avg_duration');
        $stat->avg_session_duration_seconds = $avgDuration ? (int) $avgDuration : null;

        // Temps de réponse moyen
        $avgResponseTime = ConversationEvent::whereDate('event_at', $date)
            ->whereNotNull('response_time_ms')
            ->avg('response_time_ms');
        $stat->avg_response_time_ms = $avgResponseTime ? (int) $avgResponseTime : null;

        // Menu stats dynamique — indépendant du flow Twilio
        $conversationIds = Conversation::whereDate('started_at', $date)->pluck('id');
        $menuStats = [];
        ConversationEvent::whereIn('conversation_id', $conversationIds)
            ->where('event_type', 'menu_choice')
            ->get()
            ->each(function ($event) use (&$menuStats) {
                $menuName = $event->menu_name
                    ?: (isset($event->metadata['menu_choice']) ? $event->metadata['menu_choice'] : 'menu_principal');
                $label = $event->choice_label ?: $event->user_input ?: 'unknown';
                $menuStats[$menuName][$label] = ($menuStats[$menuName][$label] ?? 0) + 1;
            });
        $stat->menu_stats = $menuStats;

        $stat->save();
        return $stat;
    }

    /**
     * Obtenir les stats des 30 derniers jours
     */
    public static function last30Days(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('date', '>=', now()->subDays(30))
                   ->orderBy('date', 'desc')
                   ->get();
    }

    /**
     * Obtenir les stats agrégées pour une période
     */
    public static function aggregateForPeriod(\DateTime $startDate, \DateTime $endDate): array
    {
        return self::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                SUM(total_conversations) as total_conversations,
                SUM(unique_users) as unique_users,
                SUM(completed_conversations) as completed_conversations,
                SUM(timeout_conversations) as timeout_conversations,
                SUM(menu_informations) as menu_informations,
                SUM(menu_demandes) as menu_demandes,
                SUM(menu_paris) as menu_paris,
                SUM(menu_encaissement) as menu_encaissement,
                SUM(menu_reclamations) as menu_reclamations,
                SUM(menu_plaintes) as menu_plaintes,
                SUM(menu_conseiller) as menu_conseiller,
                SUM(menu_faq) as menu_faq,
                AVG(avg_session_duration_seconds) as avg_session_duration_seconds
            ')
            ->first()
            ->toArray();
    }
}
