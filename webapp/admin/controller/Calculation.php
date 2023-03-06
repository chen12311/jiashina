<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Calculation extends Controller
{
    public function __construct(){
        parent::__construct();

        $cur['controller'] = $controller = Request::instance()->controller(); //控制器名
        $cur['action'] = $action = Request::instance()->action(); //行动名
        $this->assign('cur',$cur);
    }

    public function index()
    {
        return view();
    }

    public function ajax_index()
    {
        $numMoney = input("numMoney");
        $money = input("money");
        $number = input("num");

        $k = 1;
        $a = 0;
        $_money = 0;
        $content = '';
        for($i=1; $i<=$number; $i++){
            if($_money == 0){
                $arr = ajax_index_a($a,$k,$money,$numMoney,$_money);
                $a = $arr['a'];
                $k = $arr['k'];
                $_money = $arr['money'];
                $content .= $arr['str'];
                if($k == $number){
                    break;
                }else {
                    continue;
                }
            }
            if($_money < 0) {
                $arr = ajax_index_c($a,$k,$money,$numMoney,$_money);
                $a = $arr['a'];
                $k = $arr['k'];
                $_money = $arr['money'];
                $content .= $arr['str'];
                if($k == $number){

                    break;
                }else {
                    continue;
                }
            }
        }

        return array('content' => $content,'a'=>$a);
    }
}