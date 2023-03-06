<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Agree extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $cur['controller'] = $controller = Request::instance()->controller(); //控制器名
        $cur['action'] = $action = Request::instance()->action(); //行动名
        $this->assign('cur', $cur);
    }

    public function page()
    {
        $id = input("id");

        $data = db("content_agree")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            if($data){
                db("content_agree")->where("id",$id)->update(["content"=>input("content"),"intime"=>time()]);
            } else {
                db("content_agree")->insert(["id"=>1,"content"=>input("content"),"intime"=>time()]);
            }

            header("location:".url("page","id=".$id));
            die();
        }

        $this->assign('id',$id);
        $this->assign('data',$data);

        return view();
    }
}