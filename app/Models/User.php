<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Auth;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable {
        notify as protected laravelNotify;
    }

    use HasRoles;

    public function notify($instance)
    {
        //如果是自己，不通知
        if ($this->id == Auth::id()) {
            return;
        }

        $this->increment('notification_count');
        $this->laravelNotify($instance);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','introduction', 'avatar'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    //关联回复表，一堆多的关系
     public function replies()
     {
         return $this->hasMany(Reply::class);
     }

    /**
     *
     *
     */
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    public function isAuthorOf($model)
    {
        return $this->id == $model->user_id;
    }

    public function markAsRead()
    {
        $this->notification_count = 0;
        $this->save();

        $this->unreadNotifications->markAsRead();
    }

    //密码修改器
    public function setPasswordAttribute($value)
    {
        //如果值的长度等于60 即认为是已经做过加密的情况
        if (strlen($value) != 60) {
            $value = bcrypt($value);
        }

        $this->attributes['password'] = $value;
    }

    //头像修改器
    public function setAvatarAttribute($path)
    {
        //如果不是http的子串开头那就是后台上传的，需要补全URL
        if (! starts_with($path, 'http')) {
            $path = config('app.url')."/uploads/images/avatars/$path";
        }

        $this->attributes['avatar'] = $path;
    }
}
