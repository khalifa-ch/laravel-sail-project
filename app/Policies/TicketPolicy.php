<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    // viewAny: all roles can see the list (filtering done in controller)
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'agent', 'client']);
    }

    // view: admin=all, agent=assigned tickets only, client=own tickets only
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->hasRole('agent')) {
            return $ticket->assigned_agent_id === $user->id;
        }
        if ($user->hasRole('client')) {
            return $ticket->client_id === $user->id;
        }
        return false;
    }

    // create: only clients can create tickets
    public function create(User $user): bool
    {
        return $user->hasRole('client');
    }

    // update: admin can edit all, client can edit own tickets (only if status=open)
    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $user->hasRole('client') && $ticket->client_id === $user->id && $ticket->status === 'open';
    }

    // updateStatus: admin=any change, agent=assigned tickets, forward only (open → in_progress → closed)
    public function updateStatus(User $user, Ticket $ticket, string $newStatus): Response
    {
        if ($user->hasRole('admin')) {
            return Response::allow();
        }

        if (! $user->hasRole('agent') || $ticket->assigned_agent_id !== $user->id) {
            return Response::deny();
        }

        $order = ['open' => 0, 'in_progress' => 1, 'closed' => 2];

        return $order[$newStatus] > $order[$ticket->status]
            ? Response::allow()
            : Response::deny('Agents can only move tickets forward.');
    }

    // updatePriority: admin only
    public function updatePriority(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin');
    }

    // assignAgent: admin only
    public function assignAgent(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin');
    }

    // reopen: admin only AND ticket must be closed
    public function reopen(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin') && $ticket->status === 'closed';
    }

    // delete: admin only
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin');
    }

    // restore: admin only
    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin');
    }

    // forceDelete: admin only
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin');
    }
}
