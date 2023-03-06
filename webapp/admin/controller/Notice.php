<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Notice extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $cur = array();
        $cur['action'] = $action = Request::instance()->action();
        $cur['controller'] = $controller = Request::instance()->controller();
        $this->assign('cur', $cur);
    }

    public function lists()
    {
        $lists = db("notice")->order("id asc")->select();

        $this->assign('lists',$lists);

        return view();
    }

    public function add()
    {
        if(Request::instance()->isPost()){
            $thumb = '';
            $file = request()->file('thumb');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $thumb = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input("post.");
            $info['thumb'] = $thumb;
            $info['intime'] = time();
            db("notice")->insert($info);
            db("member")->where('id','>',0)->setInc('noticeNum',1);

            header("location:".url("lists"));
            die();
        }

        return view();
    }

    public function edit ()
    {
        $id = input("id");

        if(Request::instance()->isPost()){
            $thumb = '';
            $file = request()->file('thumb');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $thumb = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input("post.");
            $info['thumb'] = $thumb;
            db("notice")->where("id",$id)->update($info);

            header("location:".url("lists"));
            die();
        }

        $data = db("notice")->where("id",$id)->find();

        $this->assign('data', $data);

        return view();
    }

    public function del()
    {
        $id = input("id");

        db("notice")->delete($id);
        $lists = db("member")->where('id','>',0)->select();
        foreach($lists as $v){
            $count = db("notice_del")->where(['openid'=>$v['openid'],'zid'=>$id])->count();
            if(!$count) db("member")->where('id',$v['id'])->setDec("noticeNum");
        }

        return "succ";
    }
}