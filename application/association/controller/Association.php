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
        $count = AssociatonModel::count('aid');

        return json([
            'count'     =>      $count,
            'data'      =>      AssociatonModel::select(),
        ]);
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
        if(!$aid)
        {
            return json([
                'error_code'    =>      1,
                'msg'           =>      '无加入的社团',
            ]);
        }
        //whereIn直接匹配数组值
        $associations = AssociatonModel::whereIn('aid',$aid)->select();
        return json($associations);
    }

    /**通过ID获取社团信息
     * 2020.11.23 16:29     王瑶
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getassociation($aid)
    {
        $association = AssociatonModel::where('aid',$aid)->findOrEmpty();
        if(!$association)
        {
            return json([
                'error_code'        =>      1,
                'msg'               =>      '错误',
            ]);
        }
        return $association;
    }
    public function getanum($aid)
    {
        $num = AssociatorModel::where('aid',$aid)->count();
        if(!$num)
        {
            $num=0;
        }
        return $num;
    }
    //进入社团详情页，获取详细信息
    public function associationinfo(Request $request)
    {
        $aid = $request -> post('aid');
        //获取社团协会基本信息
        $association = $this->getassociation($aid);
        //获取社团人数、点赞数
        $num = AssociatorModel::where('aid',$aid)->count();
    }

}