<?php


namespace app\association\controller;


use app\home\controller\User;
use think\Controller;
use think\Request;
use app\model\forassociation\Associator as AssociatorModel;
use app\model\forassociation\User as UserModel;
use app\model\forassociation\Authority as AuthorityModel;
use think\Response;

class Authorityuser
{
    /**获取社团所有用户的权限和权限个数
     * 2020.11.25 21：36     王瑶
     * @param Request $request
     * @return \think\response\Json
     */
    public function getalluserauth(Request $request)
    {
        //获取全部管理员信息
        $aid = $request->post('aid');
//        return $aid;
        $userid = AssociatorModel::where('aid',$aid)->column('uid');
//        return json($userid);
        $userinfo = UserModel::whereIn('id',$userid)->select();
//        return json($userinfo);
        //循环一对多查询用户的权限信息，并统计获得的权限数
        foreach ($userinfo as $key => $value)
        {
            $auth = $value->authority;
            $userinfo[$key]['authnum'] = count($auth);
        }
        return json($userinfo);
    }

}