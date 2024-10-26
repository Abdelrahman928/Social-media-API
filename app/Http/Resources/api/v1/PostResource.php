<?php

namespace App\Http\Resources\api\v1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'share_count' => $this->share()->count(),
            'original_post' => new PostResource($this->whenLoaded('originalPost')),
            'media' => MediaResource::collection($this->whenLoaded('media'))
        ];
    }
}
