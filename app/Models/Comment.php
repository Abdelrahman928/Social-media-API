<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function commentable(){
        return $this->morphTo();
    }

    public function like(){
        return $this->morphMany(Like::class, 'likeable');
    }

    public function media(){
        return $this->morphMany(Media::class, 'mediable');
    }

    public function report(){
        return $this->morphMany(Report::class, 'reportable');
    }
}
