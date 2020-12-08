<?php


namespace app\association\controller;


use think\Request;
use app\model\forassociation\Associator as AssociatorModel;
use app\model\forassociation\Authority as AuthorityModel;
use app\common\measure\Upload;
use app\model\forassociation\Information as InformationModel;
use app\model\forassociation\Association as AssociationModel;

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
//        print_r($request->file('images'));
//        die;
        $data = $request->post();
        $files = $request->file('images');
        $imgs = [];
        $upload = new Upload();
        $upload->uploadimgs($files,$imgs);
        $data['image'] = $imgs;
        //存图片路径信息
        $info = new InformationModel();
        $data['create_time'] = time();
        $result = $info->save($data);
        if($result) {
            return json([
                'error_code'        =>      1,
                'msg'               =>      '发布成功',
                'data'              =>      InformationModel::json(['image'])->where('id',$info['id'])->find(),
            ]);
        } else {
            return json([
                'error_code'        =>      0,
                'msg'               =>      '发布失败，请稍后再试',
            ]);
        }
    }

    /**查询5条最新社团资讯
     * 2020-12-03 21:04 wangyao
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getnewinfor(Request $request)
    {
        $page = $request->get('page');

        $information = new InformationModel();
        $news = InformationModel::order("id",'desc')->json(['image'])->paginate(5);
        $newsarray = $news->toArray();
        if(((int)$page)>(($news->toArray())['last_page'])) {
            return json([
                'error_code'    =>  0,
                "msg"           =>  '没有更多数据了'
            ]);
        }
        //获取社员头像地址
        foreach ($newsarray['data'] as $key => $item)
        {
            $avatarurl = AssociationModel::where('id',$item['aid'])->value('avatar');
            $name = AssociationModel::where('id',$item['aid'])->value('shortname');
            $newsarray['data'][$key]['avatarurl'] = $avatarurl;
            $newsarray['data'][$key]['shortname'] = $name;
        }
        return json($newsarray);
    }

}