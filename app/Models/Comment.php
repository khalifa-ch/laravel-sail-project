<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory;

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
