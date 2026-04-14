<?php

namespace App\Http\Controllers;

use App\Models\AgentToken;
use App\Models\User;
use App\Services\Chatwoot\ChatwootClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    public function __construct(
        private ChatwootClient $chatwoot
    ) {}

    /**
     * Page Agents
     */
    public function index()
    {
        $agents = $this->chatwoot->listAgents();

        return view('agents.index', compact('agents'));
    }

    /**
     * AJAX — Creer un agent (Chatwoot + user local)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role'  => 'required|in:agent,administrator',
        ]);

        try {
            // 1. Create agent in Chatwoot
            $chatwootAgent = $this->chatwoot->createAgent(
                $request->name,
                $request->email,
                $request->role
            );

            // 2. Generate temporary password
            $tempPassword = Str::random(8);

            // 3. Create local Laravel user
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($tempPassword),
                'role'     => $request->role,
            ]);

            // 4. Link to Chatwoot via agent_tokens
            AgentToken::create([
                'user_id'              => $user->id,
                'chatwoot_agent_id'    => $chatwootAgent['id'] ?? null,
                'chatwoot_access_token' => $chatwootAgent['access_token'] ?? '',
                'chatwoot_agent_name'  => $request->name,
                'chatwoot_agent_email' => $request->email,
            ]);

            return response()->json([
                'success'      => true,
                'agent'        => $chatwootAgent,
                'temp_password' => $tempPassword,
                'message'      => "Agent cree. Mot de passe temporaire : {$tempPassword} — Transmettez-le de maniere securisee.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Modifier un agent (role, nom)
     */
    public function update(int $agentId, Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'role' => 'sometimes|in:agent,administrator',
        ]);

        try {
            $agent = $this->chatwoot->updateAgent($agentId, $request->only(['name', 'role']));

            // Update local user if exists
            $token = AgentToken::where('chatwoot_agent_id', $agentId)->first();
            if ($token) {
                $updates = [];
                if ($request->has('name')) {
                    $updates['name'] = $request->name;
                    $token->update(['chatwoot_agent_name' => $request->name]);
                }
                if ($request->has('role')) {
                    $updates['role'] = $request->role;
                }
                if ($updates) {
                    $token->user->update($updates);
                }
            }

            return response()->json(['success' => true, 'agent' => $agent]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Supprimer un agent (Chatwoot + user local)
     */
    public function destroy(int $agentId): JsonResponse
    {
        try {
            // Delete from Chatwoot
            $this->chatwoot->deleteAgent($agentId);

            // Delete local user + token (cascade)
            $token = AgentToken::where('chatwoot_agent_id', $agentId)->first();
            if ($token) {
                $token->user->delete(); // agent_tokens deleted via cascade
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
