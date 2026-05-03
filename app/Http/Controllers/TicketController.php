<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
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

        return response()->json(TicketResource::collection($tickets));
    }

    /**
     * Get single ticket.
     */
    public function show(Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        $ticket->load('client', 'agent', 'comments.user');

        return response()->json(TicketResource::make($ticket));
    }

    /**
     * Create ticket (clients only).
     */
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $this->authorize('create', Ticket::class);

        $ticket = Ticket::create([
            ...$request->validated(),
            'status'    => 'open',
            'client_id' => $request->user()->id,
        ]);

        return response()->json(TicketResource::make($ticket), 201);
    }

    /**
     * Update ticket (title/description).
     * Admin=all, Client=own+open only
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('update', $ticket);

        $ticket->update($request->validated());

        return response()->json(TicketResource::make($ticket));
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

        return response()->json(TicketResource::make($ticket));
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

        return response()->json(TicketResource::make($ticket));
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

        return response()->json(TicketResource::make($ticket));
    }

    /**
     * Reopen closed ticket (admin only).
     */
    public function reopen(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('reopen', $ticket);

        $ticket->update(['status' => 'open']);

        return response()->json(TicketResource::make($ticket));
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
