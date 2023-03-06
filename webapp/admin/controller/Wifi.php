<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Wifi extends Controller
{
    private $db;
    public function __construct()
    {
        parent::__construct();
        $this->db = db("wifi_merchant_fenlei");
        $this->wifidb = db("wifi");
        $this->sjdb = db("wifi_merchant_info");

        $cur['action'] = $action = Request::instance()->action();
        $cur['controller'] = $controller = Request::instance()->controller();
        $this->assign('cur',$cur);
    }

    public function category_lists()
    {
        $lists = $this->db->select();

        $this->assign('lists',$lists);

        return view();
    }

    public function category_add()
    {
        if(Request::instance()->isPost()){
            // 缩略图
            $ico = '';
            $file = request()->file('ico');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $ico = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input();
            $info['ico'] = $ico;
            $info['intime'] = time();
           

            $this->db->insert($info);

            header("location:".url("lists"));die();
        }

        return view();
    }

    public function category_edit()
    {
        $id = input("id");
        $data = $this->db->where("id",$id)->find();

        if(Request::instance()->isPost()) {
            // 缩略图
            $ico = $data['ico'];
            $file = request()->file('ico');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $ico = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input();
            $info['ico'] = $ico;

            $this->db->where("id",$id)->update($info);

            header("location:" . url("category_lists"));
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    public function category_del()
    {
        $id = input("id");

        $this->db->delete($id);

        return true;
    }    
	
	public function wifi_lists()
    {
        $lists = $this->wifidb->select();

        $this->assign('lists',$lists);

        return view();
    }


    public function wifi_edit()
    {
        $id = input("id");
        $data = $this->wifidb->where("id",$id)->find();

        if(Request::instance()->isPost()) {
            // 缩略图
            $ico = $data['ico'];
            $file = request()->file('ico');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $ico = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input();
            $info['ico'] = $ico;

            $this->wifidb->where("id",$id)->update($info);

            header("location:" . url("category_lists"));
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    public function wifi_del()
    {
        $id = input("id");

        $this->wifidb->delete($id);

        return true;
    }
	public function shangjia_lists()
    {
        $lists = $this->sjdb->select();

        $this->assign('lists',$lists);

        return view();
    }


    public function shangjia_edit()
    {
        $id = input("id");
        $data = $this->sjdb->where("id",$id)->find();

        if(Request::instance()->isPost()) {
            // 缩略图
            $ico = $data['ico'];
            $file = request()->file('ico');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $ico = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input();
            $info['ico'] = $ico;

            $this->sjdb->where("id",$id)->update($info);

            header("location:" . url("category_lists"));
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    public function shangjia_del()
    {
        $id = input("id");

        $this->sjdb->delete($id);

        return true;
    }
}