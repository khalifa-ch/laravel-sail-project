<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Voir tous les commentaires.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'agent', 'client']);
    }

    /**
     * Voir un commentaire spécifique.
     */
    public function view(User $user, Comment $comment): bool
    {
        // Admin voit tous les commentaires
        if ($user->hasRole('admin')) {
            return true;
        }

        // Agent voit les commentaires de ses tickets assignés
        if ($user->hasRole('agent')) {
            return $comment->ticket->assigned_agent_id === $user->id;
        }

        // Client voit les commentaires de ses tickets
        if ($user->hasRole('client')) {
            return $comment->ticket->client_id === $user->id;
        }

        return false;
    }

    /**
     * Créer un commentaire.
     * Client doit commenter ses tickets (non-closed)
     * Agent doit commenter ses tickets assignés (non-closed)
     * Admin peut commenter n'importe quel ticket
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'agent', 'client']);
    }

    /**
     * Modifier un commentaire.
     * Client/Agent peuvent modifier seulement leurs propres commentaires
     * Admin peut modifier tous
     */
    public function update(User $user, Comment $comment): bool
    {
        // Admin peut modifier tous les commentaires
        if ($user->hasRole('admin')) {
            return true;
        }

        // Seulement le propriétaire du commentaire peut le modifier
        if ($comment->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Supprimer un commentaire.
     * Client/Agent peuvent supprimer seulement leurs propres commentaires
     * Admin peut supprimer tous
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Admin peut supprimer tous les commentaires
        if ($user->hasRole('admin')) {
            return true;
        }

        // Seulement le propriétaire du commentaire peut le supprimer
        if ($comment->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Restaurer un commentaire supprimé (soft delete).
     */
    public function restore(User $user, Comment $comment): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Supprimer définitivement.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        return $user->hasRole('admin');
    }
}