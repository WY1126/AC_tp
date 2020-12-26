<?php


namespace app\common\measure;


use think\Controller;
use think\Image;

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

    public function uploadimgs($files,&$imgs)
    {
        if(is_array($files)){
            foreach($files as $file){
                // 移动到框架应用根目录/uploads/ 目录下  验证大小和后缀
                $info = $file->move( '../uploads');
                if($info){
                    $tempname = '|'.$info->getFilename();
                    $tempSaveName = str_replace("\\","/",$info->getSaveName());
                    $tempSaveName = str_replace($info->getFilename(),$tempname,$tempSaveName);
                    $image = Image::open($info);
                    $image->thumb(200,200)->save('../uploads/'.$tempSaveName);
                    //向数组添加图片路径
                    //反斜杠替换
                    $getSaveName=str_replace("\\","/",$info->getSaveName());
                    array_push($imgs,$tempSaveName);
                }
                else{
                    // 上传失败获取错误信息
                    return $file->getError();
                }
            }
        }


    }
}