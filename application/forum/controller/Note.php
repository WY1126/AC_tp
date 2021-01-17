<?php


namespace app\forum\controller;
use app\model\forforum\Note as NoteModel;
use think\Db;
use think\Image;
use think\Request;
use app\common\measure\Upload;
use app\model\forforum\LikeNoteComment as LikeNoteCommentModel;
use app\model\forforum\LikeNoteReply as LikeNoteReplyModel;
use app\model\forforum\NoteComment as NoteCommentModel;
use app\model\forforum\NoteReply as NoteReplyModel;
use app\model\forforum\LikeNote as LikeNoteModel;
use think\Config;

class Note
{
    //图片压缩时 图片尺寸比例
    public $x = 0.6;
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
        if($tab!=0) {
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

    //校验发送公告用户的权限
    public function checkuser($uid)
    {
        $result = Db::name('userAdmin')->where('id',$uid)->find();
        if($result!=null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送帖子-初步
     * @param Request $request
     * @return \think\response\Json
     * @author 王瑶  2021-01-04  22:05:03
     */
    public function sendnote(Request $request)
    {
//        print_r( $request->post('image'));
//        die;
        $uid = $request->post('uid');

        $data = $request->post();
        $image = json_decode($request->post('image'),true);
        if($request->post('tab')==8){
            if(!$this->checkuser($uid)) {
                //json转数组
//                $image = json_decode($request->post('image'),true);
                foreach ($image as $key => $item) {
                    $this->delFile(\think\facade\Config::get('rootaddress.imagerootaddress')."\\".$item);
                }
                return json([
                    'error_code' => 0,
                    'msg'   =>  '管理员操作'
                ]);
            }
        }
//        $a = ['s','s','d'];
//        return json($data);
//        die;
        //存储图片路径信息
//        if(count($image)==1){
//            $data['']
//        }
        $data['imglen'] = count($image);
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
    public function sendnoteb(Request $request)
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

    public function uploadimg(Request $request)
    {
        $file = $request->file('file');
        // 移动到框架应用根目录/uploads/ 目录下  验证大小和后缀
        $info = $file->move( '../uploads');
        if($info){
//            $tempname = '@'.$info->getFilename();
//            $tempSaveName = str_replace("\\","/",$info->getSaveName());
//            $tempSaveName = str_replace($info->getFilename(),$tempname,$tempSaveName);
//            $image = Image::open($info);
//            $image->thumb(200,200)->save('../uploads/'.$tempSaveName);
            //向数组添加图片路径
            //反斜杠替换
//            $getSaveName=str_replace("\\","/",$info->getSaveName());
            $getSaveName = $info->getSaveName();
//            array_push($imgs,$tempSaveName);
//            return $tempSaveName;
            //图片大于80KB执行压缩程序
            if($info->getSize()>81920){
                $source_path = '..\\uploads\\'.$getSaveName;
                $img_info = getimagesize($source_path);
                //        print_r($img_info[3]);
                //return json($img_info);
                //        print_r($img_info[0]*0.2);
                //        die;
                $target_path = '../uploads';
                $imgWidth = (int)$img_info[0]*$this->x;    $imgHeight = (int)$img_info[1]*$this->x;
                $saveName = $this->resize_image($source_path,$target_path,$imgWidth,$imgHeight);
                $path = \think\facade\Config::get('rootaddress.imagerootaddress')."\\".$getSaveName;
                $this->delFile($path);
                return str_replace("\\","/",$saveName);
            }
            return str_replace("\\","/",$getSaveName);
        }
        else{
            // 上传失败获取错误信息
            return $file->getError();
        }
    }

    /**
     * 按照指定的尺寸压缩图片
     * @param $source_path
     * @param $target_path
     * @param $imgWidth
     * @param $imgHeight
     * @return bool|string
     */
    function resize_image($source_path,$target_path,$imgWidth,$imgHeight)
    {
        $source_info = getimagesize($source_path);
        $source_mime = $source_info['mime'];
        switch ($source_mime)
        {
            case 'image/gif':
                $source_image = imagecreatefromgif($source_path);
                break;
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($source_path);
                break;
            case 'image/png':
                $source_image = imagecreatefrompng($source_path);
                break;
            default:
                return false;
                break;
        }
        $target_image     = imagecreatetruecolor((int)$imgWidth, (int)$imgHeight); //创建一个彩色的底图
        imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $imgWidth, $imgHeight, $source_info[0], $source_info[1]);
        //保存图片到本地
        $dir = $target_path. '/'. date("Ymd") . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777);
        }
        $temp =date("YmdHis").uniqid().'.jpg';
        $fileName = $dir.$temp;
        if(!imagejpeg($target_image,'./'.$fileName)){
            $fileName = '';
        }
        imagedestroy($target_image);
        return  date("Ymd") . '/'.$temp;
    }

    //删除文件  $path为绝对路径
    public function delFile($path)
    {
        $url=iconv('utf-8','gbk',$path);
        if(PATH_SEPARATOR == ':'){ //linux
            unlink($path);
        }else{  //Windows
            unlink($url);
        }
    }
    /**点赞帖子
     * @author 王瑶  2021-01-14  09:55:13
     * @param Request $request
     * @return \think\response\Json
     */
    public function likenote(Request $request)
    {
        $nid = $request->post('nid');
        $uid = $request->post('uid');
        //判断是否已存在点赞表
        $result = LikeNoteModel::where([
            'nid'       =>      $nid,
            'uid'       =>      $uid,
        ])->find();


        if(!$result){
            $likeinfor = new LikeNoteModel();

            $likeinfor->save([
                'nid'   =>  $nid,
                'uid'   =>  $uid,
                'create_time'   =>  time()
            ]);
//            if($f) {
            $result = LikeNoteModel::where([
                'nid'        =>      $likeinfor['nid'],
                'uid'        =>      $likeinfor['uid']
            ])->find();
//            }
        }
        $temp = NoteModel::get($result['nid']);
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
    //检查发送
}