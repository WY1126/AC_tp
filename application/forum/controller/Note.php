<?php


namespace app\forum\controller;
use app\model\forforum\Note as NoteModel;
use think\Db;
use think\Request;
use app\common\measure\Upload;
use app\model\forforum\LikeNoteComment as LikeNoteCommentModel;
use app\model\forforum\LikeNoteReply as LikeNoteReplyModel;
use app\model\forforum\NoteComment as NoteCommentModel;
use app\model\forforum\NoteReply as NoteReplyModel;
use app\model\forforum\LikeNote as LikeNoteModel;

class Note
{

    /**
     * 搜索功能
     * @author 王瑶  2021-01-05  21:46:08
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function searchnote(Request $request)
    {
        global $uid;
        $search = $request->get('value');
        $tab = $request->get('tab');
        $page = $request->get('page');
        $uid = $request->param('uid');
        if($tab==0) {
            $notes = Db::name('note')
                ->whereLike('content','%'.$search.'%')
                ->order("id",'desc')->json(['image'])                   //排序，json输出图片信息
                ->alias('n')->join('user u','n.uid = u.id')     //连接user表获取用户昵称和头像地址
                ->join('noteType nt','n.tab = nt.id')
                ->field('n.*,u.nickname,u.avatar,nt.type')->paginate(5)         //查询5条数据
                ->each(                                                                 //each遍历数组
                    function ($item , $key){
                        //获取评论数
                        global $uid;
                        $item['commentnum'] = NoteCommentModel::where('nid',$item['id'])->count()
                            + NoteReplyModel::where('nid',$item['id'])->count();
                        //获取用户点赞状态
                        $item['status'] = LikeNoteModel::where(['nid'=>$item['id'],'uid'=> $uid])->count();
                        return $item;
                    });
        } else {
            $notes = Db::name('note')
                ->where('tab',$tab)
                ->whereLike('content',$search)
                ->order("id",'desc')->json(['image'])                   //排序，json输出图片信息
                ->alias('n')->join('user u','n.uid = u.id')     //连接user表获取用户昵称和头像地址
                ->join('noteType nt','n.tab = nt.id')
                ->field('n.*,u.nickname,u.avatar,nt.type')->paginate(5)         //查询5条数据
                ->each(                                                                 //each遍历数组
                    function ($item , $key){
                        //获取评论数
                        global $uid;
                        $item['commentnum'] = NoteCommentModel::where('nid',$item['id'])->count()
                            + NoteReplyModel::where('nid',$item['id'])->count();
                        //获取用户点赞状态
                        $item['status'] = LikeNoteModel::where(['nid'=>$item['id'],'uid'=> $uid])->count();
                        return $item;
                    });
        }
        if(((int)$page)>(($notes->toArray())['last_page'])) {
            return json([
                'error_code'    =>  0,
                "msg"           =>  '没有更多数据了'
            ]);
        }
        return json($notes);
    }
    /**分页查询-通过帖子类型分类查询
     * @author 王瑶  2021-01-05  00:27:25
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getnote(Request $request)
    {
        global $uid;
        $page = $request->param('page');
        $tab = $request->param('tab');
        $uid = $request->param('uid');
        //join查询-用户昵称和头像地址
        if($tab!=0){
            $notes = Db::name('note')
                ->order("id",'desc')->json(['image'])                   //排序，json输出图片信息
                ->where('tab',$tab)
                ->alias('n')->join('user u','n.uid = u.id')     //连接user表获取用户昵称和头像地址
                ->join('noteType nt','n.tab = nt.id')
                ->field('n.*,u.nickname,u.avatar,nt.type')->paginate(5)         //查询5条数据
                ->each(                                                                 //each遍历数组
                    function ($item , $key){
                        //获取评论数
                        global $uid;
                        $item['commentnum'] = NoteCommentModel::where('nid',$item['id'])->count()
                            + NoteReplyModel::where('nid',$item['id'])->count();
                        //获取用户点赞状态
                        $item['status'] = LikeNoteModel::where(['nid'=>$item['id'],'uid'=> $uid])->count();
                        return $item;
                    });
        } else {
            $notes = Db::name('note')
                ->order("id",'desc')->json(['image'])                   //排序，json输出图片信息
                ->alias('n')->join('user u','n.uid = u.id')     //连接user表获取用户昵称和头像地址
                ->join('noteType nt','n.tab = nt.id')
                ->field('n.*,u.nickname,u.avatar,nt.type')->paginate(5)         //查询5条数据
                ->each(                                                                 //each遍历数组
                    function ($item , $key){
                        //获取评论数
                        global $uid;
                        $item['commentnum'] = NoteCommentModel::where('nid',$item['id'])->count()
                            + NoteReplyModel::where('nid',$item['id'])->count();
                        //获取用户点赞状态
                        $item['status'] = LikeNoteModel::where(['nid'=>$item['id'],'uid'=> $uid])->count();
                        return $item;
                    });
        }
        $newsarray = $notes->toArray();
        if(((int)$page)>(($notes->toArray())['last_page'])) {
            return json([
                'error_code'    =>  0,
                "msg"           =>  '没有更多数据了'
            ]);
        }
        return json($notes);
    }
    /**
     * 发送帖子-初步
     * @param Request $request
     * @return \think\response\Json
     * @author 王瑶  2021-01-04  22:05:03
     */
    public function sendnote(Request $request)
    {
        $data = $request->post();
        $files = $request->file('images');
        $imgs = [];    $upload = new Upload();
        $upload->uploadimgs($files,$imgs);
        $data['image'] = $imgs;
        $data['create_time'] = time();
        //存储图片路径信息
        $info = new NoteModel();
        $result = $info->save($data);
        if($result) {
            return json([
                'error_code'        =>      1,
                'msg'               =>      '发布成功',
                'data'              =>      NoteModel::json(['image'])->where('id',$info['id'])->find()
            ]);
        } else {
            return json([
                'error_code'        =>      0,
                'msg'               =>      '请求失败',
            ]);
        }
    }
}