<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'body'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function originalPost()
    {
        return $this->belongsTo(Post::class, 'original_post_id');
    }

    public function share()
    {
        return $this->hasMany(Post::class, 'original_post_id');
    }
    
    public function like(){
        return $this->morphMany(Like::class, 'likeable');
    }

    public function media(){
        return $this->morphMany(Media::class, 'mediable');
    }

    public function comment(){
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function report(){
        return $this->morphMany(Report::class, 'reportable');
    }
}
