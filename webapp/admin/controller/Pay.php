<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use think\db\Query;
use admin\Admin;
new Admin();

class pay extends Controller
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
        $where = array();
        $where['state'] = ['in','1,2,3,5'];
        $lists = db("record_pay")->where($where)->paginate(50,false,["query"=>[]]);
        $pages = $lists->render();

        $this->assign('lists',$lists);
        $this->assign('pages',$pages);

        return view();
    }
	
    public function alists()
    {
        $where = array();
		$time = input("time");
		
		if($time ==''){
			$time=date("Y-m-d");
		}

		//echo $time;die;
		if(strtotime($time) < strtotime('2022-07-08')){
				$lists = db("record_pay")->where(["state"=>[">",11],"intime"=>["between", [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')]]])->order('intime desc')->paginate(50,false,["query"=>["time"=>$time]]);
		}else{      
		
				//where(["state"=>[">=",1]])->
				$lists = db("record_collage")->where(["state"=>[">=",1]])->whereTime('ctime', 'between', [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')])->order('ctime desc')->paginate(50,false,["query"=>["time"=>$time]]);
		
		
		}

        $pages = $lists->render();
        if (Request::instance()->isPost()) {
            if(input("dc")) {
                $arr = [];
                $lists = db("record_pay")->where(["state"=>[">",11],"intime"=>["between", [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')]]])->order('intime desc')->select();
                foreach($lists as $v){
                    $nickname = db("member")->where("openid",$v['openid'])->value("nickname");
                    $arr[] = ["id"=>$v["id"],"nickname"=>$nickname,"money"=>xiaoshu($v["money"]),"msg"=>$v["msg"],"intime"=>date("Y-m-d H:i:s",$v["intime"])];
                }
                $indexKey = array('id', 'nickname', 'money', 'msg', 'intime');
                $indexName = array('ID','变动人','金额','类型','下单时间');
                $indexWidth = array('10','30','15','30','30');
                toExcel($arr, "数据", $indexKey, $indexName, $indexWidth);
            }
        }
        
		       // $lists = array_merge($lists,$lists);

        $this->assign('lists',$lists);
        $this->assign('pages',$pages);
        $this->assign('time',$time);

        return view();
    }        
	public function aalists()
    {
        $where = array();
		$time = input("time");
		
		if($time ==''){
			$time=date("Y-m-d");
		}
		//echo $time;die;

		$lists = db("order_collage_item")->group('openid')->where(["collage_state"=>["in","2,3"]])->whereTime('ctime', 'between', [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')])->order('ctime desc')->paginate(50,false,["query"=>["time"=>$time]]);
	

        $pages = $lists->render();


		       // $lists = array_merge($lists,$lists);

        $this->assign('lists',$lists);
        $this->assign('pages',$pages);
        $this->assign('time',$time);

        return view();
    }    
	public function zmlists()
    {
        $where = array();
		$time = input("time");
		
		if($time ==''){
			$time=date("Y-m-d");
		}

		//echo $time;die;
      
		$lists = db("record_zhuanmai")->where(["state"=>0,"intime"=>["between", [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')]]])->order('intime desc')->paginate(50,false,["query"=>["time"=>$time]]);
		//var_dump($lists);die;

        $pages = $lists->render();
        if (Request::instance()->isPost()) {
            if(input("dc")) {
                $arr = [];
                $lists = db("record_pay")->where(["state"=>[">",11],"intime"=>["between", [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')]]])->order('intime desc')->select();
                foreach($lists as $v){
                    $nickname = db("member")->where("openid",$v['openid'])->value("nickname");
                    $arr[] = ["id"=>$v["id"],"nickname"=>$nickname,"money"=>xiaoshu($v["money"]),"msg"=>$v["msg"],"intime"=>date("Y-m-d H:i:s",$v["intime"])];
                }
                $indexKey = array('id', 'nickname', 'money', 'msg', 'intime');
                $indexName = array('ID','变动人','金额','类型','下单时间');
                $indexWidth = array('10','30','15','30','30');
                toExcel($arr, "数据", $indexKey, $indexName, $indexWidth);
            }
        }
        
		       // $lists = array_merge($lists,$lists);

        $this->assign('lists',$lists);
        $this->assign('pages',$pages);
        $this->assign('time',$time);

        return view();
    }
}