<?php


namespace app\model\forassociation;


use think\Model;

class AsInComment extends Model
{
    //关联回复表
    public function asInReply()
    {
        return $this->hasMany('AsInReply','comment_id','id');
    }

}