<?php


namespace app\model\forassociation;


use think\Model;

class Section extends Model
{
    public function members()
    {
//        return $this->belongsToMany('Role', 'Access');
//        return $this->belongsToMany('Section','SectionMember');
        return $this->belongsToMany('Associator','SectionMember');
    }


}