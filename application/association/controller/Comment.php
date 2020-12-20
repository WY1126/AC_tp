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
use app\model\forassociation\AsInReply as AsInReplyModel;
use app\model\forassociation\User as UserModel;
use app\model\forassociation\Testx as TestxModel;

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
        $infor = InformationModel::get($iid);
        $comments = $infor->asInComment;
        foreach ($comments as $key => $item)
        {
            $comment = AsInCommentModel::get($item['id']);
            $reply = $comment->asInReply;
            $comments[$key]['reply'] = $reply;
        }
        return json($comments);
    }
}