<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
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
