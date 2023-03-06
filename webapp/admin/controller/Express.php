<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Express extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $cur['controller'] = $controller = Request::instance()->controller(); //控制器名
        $cur['action'] = $action = Request::instance()->action(); //行动名
        $this->assign('cur', $cur);
    }

    public function lists(){
        $list = db("address_provinces")->order("id asc")->select();

        $this->assign('list',$list);

        return view();
    }

    public function edit(){
        $id = input("id");
        $data = db("address_provinces")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $money = (input("money") ? input("money") : 0) * 100;

            db("address_provinces")->where("id",$id)->update(['money'=>$money]);

            header("location:".url('lists'));
            die();
        }

        $this->assign('data',$data);

        return view();
    }
}