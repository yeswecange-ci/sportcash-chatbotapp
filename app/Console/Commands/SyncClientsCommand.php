<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\ConversationEvent;

class SyncClientsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:sync {--force : Force sync even if client already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize clients from conversations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting client synchronization...');

        $conversations = Conversation::whereNotNull('phone_number')
            ->orderBy('created_at', 'asc')
            ->get();

        $synced = 0;
        $updated = 0;
        $total = $conversations->count();

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        foreach ($conversations as $conversation) {
            $client = Client::findOrCreateByPhone($conversation->phone_number);

            // Update client info from conversation
            $client->updateFromConversation($conversation);

            // Count interactions for this conversation
            $interactionCount = ConversationEvent::where('conversation_id', $conversation->id)
                ->whereIn('event_type', ['free_input', 'menu_choice'])
                ->count();

            if ($interactionCount > 0 && $this->option('force')) {
                // Reset counts if force option is used
                $client->interaction_count = 0;
                $client->conversation_count = 0;
                $client->save();
            }

            if ($interactionCount > 0) {
                $client->increment('interaction_count', $interactionCount);
            }

            $client->increment('conversation_count');

            // Update first and last interaction dates
            if (!$client->first_interaction_at || $conversation->started_at < $client->first_interaction_at) {
                $client->first_interaction_at = $conversation->started_at;
            }

            if (!$client->last_interaction_at || $conversation->last_activity_at > $client->last_interaction_at) {
                $client->last_interaction_at = $conversation->last_activity_at ?? $conversation->started_at;
            }

            $client->save();

            if ($client->wasRecentlyCreated) {
                $synced++;
            } else {
                $updated++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Synchronization completed!");
        $this->info("- New clients: {$synced}");
        $this->info("- Updated clients: {$updated}");
        $this->info("- Total processed: {$total}");

        return Command::SUCCESS;
    }
}
