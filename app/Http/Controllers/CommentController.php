<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * List all comments for a ticket.
     */
    public function index(Ticket $ticket): JsonResponse
    {
        $this->authorize('viewAny', Comment::class);

        $comments = $ticket->comments()->with('user')->paginate(20);

        return response()->json(CommentResource::collection($comments));
    }

    /**
     * Create a comment on a ticket.
     */
    public function store(StoreCommentRequest $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('create', [Comment::class, $ticket]);

        // Check ticket is not closed
        

        $comment = Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'content'   => $request->validated()['content'],
        ]);

        $comment->load('user');

        return response()->json(CommentResource::make($comment), 201);
    }

    /**
     * Get single comment.
     */
    public function show(Comment $comment): JsonResponse
    {
        $this->authorize('view', $comment);

        $comment->load('user');

        return response()->json(CommentResource::make($comment));
    }

    /**
     * Update comment (content only).
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $comment->update($request->validated());

        $comment->load('user');

        return response()->json(CommentResource::make($comment));
    }

    /**
     * Delete comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(null, 204);
    }
}
