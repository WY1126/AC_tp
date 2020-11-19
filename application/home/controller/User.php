<?php


namespace app\home\controller;
use app\common\service\AssociatonModelService;
use app\model\forassociation\User as UserModel;
use think\Controller;
use think\Request;
use think\Validate;

class User extends Controller
{
    /**用户注册接口
     * 2020-11-19 wangyao
     *
     *
     *
     */
    public function signup(Request $request)
    {
        //验证用户名和密码，前段做就好
//        $validator = new Validate([
//            'username'      =>      'require|max:5',
//            'password'      =>      'require',
//        ]);
        $username = $request -> post('username');
        $password = md5($request -> post('password'));
        $returndata = [];
        $user = UserModel::get(['username' => $username]);
        if(!empty($user)) {
            $returndata['error_code']     =   0;
            $returndata['msg']            = '用户名已存在';
            return json($returndata);
        }

        $user = new UserModel();
        $user -> data(['username'   => $username,
            'password'  => $password,
        ]);
        $result = $user->save();
        if($result) {
            $returndata['error_code']       =     1;
            $returndata['msg']              = '注册失败';
            return $returndata;
        } else {
            return $result;
        }
    }

}