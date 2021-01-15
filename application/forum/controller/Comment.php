<?php


namespace app\forum\controller;
use app\model\forforum\NoteComment as NoteCommentModel;
use app\model\forforum\NoteReply as NoteReplyModel;
use app\model\forassociation\User as UserMOdel;
use app\model\forforum\Note as NoteModel;
use app\model\forforum\LikeNoteReply as LikeNoteReplyModel;
use app\model\forforum\LikeNoteComment as LikeNoteCommentModel;
use think\Db;
use think\Request;
use think\response\Json;

class Comment
{
    /**
     * 发送评论
     * @param Request $request
     * @return Json
     * @author 王瑶  2021-01-14  16:00:11
     */
    public function sendcomment(Request $request)
    {
        $data = $request->param();
        $notecomment = new NoteCommentModel();
        $result = $notecomment->save($data);
        if($result) {
            $comments = NoteCommentModel::get($notecomment['id']);
            $info = UserMOdel::get($notecomment['uid']);
//            $nickname = UserMOdel::where('id',$notecomment['uid'])->value('nickname');
//            $avatar = UserMOdel::where('id',$notecomment['uid'])->value( 'avatar');
            $comments['nickname'] = $info['nickname'];
            $comments['avatar'] = $info['avatar'];
            $comments['reply'] = [];
            return json($comments);
        } else {
            return json([
               'error_code' =>      1,
               'msg'        =>      '插入数据失败'
            ]);
        }
    }

    /**
     * 发送帖子回复
     * @author 王瑶  2021-01-14  17:09:06
     * @param Request $request
     * @return Json
     */
    public function sendreply(Request $request)
    {
        $data = $request -> param();
        $notereply = new NoteReplyModel();
        $result = $notereply -> save($data);
        if($result) {
            $replys = NoteReplyModel::get($notereply['id']);
            $userInfo = UserMOdel::get($notereply['uid']);
            $toUserInfo = UserMOdel::get($notereply['to_uid']);
            $replys['nickname'] = $userInfo['nickname'];
            $replys['avatar'] = $userInfo['avatar'];
            $replys['to_nickname'] = $toUserInfo['nickname'];
            return  json($replys);
        } else {
            return json([
                'error_code' =>   1,
                'msg'        =>     '插入数据失败'
            ]);
        }
    }

    /**
     * 获取帖子评论和回复
     * @author 王瑶  2021-01-14  19:59:09
     * @param Request $request
     * @return Json
     * @throws \think\exception\DbException
     */
    public function getcomment(Request $request)
    {
        global $uid;
        $nid = $request -> get('nid');
        $uid = $request -> get('uid');
        $comments = Db::name('noteComment')
            -> where('nid',$nid) -> order('create_time','desc')
            -> alias('nc') -> join('user u','nc.uid = u.id')
            -> field('nc.*,u.nickname,u.avatar')->paginate(20)
            ->each (
                function ($item , $key) {              //获取点赞状态、评论数和回复
                    global $uid;
                    //获取请求用户的评论点赞状态
                    $status = LikeNoteCommentModel::where(['uid'=>$uid,'cid'=>$item['id']])->value('status');
                    $item['status'] = $status;
                    //评论的回复内容
                    $reply = Db::name('noteReply')
                        -> where('comment_id',$item['id']) -> order('create_time','desc')
                        -> alias('nr') -> join('user u','nr.uid = u.id')
                        -> field('nr.*,u.nickname,u.avatar') ->select();
                    //获取当前用户的点赞状态和回复对象的昵称
                    foreach ($reply as $key => $ite) {
                        global $uid;
                        $reply[$key]['status'] = LikeNoteReplyModel::where(['uid'=>$uid,'rid'=>$ite['id']])->value('status');
                        $reply[$key]['to_nickname'] = UserMOdel::where('id',$ite['to_uid'])->value('nickname');
                    }
                    $item['reply'] = $reply;
                    return $item;
                }
            );
        return json($comments);
    }

    /**
     * 帖子评论、回复的点赞
     * @author 王瑶  2021-01-14  21:54:23
     * @param Request $request
     * @return Json
     */
    public function dolike(Request $request)
    {
        $type = $request->get('type');
        $id = $request->get('id');
        $uid = $request->get('uid');
        $create_time = $request->get('create_time');
        if(!$type) {
            //回复
            //判断是否已存在点赞表
            $result = LikeNoteReplyModel::where([
                'rid'    =>      $id,
                'uid'   =>      $uid
            ])->find();
            if(!$result){
                $reply = new LikeNoteReplyModel();
                $reply->save([
                    'rid'    =>      $id,
                    'uid'   =>      $uid,
                    'create_time'   =>  $create_time,
                ]);
                $result = LikeNoteReplyModel::where([
                    'rid'   =>      $reply['rid'],
                    'uid'   =>      $reply['uid'],
                ])->find();
            }
            $temp = NoteReplyModel::where('id',$result['rid'])->find();
            //        //点赞
            if($result['status']==0) {
                $temp->likenum += 1;
            }
            else {
                $temp->likenum -= 1;
            }
            $temp->save();

            $msg = ['c取消点赞','c点赞成功'];
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
            $result = LikeNoteCommentModel::where([
                'cid'    =>      $id,
                'uid'   =>      $uid
            ])->find();
            if(!$result){
                $reply = new LikeNoteCommentModel();
                $reply->save([
                    'cid'    =>      $id,
                    'uid'   =>      $uid,
                    'create_time'   =>  $create_time,
                ]);
                $result = LikeNoteCommentModel::where([
                    'cid'   =>      $reply['cid'],
                    'uid'   =>      $reply['uid'],
                ])->find();
            }
            $temp = NoteCommentModel::where('id',$result['cid'])->find();
            //        //点赞
            if($result['status']==0) {
                $temp->likenum += 1;
            }
            else {
                $temp->likenum -= 1;
            }
            $temp->save();
            $msg = ['r取消点赞','r点赞成功'];
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