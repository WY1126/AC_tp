<?php


namespace app\association\controller;
use app\model\forassociation\Association as AssociatonModel;
use think\Request;
use app\model\forassociation\Associator as AssociatorModel;
use app\model\forassociation\AsLike as AslikeModel;
use app\model\forassociation\Section as SectionModel;

class Association
{
    /**获取全部社团协会接口
     * 2020.11.19   王瑶
     * @return \think\response\Json
     */
    public function getallassociation()
    {
        $count = AssociatonModel::count('aid');

        return json([
            'count'     =>      $count,
            'data'      =>      AssociatonModel::select(),
        ]);
    }

    /**获取我的社团
     * 2020.11.23 王瑶
     * @param Request $request
     * @return \think\response\Json
     */
    public function getmyassociation(Request $request)
    {
        $uid = $request -> post('uid');
        //column获取某一列的值
        $aid = AssociatorModel::where('uid',$uid)->column('aid');
        if(!$aid)
        {
            return json([
                'error_code'    =>      1,
                'msg'           =>      '无加入的社团',
            ]);
        }
        //whereIn直接匹配数组值
        $associations = AssociatonModel::whereIn('aid',$aid)->select();
        return json($associations);
    }

    /**通过ID获取社团信息
     * 2020.11.23 16:29     王瑶
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getassociation($aid)
    {
        $association = AssociatonModel::where('aid',$aid)->findOrEmpty();
        if(!$association)
        {
            return json([
                'error_code'        =>      1,
                'msg'               =>      '错误',
            ]);
        }
        return $association;
    }
    //复合查询查找部门和部门下的成员
    public function test()
    {
//        return 'dsa';
//        die;
        $sector = SectionModel::where('sid',1)->find();

        $members = $sector->members;

        return json($members);


    }

    //进入社团详情页，获取详细信息
    public function associationinfo(Request $request)
    {
        $aid = $request -> post('aid');
        //获取社团协会基本信息
        $association = $this->getassociation($aid);
        //获取社团人数、点赞数
        $ernum = AssociatorModel::where('aid',$aid)->count();
        $likenum = AslikeModel::where('aid',$aid)->count();
        //
        $sector = SectionModel::where('aid',$aid)->columu('sid','name');


    }
    /**用户点赞社团接口或取消点赞
     * 2020.11.25   12:50   王瑶
     * @param Request $request
     * @return \think\response\Json
     */
    public function likeassociation(Request $request)
    {
        $aid = $request->post('aid');
        $uid = $request->post('uid');
        //判断点赞表是否存在
        $temp = [
            'aid'   =>  $aid,
            'uid'   =>  $uid,
        ];
        $aslike = new AslikeModel();
        $data = AslikeModel::where($temp)->find();
        //不存在点赞表，创建点赞表
        if(!$data)
        {
            $f = $aslike->save($temp);
            if($f) {
                $data = AslikeModel::where($temp)->find();
            }
        }
        $likenum = $this->dolikeas($data);

        //修改statue
        $msg = ['取消点赞','点赞成功'];
        //修改status状态
        $data->statue += 1;     $data->statue %= 2;

        $data->save();
        return json([
            'data'      =>      $data,
            'likenum'   =>      $likenum,
            'msg'       =>      $msg[$data->statue],
        ]);

    }
    //发出点赞操作
    public function dolikeas ($data)
    {
//        $data['aid'] = $request->post('aid');
//        $data['statue'] = $request->post('statue');
        //对社团表likenum进行增减
        $temp = AssociatonModel::where('id',$data['aid'])->find();
        if($data['statue']==0){
            $temp->like_num +=1;
        } else {
            $temp->like_num -=1;
        }
        $temp->save();
        return $temp->like_num;
    }
}