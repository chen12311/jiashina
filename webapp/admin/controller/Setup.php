<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Setup extends Controller
{
    private $tempHtml;
    public function __construct(){
        parent::__construct();

        $cur['controller'] = $controller = Request::instance()->controller(); //控制器名
        $cur['action'] = $action = Request::instance()->action(); //行动名
        $this->assign('cur',$cur);
    }

    /**
     * 网站基本设置
     **/
    public function basic()
    {
        $data = db("basic")->find();

        if(Request::instance()->isPost()){
            // ico图片
            $ico = $data['ico'];
            $file = request()->file('ico');
            if ($file) {
                $infos = $file->move('./','favicon.ico');
                if ($infos) {
                    $url = $infos->getFilename();
                    $ico = '/' . $url;
                }
            }

            // logo图片
            $logo = $data['logo'];
            $file = request()->file('logo');
            if ($file) {
                $infos = $file->move('./adminstatic/img/','logo-big-white.png');
                if ($infos) {
                    $url = $infos->getFilename();
                    $logo = '/adminstatic/img/' . $url;
                }
            }

            $info = input();
            $info['ico'] = $ico;
            $info['logo'] = $logo;
            db("basic")->where("id",1)->update($info);

            header("location:".url("basic"));
            die();
        }

        return view("",$data);
    }

    /**
     * 网站设置
     */
    public function website()
    {
        $data = db("website")->find();

        if(Request::instance()->isPost()){
            $info = input();
            db("website")->where("id",1)->update($info);

            header("location:".url("website"));
            die();
        }

        return view("",$data);
    }
    /**
     * 网站设置
     */
    public function gg()
    {
        $data = db("basic")->find();
		
		$ggtp = $data['ggtp'] ? string2array($data['ggtp']) : [];

        if(Request::instance()->isPost()){
            // ico图片
            $thumb = $data['ggsp'];
            $file = request()->file('ggsp');
            if ($file) {
                $infos = $file->move('./uploads/gg/');
                if ($infos) {
                    $url = $infos->getFilename();
                    $thumb = "/uploads/gg/" . date("Ymd", time()) . '/' . $url;
                }
            }


            $info = input();
			            $info['ggsp'] = $thumb;

			 $info['ggtp'] = isset($info["ggtp"]) ? array2string($info["ggtp"]) : "";
            db("basic")->where("id",1)->update($info);

            header("location:".url("gg"));
            die();
        }
        $this->assign('ggtp',$ggtp);
        $this->assign('data',$data);

        return view();
    }


    /**
     * 修改登录密码
     * */
    public function admininfo()
    {
        $adminInfo = db('admin')->where('adminid','1')->find();
        if(Request::instance()->isPost()){
            $info = array();
            $info['password'] = password(input('password'),$adminInfo['encrypt']);
            $basicPost = db('admin')->where("adminid","1")->update($info);
            if($basicPost){
                $this->success("修改成功",url('admininfo'));
            }
        }
        return view($this->tempHtml,$adminInfo);
    }

    public function collage ()
    {
        $data = db("collage")->where("id",1)->find();

        return view("",$data);
    }
	
	
	    public function ajax_img()
    {
        $images = '';
        $files = request()->file('ggtp');
        if($files){
            foreach($files as $fileArr){
                // 移动到框架应用根目录/public/uploads/ 目录下
                $infoArr = $fileArr->move('./uploads/gg/');
                if($infoArr){
                    $url = $infoArr->getFilename();
                    $images .= "<input name='ggtp[]' value='/uploads/gg/".date("Ymd",time())."/".$url."'>";
                }else{
                    $images .= "";
                }
            }
        }
        return $images;
    }
	
}
