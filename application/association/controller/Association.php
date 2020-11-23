<?php


namespace app\association\controller;
use app\model\forassociation\Association as AssociatonModel;
use think\Request;
use app\model\forassociation\Associator as AssociatorModel;

class Association
{
    /**
     * @return \think\response\Json
     */
    public function getallassociation()
    {
        return json(AssociatonModel::select());
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getmyassociation(Request $request)
    {

        $uid = $request -> post('uid');
        //column查询某一列的值
        $aid = AssociatorModel::where('uid',$uid)->column('aid');
        if($aid)
        {
            //        return json($aid);
            $myassociation = AssociatonModel::whereIn('aid',$aid)->selectOrFail();
            return json($myassociation);
        }
        else
        {
            return json([
                'error_code'    =>      '0',
                'msg'           =>      '用户无加入社团'
            ]);
        }
    }

}