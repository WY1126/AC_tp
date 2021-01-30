<?php


namespace app\home\controller;
use app\model\forassociation\User as UserModel;
use app\model\fornotify\Notify as NotifyModel;
use app\model\forforum\Note as NoteModel;
use app\model\forforum\NoteReply as NoteReplyModel;
use app\model\forforum\NoteComment as NoteCommentModel;
use think\Db;
use think\Exception;
use think\Request;

class Notify
{
    /**
     * 创建消息
     * @author 王瑶  2021-01-26  19:48:32
     * note_id 167
     * uid 14
     * sender_type 2
     * type 2
     * target_id 167
     * target_type 1
     * action 1
     * content ''
     * to_uid 12
     * is_read 0
     * create_time 1614567892
     */
    public function createnotify(Request $request)
    {
        //创建点赞消息
        $data = $request->param();
        $requestdata = [
          'note_id' =>  $data['note_id'],
          'uid'     =>  $data['uid'],
          'type'    =>  $data['type'],
          'target_type' =>  $data['target_type'],
          'target_id'   =>  $data['target_id'],
            'action'    =>  $data['action'],
            'to_uid'    =>  $data['to_uid'],
            'is_read'   =>  0,
            'content'   =>  $data['content'],
        ];
        if(NotifyModel::where($requestdata)->find()) {
            return json([
                'error_code'    =>  '3',
                'msg'           =>  '已有消息',
            ]);
        }
        $notify = new NotifyModel();
        if ( $data['uid'] != $data['to_uid']) {
            $result = $notify->save($data);
            if($result) {
                return json([
                    'error_code'    =>  '0',
                    'msg'           =>  '发送成功',
                    'data'          =>  ($notify),
                ]);
            } else {
                return json([
                    'error_code' => 1,
                    'msg'        => '创建消息失败！'
                ]);
            }
        } else {
            return json([
                'error_code' => 2,
                'msg'       =>  '不创建消息',
            ]);
        }
    }
//    查询消息分三类查询：点赞消息、评论消息、回复消息。
    public function scannotify(Request $request)
    {
        $to_uid = $request->get('to_uid');
        //①查询点赞消息
        $notifys = Db::name('notify')
            -> where(['to_uid'=>$to_uid,'is_read'=>0]) ->order('create_time','desc')
            -> alias('ny') -> join('user u','ny.uid = u.id')
            -> field('ny.*,u.nickname,u.avatar') -> select();
        foreach ($notifys as $key => $item)
        {
            switch ($item['action']) {
                case '1':  //点赞
                    $notifys[$key]['content'] = $item['target_type'] == '1'? '赞👍了你的帖子':($item['target_type'] == '2' ? '赞👍了你的评论':
                        '赞👍了你的回复');
                    break;
                case '2':    //评论
                    break;
                case '3':   //回复
                    break;
                default :
                    break;
            }
            $notifys[$key]['msg'] = $this->actionmsg($item['target_type'],$item['target_id']);
        }
        return json($notifys);
    }

    /**
     * 获取用户操作对象的文字内容
     * @author 王瑶  2021-01-27  10:20:10
     * @param $target_type
     * @param $target_id
     * @return mixed|string
     */
    public function actionmsg($target_type,$target_id)
    {
        $msg = '';
        switch ($target_type){
            case '1':   //帖子
                $msg = NoteModel::where('id',$target_id) -> value('content');
                if($msg == ''||$msg == null)
                $msg = '(点击前往)';
                break;
            case '2':   //评论
                $msg = NoteCommentModel::where('id',$target_id) -> value('content');
                break;
            case '3':   //回复
                $msg = NoteReplyModel::where('id',$target_id) -> value('content');
                break;
            default :
                break;
        }
        return $msg;
    }
}