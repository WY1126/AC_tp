<?php


namespace app\home\controller;
use app\common\service\AssociatonModelService;
use app\model\forassociation\User as UserModel;
use think\Controller;
use think\Request;
use think\Validate;
use app\all\controller\Upload;

class User extends Controller
{
    /**用户注册接口
     * 2020.11.19   王瑶
     * 通常只存储用户名和密码作为唯一表示
     * @param Request $request
     * @return \think\response\Json
     */
    public function signup(Request $request)
    {
        //验证用户名和密码，前段做就好
        $username = $request -> post('username');
        $password = md5($request -> post('password'));
//        $file     = $request -> file('avatarimage');  $avatarurl = '';
//        if(!$file) {
//            $avatarurl = uploadavatar($file);
//        } else {
//            $avatarurl = $request -> post('avatarurl');
//        }
        $returndata = [];
        $user = UserModel::get(['username' => $username]);
        if(!empty($user)) {
            $returndata['error_code']     =   0;
            $returndata['msg']            = '用户名已存在';
            return json($returndata);
        }
        $user = new UserModel();
        $temp = $request ->param();     $temp['nickname'] = $temp['username'].'a';
        //md5加密密码
        $temp['password'] = md5($temp['password']);
        $user -> data($temp);
        $result = $user->save();
        if(!$result) {
            $returndata['error_code']       =     1;
            $returndata['msg']              = '注册失败';
            return json($returndata);
        } else {
            return json($user);
        }
    }

    /**用户登录
     * 2020.11.19   王瑶
     * @param Request $request
     * @return \think\response\Json
     */
    public function signin(Request $request)
    {
        $username = $request->post('username');
        $password = $request->post('password');
        $user = UserModel::get(['username' => $username]);
        if(empty($user) || ($password != $user->password))
        {
            return json([
                'error_code'    =>      0,
                'msg'           =>      '用户名或密码错误',
            ]);
        } else {
            return json([
                'error_code'    =>      1,
                'msg'           =>      '登录成功',
            ]);
        }
    }

    /**用户修改个人信息
     * @param Request $request
     * @return \think\response\Json
     */
    public function changeuserinfo(Request $request)
    {
        $user = UserModel::where('username',$request->username)->find();
        if(!$user) {
            return json([
                'error_code'    =>      0,
                'msg'           =>      '请求修改失败！',
            ]);
        }
        $user -> data($request->param());
        $result = $user -> save();
        if(!$result) {
            return json([
                'error_code'    =>      0,
                'msg'           =>      '请求修改失败！',
            ]);
        }
        else {
            return json($user);
        }
    }


}