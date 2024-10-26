<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'phone_number',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function routeNotificationForVonage(Notification $notification): string
    {
        return $this->phone_number;
    }

    static function booted(){
        Static::created(function($user){
            $user->defaultProcilePic();
        });
    }

    public function defaultProcilePic(){
        static::created(function ($user) {
            $defaultProfilePicture = 'path/to/default/profile/pic/default.png';

            $user->media()->create([
                'media_type' => 'default_profile_picture',
                'file_path' => $defaultProfilePicture,
            ]);
        });
    }

    public function incrementWarningCount()
    {
        $this->increment('warning_count');

        if ($this->warning_count > 2) { 
            $this->update(['is_restricted' => true]);
        }
    }

    public function markPhoneAsVerified(){
        $this->phone_verified_at = now();
        $this->save();
    }

    public function follow(User $user){
        if ($this->id === $user->id) {
            return false;
        }

        if ($this->blockedUsers()->where('blocked_id', $user->id)->exists()) {
            return false;
        }

        if (!$this->following()->where('user_id', $user->id)->exists()) {
            $attached = $this->following()->syncWithoutDetaching([$user->id]);

            if(!empty($attached['attached'])){
                return 'followed';
            };
        }

        $this->following()->detach([$user->id]);

        return 'unfollowed';
    }
    
    public function follower(){
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    public function following(){
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    public function post(){
        return $this->hasMany(Post::class);
    }

    public function share(){
        return $this->hasMany(User::class, 'shared_post_id');
    }

    public function comment(){
        return $this->hasMany(Comment::class);
    }

    public function media(){
        return $this->morphOne(Media::class, 'mediable');
    }

    public function report(){
        return $this->morphMany(Report::class, 'reportable');
    }

    public function blocks(){
        return $this->belongsToMany(User::class, 'blocks', 'blocker_id', 'blocked_id');
    }

    public function blockedBy(){
        return $this->belongsToMany(User::class, 'blocks', 'blocked_id', 'blocker_id');
    }

    public function warning(){
        return $this->hasMany(Warning::class);
    }
}
