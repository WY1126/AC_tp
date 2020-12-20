<?php


namespace app\model\forassociation;


use think\Model;

class User extends Model
{
    public function authority() {
        //hasOne 表示一对一关联，参数一表示附表，参数二外键，默认 user_id
        return $this->hasMany('Authority','uid','id');
    }
}