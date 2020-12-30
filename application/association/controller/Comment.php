<?php


namespace app\association\controller;


use app\model\forassociation\LikeComment;
use app\model\forassociation\LikeReply;
use think\Request;
use app\model\forassociation\Associator as AssociatorModel;
use app\model\forassociation\Authority as AuthorityModel;
use app\common\measure\Upload;
use app\model\forassociation\Information as InformationModel;
use app\model\forassociation\Association as AssociationModel;
use app\model\forassociation\LikeInformation as LikeInformationModel;
use app\model\forassociation\AsInComment as AsInCommentModel;
use app\model\forassociation\AsInReply as AsInReplyModel;
use app\model\forassociation\User as UserModel;
use app\model\forassociation\Testx as TestxModel;
use app\model\forassociation\LikeComment as LikeCommentModel;
use app\model\forassociation\LikeReply as LikeReplyModel;

class Comment
{
    /**2020-12-18   wangyao
     * 发送社团资讯评论
     * @param Request $request
     * @return \think\response\Json
     */
    public function sendcomment(Request $request)
    {
        $data = $request->post();
        $avatarurl = UserModel::where('id',$data['uid'])->value('avatar');
        $data['create_time'] = time();
        $data['avatarurl']   = $avatarurl;
//        var_dump( $data);
        $acincomment = new AsInCommentModel();
        $flag = $acincomment->save($data);
//        return json($flag);
        if($flag) {
            return json(AsInCommentModel::get($acincomment['id']));
        }
    }
    //发送回复
    public function sendreply(Request $request)
    {
        $data = $request->post();
        $avatarurl = UserModel::where('id',$data['uid'])->value('avatar');
        $data['avatarurl']   = $avatarurl;
        $data['create_time'] = time();
        $acinreply = new AsInReplyModel();
        $flag = $acinreply->save($data);
        if($flag) {
            return json($acinreply);
        }
    }
    /** 获取社团资讯评论内容2020-12-19  23:32   wangyao
     * @param Request $request
     * @return \think\response\Json
     *
     * 基础评论前端要显示的数据
     * -用户头像地址
     * -用户昵称
     * -发布时间
     * -点赞数（浏览用户的点赞状态）
     * -评论内容
     * -回复：
     * --头像地址
     * --昵称
     * --事件
     * --点赞数（浏览用户的点赞状态）
     * --被回复对象昵称
     */
    public function getcomment(Request $request)
    {
        $iid = $request->post('iid');
        $uid = $request->post('uid');
        $infor = InformationModel::get($iid);
        $comments = $infor->asInComment;
        foreach ($comments as $key => $item)
        {
            $requestdata = [
                'uid'   =>  $uid,
                'cid'    =>  $item['id']
            ];
            $comment = AsInCommentModel::where('id',$item['id'])->find();
            $likecommentnum = AsInCommentModel::where('id',$item['id'])->value('likenum');
            $commentstatus  = LikeCommentModel::where($requestdata)->value('status');
            $comments[$key]['likenum'] = $likecommentnum;
            $comments[$key]['status'] = $commentstatus;
            $reply = $comment->asInReply;
            //查找回复的点赞状态和点赞数

            foreach ($reply as $ke => $ite)
            {
                $requestdata = [
                    'uid'   =>  $uid,
                    'rid'   =>  $ite['id']
                ];
                $likereplynum = AsInReplyModel::where('id',$ite['id'])->value('likenum');
                $status =  LikeReplyModel::where($requestdata)->value('status');
                if($status===null)      $status=0;
                $reply[$ke]['likenum'] = $likereplynum;
                $reply[$ke]['status'] = $status;
            }
            $comments[$key]['reply'] = $reply;
        }
        return json($comments);
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @author 王瑶  2020-12-30  22:04:34
     */
    public function good(Request $request)
    {
        $type = $request->post('type');
        $id = $request->post('id');
        $uid = $request->post('uid');
        if($type){
            //回复
            //判断是否已存在点赞表
            $result = LikeReplyModel::where([
                'rid'    =>      $id,
                'uid'   =>      $uid
            ])->find();
            if(!$result){
                $reply = new LikeReplyModel();
                $reply->save([
                    'rid'    =>      $id,
                    'uid'   =>      $uid,
                    'create_time'   =>  time(),
                ]);
                $result = LikeReply::where([
                    'rid'   =>      $reply['rid'],
                    'uid'   =>      $reply['uid'],
                ])->find();
            }
            $temp = AsInReplyModel::where('id',$result['rid'])->find();
            //        //点赞
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
        } else {
            //评论
            //判断是否已存在点赞表
            $result = LikeCommentModel::where([
                'cid'    =>      $id,
                'uid'   =>      $uid
            ])->find();
            if(!$result){
                $reply = new LikeComment();
                $reply->save([
                    'cid'    =>      $id,
                    'uid'   =>      $uid,
                    'create_time'   =>  time(),
                ]);
                $result = LikeCommentModel::where([
                    'cid'   =>      $reply['cid'],
                    'uid'   =>      $reply['uid'],
                ])->find();
            }
            $temp = AsInCommentModel::where('id',$result['cid'])->find();
            //        //点赞
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
}