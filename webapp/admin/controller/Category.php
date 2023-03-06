<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Category extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $cur['action'] = $action = Request::instance()->action();
        $cur['controller'] = $controller = Request::instance()->controller();
        $this->assign('cur',$cur);
    }

    /**
     * 栏目列表
     */
    public function lists()
    {
        $id = input("id") ? input("id") : 0;
        
        $cateNav = cateNav($id);

        $list = db("category")->where("pid",$id)->order("id asc")->select();

        $this->assign('id',$id);
        $this->assign('cateNav',$cateNav);
        $this->assign('list',$list);

        return view();
    }

    /**
     * 添加栏目
     */
    public function add()
    {
        $id = input("id");

        $cateNav = cateNav($id, 1);

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

            $info = input("post.");
            $info['pid'] = $id;
            $info['level'] = $cateNav['level'];
            $info['thumb'] = $thumb;
            $info['intime'] = time();
            db("category")->insert($info);

            header("location:".url('lists','id='.$id));
            die();
        }

        $this->assign('id',$id);
        $this->assign('cateNav',$cateNav);

        return view();
    }

    public function edit()
    {
        $id = input("id");

        $data = getData("category",['id'=>$id]);
        $cateNav = cateNav($id, 2);

        if(Request::instance()->isPost()){
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

            $arr = input("post.");
            $arr['thumb'] = $thumb;
            db("category")->where('id',$id)->update($arr);

            header("location:".url('lists','id='.$data['pid']));
            die();
        }

        $this->assign('data',$data);
        $this->assign('cateNav',$cateNav);

        return view();
    }
    
    public function del()
    {
        $id = input("id");

        $data = db("category")->where("id",$id)->find();
        if($data['level'] == 1){
            db("fragment")->where("catid",$id)->delete();
            db("category_new")->where(['state'=>1,'href'=>$id])->delete();
            $listOne = db("category")->where("pid",$data['id'])->select();
            foreach($listOne as $v){
                $listTwo = db("category")->where("pid",$v['id'])->select();
                foreach($listTwo as $r){
                    $contentList = db("content")->where("catid",$r['id'])->select();
                    foreach($contentList as $i){
                        db("content_list")->where("zid",$i['id'])->delete();
                        db("position")->where("cid",$i['id'])->delete();
                    }
                    db("content")->where("catid",$r['id'])->delete();
                }
                db("category")->where("pid",$v['id'])->delete();
            }
        }elseif($data['level'] == 2){
            $listTwo = db("category")->where("pid",$data['id'])->select();
            foreach($listTwo as $v){
                $contentList = db("content")->where("catid",$v['id'])->select();
                foreach($contentList as $r){
                    db("content_list")->where("zid",$r['id'])->delete();
                    db("position")->where("cid",$r['id'])->delete();
                }
                db("content")->where("catid",$v['id'])->delete();
            }
        }elseif($data['level'] == 3){
            $contentList = db("content")->where("catid",$data['id'])->select();
            foreach($contentList as $v){
                db("content_list")->where("zid",$v['id'])->delete();
                db("position")->where("cid",$v['id'])->delete();
            }
            db("content")->where("catid",$data['id'])->delete();
        }
        db("category")->where("id",$id)->delete();
        
        return true;
    }
}
?>