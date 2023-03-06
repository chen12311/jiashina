<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Position extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $cur['action'] = $action = Request::instance()->action();
        $cur['controller'] = $controller = Request::instance()->controller();
        $this->assign('cur', $cur);
    }

    public function lists()
    {
        $state = input("state");

        $list = db("position")->where("state",$state)->order("ID DESC")->paginate(15);
        $page = $list->render();

        $this->assign('page',$page);
        $this->assign('list',$list);
        $this->assign('state',$state);

        return view();
    }

    public function add()
    {
        $lists = db("content")->field("id,title")->where("zcatid",">",0)->order("id desc")->select();

        $this->assign('lists',$lists);

        return view();
    }

    public function del()
    {
        $id = input("id");

        db("position")->delete($id);

        return true;
    }
}

?>