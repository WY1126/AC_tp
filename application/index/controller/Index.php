<?php
namespace app\index\controller;

use think\Config;
use think\Controller;
use think\Db;
use app\model\forassociation\User as Usermodel;
use app\model\forassociation\Association as Associationmodel;
use app\model\forassociation\Section as Sectionmodel;
use app\model\forassociation\Authority as Authoritymodel;
use app\model\forassociation\SectionMember as Sectionmembermodel;
use app\model\forassociation\Information as Informationmodel;
use app\model\forassociation\AsInComment as AsInCommentmodel;
use app\model\forassociation\AsInReply as AsInReplymodel;
use app\model\forassociation\Test as TestModel;
use think\Request;
use app\common\measure\Upload;


class Index extends Controller
{
    public function getnotify()
    {
        return json(Db::name('notify')->select());
    }
    public function getme(Request $request)
    {
//        return 'sa';
//        die;
        $info = $request->post();
        return json($info);
    }
    public function getjwt()
    {
        return \think\facade\Config::get('jwt.key');
    }
    public function index()
    {
        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V5.1<br/><span style="font-size:30px">12载初心不改（2006-2018） - 你值得信赖的PHP框架</span></p></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=64890268" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="eab4b9f840753f8e7"></think>';
    }

    public function hello($name = '王瑶')
    {
        return 'hello,' . $name;
    }
    public function getuser()
    {
        return json(Usermodel::where('uid',1)->find());
    }
    public function getassociation()
    {
        return json(Associationmodel::where('aid',1)->find());
    }
    public function getsection()
    {
        return json(Sectionmodel::where('sid',2)->find());
    }
    public function gettime()
    {
        return json(time());
    }
    public function getauthority()
    {
        return json(Authoritymodel::where('auid',1)->find());
    }
    public function getsectionmember()
    {
        return json(Sectionmembermodel::where('sid',1)->select());
    }
    public function getinformation()
    {
        return json(Informationmodel::where('iid',3)->json(['image'])->find());
    }
    public function getcomment()
    {
        return json(AsInCommentmodel::where('comment_id',1)->find());
    }
    public function getreply()
    {
        return json(AsInReplymodel::where('rid',1)->find());
    }
    //获取全部test数据
    public function getalltest()
    {
        $test = TestModel::select();
        return $test;
    }
    //插入test数据
    public function insettest(Request $request)
    {
//        return json($request->file('image'));
        $testinfo = $request -> param();
//        $img = $request ->post('image');
//        uploadavatar($file)
        //实例化对象
        $upload = new Upload();
        $testinfo['image'] = $upload->uploadavatar($request ->file('image'));
        $Test = new TestModel();
        $Test ->save($testinfo);
        return json($Test);
    }
    public function testupload(Request $request)
    {

        $files = $request->file('image');
        $up = new Upload();
        $img = [];
        if(is_array($files)){
            foreach($files as $file){
                // 移动到框架应用根目录/uploads/ 目录下  验证大小和后缀
                $info = $file->move( '../uploads');
                if($info){
                    //向数组添加图片路径
//                    array_push($imgs,$info->getSaveName());
                    echo str_replace("\\","/",$info->getSaveName());
                }
                else{
                    // 上传失败获取错误信息
                    return $file->getError();
                }
            }
        }
    }
}
