<?php

namespace App\Http\Controllers;

use App\Services\Chatwoot\ChatwootClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function __construct(
        private ChatwootClient $chatwoot,
    ) {}

    /**
     * Page Teams
     */
    public function index()
    {
        $teams = $this->chatwoot->listTeams();
        $teamsWithMembers = [];

        foreach ($teams as $team) {
            $members = $this->chatwoot->getTeamMembers($team['id']);
            $team['members'] = $members;
            $teamsWithMembers[] = $team;
        }

        // Pass all agents for the "add member" dropdown
        $agents = $this->chatwoot->listAgents();

        return view('teams.index', [
            'teams'  => $teamsWithMembers,
            'agents' => $agents,
        ]);
    }

    /**
     * AJAX — Lister les teams
     */
    public function list(): JsonResponse
    {
        return response()->json($this->chatwoot->listTeams());
    }

    /**
     * AJAX — Creer une equipe
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $team = $this->chatwoot->createTeam($request->name, $request->description);
            return response()->json(['success' => true, 'team' => $team]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Modifier une equipe
     */
    public function update(int $teamId, Request $request): JsonResponse
    {
        $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $team = $this->chatwoot->updateTeam($teamId, $request->only(['name', 'description']));
            return response()->json(['success' => true, 'team' => $team]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Supprimer une equipe
     */
    public function destroy(int $teamId): JsonResponse
    {
        try {
            $this->chatwoot->deleteTeam($teamId);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Ajouter des membres a une equipe
     */
    public function addMembers(int $teamId, Request $request): JsonResponse
    {
        $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'integer',
        ]);

        try {
            $result = $this->chatwoot->addTeamMembers($teamId, $request->user_ids);
            return response()->json(['success' => true, 'members' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Retirer des membres d'une equipe
     */
    public function removeMembers(int $teamId, Request $request): JsonResponse
    {
        $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'integer',
        ]);

        try {
            $this->chatwoot->removeTeamMembers($teamId, $request->user_ids);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Assigner une conversation a une team
     */
    public function assignTeam(int $conversationId, Request $request): JsonResponse
    {
        $request->validate(['team_id' => 'required|integer']);

        $result = $this->chatwoot->assignTeam($conversationId, $request->team_id);

        return response()->json($result);
    }
}
