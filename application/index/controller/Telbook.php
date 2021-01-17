<?php


namespace app\index\controller;
use app\model\fortelbook\TelBook as TelBookModel;
use app\model\fortelbook\TelBookSector as TelBookSectorModel;
use think\Db;
use think\Request;
use app\common\measure\Imgcompress;

class Telbook
{
    /**上传电话簿
     * @author 王瑶  2021-01-08  17:40:23
     * @param Request $request
     * @return \think\response\Json
     */
    public function inputtel(Request $request)
    {
        $data = $request->get();
        if(strlen($data['tel_num'])!=8&&strlen($data['tel_num'])!=11){
            return json([
                'error_code'    =>      0,
                'msg'           =>      '号码数据格式不符'
            ]);
        }
        $telbook = new TelBookModel();
        $result = $telbook->save($data);
        if($result) {
            return json(TelBookModel::get($telbook['id']));
        }
    }

    /**获取电话簿
     * @author 王瑶  2021-01-08  17:39:32
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function gettel(Request $request)
    {
        $page = $request->get('page');
        $sector = $request->get('sector');
        $searchinfo = $request->get('searchinfo');
        //如果sector==0&&searchinfo==''搜索全部电话，分页查询20条
        if($sector==0 && $searchinfo==''){
            $tels = Db::name('tel_book')
                ->alias('tb')->join('telBookSector tbs','tb.sid = tbs.id')
                ->field('tb.*,tbs.sector')->paginate(30);
        }
        //按部门查询
        else if($sector!=0 && $searchinfo==''){
            $tels = Db::name('tel_book')

                ->where('sid',$sector)
                ->alias('tb')->join('telBookSector tbs','tb.sid = tbs.id')
                ->field('tb.*,tbs.sector')->paginate(30);
        }
        //搜索
        else {
            $tels = Db::name('tel_book')
                ->whereLike('name','%'.$searchinfo.'%')
                ->alias('tb')->join('telBookSector tbs','tb.sid = tbs.id')
                ->field('tb.*,tbs.sector')->paginate(30);
//            if($tels['data']===[])
//                return 'sa';
            if(($tels->toArray())['data']==[]&&$page==1)
            {
                return json([
                    'error_code'    =>  1,
                    "msg"           =>  '搜索为空'
                ]);
            }
        }
        if(((int)$page)>(($tels->toArray())['last_page'])) {
            return json([
                'error_code'    =>  0,
                "msg"           =>  '没有更多数据了'
            ]);
        }
        return json($tels);
    }
}

