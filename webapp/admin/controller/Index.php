<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Index extends Controller
{
    public function __construct(){
        parent::__construct();

        $cur['action'] = $action = Request::instance()->action();
        $cur['controller'] = $controller = Request::instance()->controller();
        $this->assign('cur',$cur);
    }

    public function index(){
        return view();
    }

    /**
     * 管理员登陆入口
     **/
    public function login(){
        //获取post
        if(Request::instance()->isPost()){
            //判断验证码
            if(!captcha_check(input('code'),1) && session('admin_logo_fail') > 1){
                $this->error(lang('code_error'),url('login'));
            }
            
            //管理员登录
            $adminInfo = db('Admin')->find();
            $encrypt = $adminInfo['encrypt'];

            $info = array();
            $info['username'] = input('username');
            $info['password'] = password(input('password'), $encrypt);
            $adminDecide = db('Admin')->where($info)->count();

            if($adminDecide){
                //登录成功设置session
                arraySession($adminInfo);
                session('admin_logo_fail',0);
                $this->success("登陆成功",url('index'));
            }else{
                $number = session('admin_logo_fail') ? session('admin_logo_fail') : 0;
                session('admin_logo_fail',$number+1);
                $this->error("登陆失败",url('login'));
            }
        } 
       return view();
    }

    /**
     * 退出登陆
     * 
     * */
    public function logout(){
        session(null);
        $this->success("退出成功",url('login'));
    }
}
