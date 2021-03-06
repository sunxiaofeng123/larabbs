<?php

namespace App\Models;

class Topic extends Model
{
    protected $fillable = ['title', 'body', 'category_id', 'excerpt', 'slug'];

    //link
    public function link($params = [])
    {
        return route('topics.show', array_merge([$this->id, $this->slug], $params));
    }

    //关联分类表一对一关系
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    //关联用户表一对一关系
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 关联回复表一对多关系
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    /**
     * @param $query
     * @param $order
     * @return 查询构建器，关于排序, 本地作用域,调用时不用加scope前缀
     */
    public function scopeWithOrder($query, $order)
    {
        //不同的排序，使用不同的数据读取逻辑
        switch ($order) {
            case 'recent':
                $query->recent();
                break;
            default:
                $query->recentReplied();
                break;
        }

        //防止N+1
        return $query->with('user', 'category');
    }

    //最新回复
    public function scopeRecentReplied($query)
    {
        return $query->orderBy('updated_at', 'desc');
    }

    //最新发布
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }


}
