<?php

namespace app\news\controller;

use QL\QueryList;
use think\Request;
use think\Url;

class News
{
     public function subtext($text, $length)
    {
        if(mb_strlen($text, 'utf8') > $length) {
            return mb_substr($text, 0, $length, 'utf8').'...';
        } else {
            return $text;
        }
    }

    /**
     * 获取新闻标题、时间和原文地址
     * @author 王瑶  2020-11-3  16:11:52
     * @return \think\response\Json
     */
    public function getnews()
    {
        $ql = QueryList::get('https://www.jxutcm.edu.cn/');     //江西中医药大学官网
        $data = [];
        for($type = 73,$i=0 ; $type <= 77 ; $type ++)
        {
            if($type==76)
                continue;
            $rt = [];
//            $text = $ql->find('.c498'.$type)->texts();   //新闻标题
//            $rt['title'] = $this->subtext($text,22);
            $rt['title'] = $ql->find('.c498'.$type)->texts();   //新闻标题
            $rt['create_time'] = $ql->find('.timestyle498'.$type)->texts(); //时间
            $rt['src'] = $ql->find('.c498'.$type)->map(function($item){     //地址
                return 'https://www.jxutcm.edu.cn/'.$item->attrs('href')[0];
            });
            for($key = 0 ; $key < count($rt['title']) ; $key++)
            {
                $data[$i][$key]['title'] = $rt['title'][$key];
//                $data[$i][$key]['title'] = $this->getnews($data[$i][$key]['title'],22);
                $text = $data[$i][$key]['title'];
                $data[$i][$key]['title'] = $this->subtext($text,20);
//                if(strlen($data[$i][$key]['title'])>22){
//                  //  $data[$i][$key]['title'] = substr($data[$i][$key]['title'],0,22).'…';
//                }
                $data[$i][$key]['create_time'] = $rt['create_time'][$key];
                $data[$i][$key]['src'] = $rt['src'][$key];
            }
            $i++;
        }
        return json($data);
    }

    /**
     * 获取新闻详细内容
     * @author 王瑶  2020-11-03  16:12:29
     * @param Request $request
     * @return \think\response\Json
     */
    public function getnewscontent(Request $request)
    {
        $rurl = $request->get('rurl');
        $ql = QueryList::get($rurl);
        $data = [];
//        $data []= $ql->find('#vsb_content')->html();
//        $img = $ql -> find('#C-Main-Article')->html();
        $title = $ql->find('.infobt')->text();
        $create_time = $ql->find('.info')->text();
        $content = $ql->find('#vsb_content')->html();
        $data['title'] = $title;
        $data['create_time'] = substr($create_time , 2 , 21);//  时间：2021-01-09
        $data['content'] = $content;
        return json($data);
    }
}