<?php


namespace app\model\forassociation;


use think\Model;

class Information extends Model
{
    //关联评论表
    public function asInComment()
    {
        return $this->hasMany('AsInComment','iid','id');
    }

}