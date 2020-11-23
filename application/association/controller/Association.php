<?php


namespace app\association\controller;
use app\model\forassociation\Association as AssociatonModel;
use think\Request;
use app\model\forassociation\Associator as AssociatorModel;

class Association
{
    /**获取全部社团协会接口
     * 2020.11.19   王瑶
     * @return \think\response\Json
     */
    public function getallassociation()
    {
        return json(AssociatonModel::select());
    }
    public function getmyassociation(Request $request)
    {
        $uid = $request -> post('uid');
//        return json($uid);
        $aid = AssociatorModel::where('uid',$uid)->value('aid');
        return json($aid);
//        return json(AssociatonModel::hasWhere('aid',$aid)->find());
    }

}