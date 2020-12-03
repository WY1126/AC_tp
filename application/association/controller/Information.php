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
        $info = new InformationModel();
        $data['image'] = $imgs;
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

//        $list = User::where('status',1)->paginate(10);
        $information = new InformationModel();
        $news = InformationModel::order("id",'desc')->paginate(5);
        if($page>($news->toArray())['per_page']) {
            return json([
                'error_code'    =>  0,
                "msg"           =>  '没有更多数据了'
            ]);
        }
//        $news = json_decode(($news),true);
//        return $news['data']
//        var_dump($news);
//        $news = $news->toArray();
        return json($news);
    }

}