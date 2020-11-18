<?php
namespace app\index\controller;

use think\Db;
use app\model\forassociation\User as Usermodel;
use app\model\forassociation\Association as Associationmodel;
use app\model\forassociation\Section as Sectionmodel;
use app\model\forassociation\Authority as Authoritymodel;
use app\model\forassociation\SectionMember as Sectionmembermodel;
use app\model\forassociation\Information as Informationmodel;
use app\model\forassociation\AsInComment as AsInCommentmodel;
use app\model\forassociation\AsInReply as AsInReplymodel;

class Index
{
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
}
