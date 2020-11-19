<?php


namespace app\association\controller;
use app\model\forassociation\Association as AssociatonModel;

class Association
{
    public function getallassociation()
    {
        return json(AssociatonModel::select());
    }
    public function getmyassociation()
    {


    }

}