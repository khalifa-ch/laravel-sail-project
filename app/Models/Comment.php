<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
    ];

    // Le ticket auquel appartient ce commentaire
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // L'utilisateur qui a écrit ce commentaire
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
