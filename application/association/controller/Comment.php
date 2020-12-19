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

        $data['create_time'] = time();
//        var_dump( $data);
        $acincomment = new AsInCommentModel();
        $flag = $acincomment->save($data);
//        return json($flag);
        if($flag) {
            return json(AsInCommentModel::get($acincomment['id']));
        }

    }
    public function sendreply(Request $request)
    {
        $data = $request->post();
        $data['create_time'] = time();
        $acinreply = new AsInReplyModel();
        $flag = $acinreply->save($data);
        if($flag) {
            return json($acinreply);
        }

    }
    //获取社团资讯评论内容2020-12-19  23:32   wangyao
    public function getcomment(Request $request)
    {
        $iid = $request->post('iid');
        $commentdata = AsInCommentModel::where('iid',$iid)->select();
        //获取评论下的回复
        foreach ($commentdata as $key => $item)
        {
            $comment_reply = AsInReplyModel::where('comment_id',$item['id'])->select();
            $commentdata[$key]['comment_reply'] = $comment_reply;
        }

            return json($commentdata);
    }

}