<?php

namespace App\Http\Resources\api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->_id,
            'body' => $this->body,
            'author' => new UserResource($this->user),
            'created_at' => $this->created_at->diffForHumans(),
            'likes' => $this->like()->count(),
            'comments_count' => $this->comment()->count(),
            'original_post' => new PostResource($this->whenLoaded('originalPost')),
            'cmments' => CommentResource::clollection($this->whenLoaded('comment')),
            'media' => MediaResource::collection($this->whenLoaded('media'))
        ];
    }
}
