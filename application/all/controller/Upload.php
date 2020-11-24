<?php


namespace app\all\controller;


use think\Controller;

class Upload extends Controller
{
    /**单图片上传
     * 2020.11.19   王瑶
     * @param $file
     * @return mixed
     */
    public function uploadavatar($file)
    {
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move( '../upload');
        if($info){
            // 成功上传后 获取上传信息
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            return $info->getSaveName();
        }else{
            // 上传失败获取错误信息
            return $file->getError();
        }
    }
}