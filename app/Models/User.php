<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    // 添加creating监听方法
    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->activation_token = str_random(30);
        });
    }

    // 获取用户Gravatar头像
    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "https://www.gravatar.com/avatar/$hash?s=$size";
    }

    // 用户微博关联关系
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    // 动态流原型，当前用户关注的人发布过的所有微博动态
    public function feed()
    {
        return $this->statuses()
                    ->orderBy('created_at', 'desc');
    }

    // 用户粉丝关联关系
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    // 用户关注人关联关系
    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    // 关注用户
    public function follow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }

        $this->followings()->sync($user_ids, false);

    }

    // 取关用户
    public function unfollow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }

        $this->followings()->detach($user_ids);
    }

    // 是否关注
    public function isFollowing($user_id)
    {
        return $this->followings()->contains($user_id);
    }
}
