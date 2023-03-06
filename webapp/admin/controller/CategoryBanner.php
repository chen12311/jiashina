<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class CategoryBanner extends Controller
{
    private $db;
    public function __construct()
    {
        parent::__construct();
        $this->db = db("category_new");

        $cur['action'] = $action = Request::instance()->action();
        $cur['controller'] = $controller = Request::instance()->controller();
        $this->assign('cur',$cur);
    }

    public function lists()
    {
        $lists = $this->db->select();

        $this->assign('lists',$lists);

        return view();
    }

    public function add()
    {
        if(Request::instance()->isPost()){
            // 缩略图
            $thumb = '';
            $file = request()->file('thumb');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $thumb = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input();
            $info['thumb'] = $thumb;
            $info['intime'] = time();
            if($info['state'] == 3){
                $info['href'] = $info['href_2'];
            } else {
                $info['href'] = $info['href_1'];
            }
            unset($info['href_1']);
            unset($info['href_2']);

            $this->db->insert($info);

            header("location:".url("lists"));die();
        }

        return view();
    }

    public function edit()
    {
        $id = input("id");
        $data = $this->db->where("id",$id)->find();

        if(Request::instance()->isPost()) {
            // 缩略图
            $thumb = $data['thumb'];
            $file = request()->file('thumb');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $thumb = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input();
            $info['thumb'] = $thumb;
            if ($info['state'] == 3) {
                $info['href'] = $info['href_2'];
            } else {
                $info['href'] = $info['href_1'];
            }
            unset($info['href_1']);
            unset($info['href_2']);

            $this->db->where("id",$id)->update($info);

            header("location:" . url("lists"));
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    public function new_lists()
    {
        $id = input("id");
        $state = input("state");

        if($state == 1){
            $lists = db("category")->where("pid",0)->order("id asc")->select();
        } else {
            $lists = db("content")->field("id,title as name")->where("zcatid",">",0)->order("id desc")->select();
        }

        $html = "";
        foreach ($lists as $v){
            $html .= "<option value='{$v['id']}'";
            $html .= $id == $v['id'] ? "selected" : "";
            $html .= ">{$v['name']}</option>";
        }

        return $html;
    }

    public function del()
    {
        $id = input("id");

        $this->db->delete($id);

        return true;
    }
}