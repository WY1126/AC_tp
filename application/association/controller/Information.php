<?php


namespace app\association\controller;


use think\Request;
use app\model\forassociation\Associator as AssociatorModel;
use app\model\forassociation\Authority as AuthorityModel;
use app\common\measure\Upload;
use app\model\forassociation\Information as InformationModel;
use app\model\forassociation\Association as AssociationModel;
use app\model\forassociation\LikeInformation as LikeInformationModel;
use app\model\forassociation\AsInComment as AsInCommentModel;
use app\model\forassociation\AsInReply as AsinreplyModel;

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
     * @author 王瑶  2020-12-03 21:04
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getnewinfor(Request $request)
    {
        $page = $request->post('page');
        $uid = $request->post('uid');
//        echo $uid;
//        return json($page);
//        die;
        $information = new InformationModel();
        $news = InformationModel::order("id",'desc')->json(['image'])->paginate(5);
        $newsarray = $news->toArray();
        if(((int)$page)>(($news->toArray())['last_page'])) {
            return json([
                'error_code'    =>  0,
                "msg"           =>  '没有更多数据了'
            ]);
        }
        //获取社员头像地址，名称,点赞数，评论数，点赞状态
        foreach ($newsarray['data'] as $key => $item)
        {
            $avatarurl = AssociationModel::where('id',$item['aid'])->value('avatar');
            $name = AssociationModel::where('id',$item['aid'])->value('shortname');
            $status = LikeInformationModel::where(['iid'=>$item['id'],'uid'=>$uid])->value('status');
            $commentnum = AsInCommentModel::where('iid',$item['id'])->count();
            $replynum = AsinreplyModel::where('iid',$item['id'])->count();
            $newsarray['data'][$key]['avatarurl'] = $avatarurl;
            $newsarray['data'][$key]['shortname'] = $name;
            $newsarray['data'][$key]['status'] = $status;
            $newsarray['data'][$key]['commentnum'] = $commentnum+$replynum;
        }
        return json($newsarray);
    }
    /**社团资讯点赞功能
     * @param Request $request
     * @return \think\response\Json
     */
    public function likeinformation(Request $request)
    {
        $iid = $request->post('iid');
        $uid = $request->post('uid');

        //判断是否已存在点赞表
        $result = LikeInformationModel::where([
            'iid'       =>      $iid,
            'uid'       =>      $uid,
        ])->find();

        if(!$result){
            $likeinfor = new LikeInformationModel();

            $likeinfor->save([
                'iid'   =>  $iid,
                'uid'   =>  $uid,
                'create_time'   =>  time()
            ]);
//            if($f) {
                $result = LikeInformationModel::where([
                    'iid'        =>      $likeinfor['iid'],
                    'uid'        =>      $likeinfor['uid']
                ])->find();
//            }
        }

        $temp = InformationModel::get($result['iid']);
//        return json($temp);
//        die();
        //点赞
        if($result['status']==0) {
            $temp->likenum += 1;
        }
        else {
            $temp->likenum -= 1;
        }
        $temp->save();

        $msg = ['取消点赞','点赞成功'];
        //修改status状态
        $result->status += 1;     $result->status %= 2;
        $result -> save();
//        return $temp->likenum;
        return json([
            'likenum'       =>      $temp->likenum,
            'error_msg'     =>      $msg[$result->status],
            'status'        =>      $result->status
        ]);
    }
}