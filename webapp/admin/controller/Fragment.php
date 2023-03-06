<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\db\Query;
use admin\Admin;
new Admin();

class fragment extends Controller
{
    public function __construct(){
        parent::__construct();

        $cur['action'] = $action = Request::instance()->action();
        $cur['controller'] = $controller = Request::instance()->controller();

        $this->assign('cur',$cur);
    }

    /**
     * 图片管理
     */
    public function index()
    {
        $catid = input("catid");

        $lists = array();
        for ($i = 1;$i <= 5; $i++){
            switch ($i){
                case 1:
                    $name = '首页banner图';
                    break;
                case 2:
                    $name = '首页图片一';
                    break;
                case 3:
                    $name = '首页图片二';
                    break;
                case 4:
                    $name = '首页图片三';
                    break;
                case 5:
                    $name = '首页图片四';
                    break;
                default:
                    break;
            }
            $lists[$i]['name'] = $name;
        }

        $this->assign('catid', $catid);
        $this->assign('lists', $lists);

        return view();
    }

    /**
     * 图片列表
     */
    public function lists()
    {
        $catid = input("catid");
        $state = input("state");

        $lists = db("fragment")->where(['catid'=>$catid,'state'=>$state])->order("id desc")->select();

        $this->assign('catid', $catid);
        $this->assign('state', $state);
        $this->assign('lists', $lists);

        return view();
    }

    /**
     * 添加图片
     */
    public function add()
    {
        $catid = input("catid");
        $state = input("state");

        if(Request::instance()->isPost()){
            // 缩略图
            $thumb = "";
            $file = request()->file('thumb');
            if($file){
                $info = $file->move('./uploads');
                if($info){
                    $url = $info->getFilename();
                    $thumb = "/uploads/".date("Ymd",time()).'/'.$url;
                }
            }

            $info = input();
            $info['thumb'] = $thumb;
            $info['intime'] = time();
            db("fragment")->insert($info);

            header("location:".url("lists",'catid='.$catid.'&state='.$state));
            die();
        }

        $this->assign('catid', $catid);
        $this->assign('state', $state);

        return view();
    }

    /**
     * 修改图片
     */
    public function edit()
    {
        $id = input("id");
        $catid = input("catid");
        $state = input("state");

        $data = db("fragment")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            // 缩略图
            $thumb = $data['thumb'];
            $file = request()->file('thumb');
            if($file){
                $info = $file->move('./uploads');
                if($info){
                    $url = $info->getFilename();
                    $thumb = "/uploads/".date("Ymd",time()).'/'.$url;
                }
            }

            $info = input("post.");
            $info['thumb'] = $thumb;
            db("fragment")->where("id",$id)->update($info);

            header("location:".url("lists",'catid='.$catid.'&state='.$state));
            die();
        }

        $this->assign('catid', $catid);
        $this->assign('state', $state);
        $this->assign('data', $data);
        
        return view();
    }

    /**
     * 删除图片
     */
    public function del(){
        $id = input("id");

        db("fragment")->delete($id);

        return true;
    }
}