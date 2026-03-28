<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class TicketController extends Controller
{
    /**
     * List tickets (filtered by role).
     * Admin=all, Agent=assigned, Client=own
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Ticket::class);

        $user = $request->user();
        $query = Ticket::with('client', 'agent', 'comments');

        if ($user->hasRole('admin')) {
            // Admin sees all tickets
            $query = $query;
        } elseif ($user->hasRole('agent')) {
            // Agent sees only assigned tickets
            $query = $query->where('assigned_agent_id', $user->id);
        } elseif ($user->hasRole('client')) {
            // Client sees only own tickets
            $query = $query->where('client_id', $user->id);
        }

        $tickets = $query->paginate(15);

        return response()->json($tickets);
    }

    /**
     * Get single ticket.
     */
    public function show(Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        $ticket->load('client', 'agent', 'comments.user');

        return response()->json($ticket);
    }

    /**
     * Create ticket (clients only).
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Ticket::class);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'priority'    => 'in:low,medium,high',
        ]);

       $ticket = Ticket::create([
        ...$data,
        'status'    => 'open',
        'client_id' => $request->user()->id,
]);
        return response()->json($ticket, 201);
    }

    /**
     * Update ticket (title/description).
     * Admin=all, Client=own+open only
     */
    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('update', $ticket);

        $data = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
        ]);

        $ticket->update($data);

        return response()->json($ticket);
    }

    /**
     * Change status (open → in_progress → closed).
     * Admin=all, Agent=assigned only (cannot reopen)
     */
    public function changeStatus(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('updateStatus', $ticket);

        $data = $request->validate([
            'status' => 'required|in:open,in_progress,closed',
        ]);

        // Agent cannot reopen (can only progress forward: open→in_progress→closed)
        if ($request->user()->hasRole('agent')) {
            if ($data['status'] === 'open') {
                return response()->json(['error' => 'Agents cannot reopen tickets.'], 403);
            }
        }

        $ticket->update(['status' => $data['status']]);

        return response()->json($ticket);
    }

    /**
     * Change priority (admin only).
     */
    public function changePriority(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('updatePriority', $ticket);

        $data = $request->validate([
            'priority' => 'required|in:low,medium,high',
        ]);

        $ticket->update(['priority' => $data['priority']]);

        return response()->json($ticket);
    }

    /**
     * Assign agent to ticket (admin only).
     */
    public function assignAgent(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('assignAgent', $ticket);

        $data = $request->validate([
            'assigned_agent_id' => 'required|exists:users,id|numeric',
        ]);

        $ticket->update(['assigned_agent_id' => $data['assigned_agent_id']]);

        return response()->json($ticket);
    }

    /**
     * Reopen closed ticket (admin only).
     */
    public function reopen(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('reopen', $ticket);

        $ticket->update(['status' => 'open']);

        return response()->json($ticket);
    }

    /**
     * Delete ticket (admin only).
     */
    public function destroy(Ticket $ticket): JsonResponse
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return response()->json(null, 204);
    }
}
