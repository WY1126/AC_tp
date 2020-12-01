<?php


namespace app\home\controller;
use app\common\service\AssociatonModelService;
use app\model\forassociation\User as UserModel;
use think\Controller;
use think\Request;
use think\Validate;
use app\all\controller\Upload;
use think\facade\Config;

class User extends Controller
{

    //用户授权登陆
    public function signup(Request $request)
    {
        $data = $request->post();
        $errmsg = $this->validate($data, [
            'code|授权码' => 'require',
            'nickname|昵称' => 'require',
            'avatar|头像' => 'require'
        ]);
        if ($errmsg !== true) {
            return json($errmsg, 400);
        }
//        return json($data);
//        $url  = 'https://api.weixin.qq.com/sns/jscode2session?appid=wx19485a63db579f06&secret=4a8acf1e33d53101efc91d1d8a2be76a&js_code='.$data['code'].'&grant_type=authorization_code';
        $url  = 'https://api.weixin.qq.com/sns/jscode2session?appid='.Config::get('applet.appid').'&secret='.Config::get('applet.secret').'&js_code='.$data['code'].'&grant_type=authorization_code';
        $info = file_get_contents($url);//该函数用作发送get请求
//        return json($info);
        $in =  json_decode($info,true);
        $openid = $in['openid'];
        //存入数据库并返回给前段作为
        $result = UserModel::where('openid',$openid)->find();
        if(!$result)
        {
            $data['openid'] = $openid;
            unset($data['code']);
            $user = new UserModel();
            $result = $user->save($data);
            if($result) {
                $data['openid'] = md5($data['openid']);
                return json($openid);
            }
        }
        $result['openid'] = md5($result['openid']);
        return json($result);
//        return json($in);

    }
    /**用户注册接口
     * 2020.11.19   王瑶
     * 通常只存储用户名和密码作为唯一表示
     * @param Request $request
     * @return \think\response\Json

    public function signup(Request $request)
    {
        //验证用户名和密码，前段做就好
        $returndata = [];
        $username = $request -> post('username');
        $password = md5($request -> post('password'));
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
    }*/

    /**用户登录
     * 2020.11.19   王瑶
     * @param Request $request
     * @return \think\response\Json

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
    }  */

    /**用户修改个人信息
     * @param Request $request
     * @return \think\response\Json

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
    } */
}