<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Insure extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $cur['action'] = $action = Request::instance()->action();
        $cur['controller'] = $controller = Request::instance()->controller();
        $this->assign('cur', $cur);
    }

    public function lists ()
    {
        $nickname = input("nickname");

        $where = "i.`id` > 0";
        if($nickname){
            $where .= " and m.`nickname` like '%$nickname%'";
        }

        $lists = db("insure")->alias("i")
                ->join("yado_member m","m.`openid` = i.`openid`")
                ->field("i.*,m.`nickname`")
                ->where($where)
                ->order("id desc")->paginate(15);
        $pages = $lists->render();

        $this->assign('lists', $lists);
        $this->assign('pages', $pages);
        $this->assign('nickname', $nickname);

        return view();
    }

    public function insure_lists()
    {
        $id = input("id");

        $data = db("insure_lists")->where("id",$id)->find();
        if($data['status'] == '自己'){
            $openid = db("insure")->where("id",$data['insure_id'])->value('openid');
            $member = db("member")->where("openid",$openid)->find();
            $data['name'] = $member['name'];
            $data['phone'] = $member['phone'];
            $data['sfz_a'] = $member['sfza'];
            $data['sfz_b'] = $member['sfzb'];
        }

        $this->assign('data', $data);

        return view();
    }

    public function health_lists()
    {
        $id = input("id");

        $lists = db("insure_health_lists")->where("insure_health_id",$id)->order("id desc")->select();

        $this->assign('lists', $lists);

        return view();
    }
}