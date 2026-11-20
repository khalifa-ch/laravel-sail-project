<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'client_id',
        'assigned_agent_id',
    ];

    protected $casts = [
        'status'   => 'string',
        'priority' => 'string',
    ];

    // Les tickets que ce user a le droit de voir : admin=tous, agent=assignés, client=les siens
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('agent')) {
            return $query->where('assigned_agent_id', $user->id);
        }

        if ($user->hasRole('client')) {
            return $query->where('client_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    // Le client qui a créé le ticket
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // L'agent assigné au ticket (nullable)
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    // Les commentaires du ticket
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
