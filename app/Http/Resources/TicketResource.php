<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\CommentResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status,
            'priority'    => $this->priority,
            'client'      => UserResource::make($this->whenLoaded('client')),
            'agent'       => UserResource::make($this->whenLoaded('agent')),
            'comments'    => CommentResource::collection($this->whenLoaded('comments')),
            'created_at'  => $this->created_at->toDateTimeString(),
        ];
    }
}
