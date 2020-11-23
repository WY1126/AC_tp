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

    /**获取我的社团
     * 2020.11.23 王瑶
     * @param Request $request
     * @return \think\response\Json
     */
    public function getmyassociation(Request $request)
    {
        $uid = $request -> post('uid');
        //column获取某一列的值
        $aid = AssociatorModel::where('uid',$uid)->column('aid');
//        return json($aid);
        if(!$aid)
        {
            return json([
                'error_code'    =>      1,
                'msg'           =>      '无加入的社团',
            ]);
        }
        $association = AssociatonModel::whereIn('aid',$aid)->select();
        return json($association);
    }

}