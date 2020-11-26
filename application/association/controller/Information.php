<?php


namespace app\association\controller;


use think\Request;
use app\model\forassociation\Associator as AssociatorModel;
use app\model\forassociation\Authority as AuthorityModel;
use app\common\measure\Upload;
use app\model\forassociation\Information as InformationModel;
class Information
{
    //检查发布人是否有发布权限
    public function checkuserauth(Request $request)
    {
        $data = [
            'aid'       =>      $request->post('aid'),
            'uid'       =>      $request->post('uid'),
            'code'      =>      $request->post('code'),
        ];
        $error_msg = [
            'error_code'     =>      0,
            'msg'            =>      '无权限操作',
        ];
        $userauth = AuthorityModel::where($data)->find();
        if(!$userauth) {
            return json($error_msg);
        }
        return json([
            'error_code'    =>      1,
            'msg'           =>      '允许发布资讯',
            'data'          =>      $userauth,
        ]);
    }
    //发布社团资讯
    public function sendinformation(Request $request)
    {
        $data = $request->post();
        $files = $request->file('images');
        //存图片路径信息
        $imgs = [];
        $upload = new Upload();
        $upload->uploadimgs($files,$imgs);




    }

}