<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Member extends Controller
{
    public function __construct(){
        parent::__construct();

        $cur['controller'] = $controller = Request::instance()->controller(); //控制器名
        $cur['action'] = $action = Request::instance()->action(); //行动名
        $this->assign('cur',$cur);
    }
    
    public function tixian ($id = 0, $num = 0)
    {
        db("member")->where("id",$id)->setField("tixian",$num);

        return "success";
    }

    public function tixian_all ($num = 0)
    {
        db("website")->where("id",1)->setField("tixian",$num);
        db("member")->where("id",">",0)->setField("tixian",$num);

        return "success";
    }

    public function money_all ($num = 0)
    {
        $lists = db("collage_many_money")->field("id,openid,money_reduce")->where("money_reduce",">",0)->select();
        foreach ($lists as $v) {
            if (!$num) {
                db("collage_many_money")->where("id", $v["id"])->setField("state", 1);
                db("member")->where("openid", $v["openid"])->setInc("collage_money", $v["money_reduce"] * 100);
            } else {
                db("collage_many_money")->where("id", $v["id"])->setField("state", 0);
                db("member")->where("openid", $v["openid"])->setDec("collage_money", $v["money_reduce"] * 100);
            }
        }
    }

    //商户信息
    public function lists() {
        $state = input("state") ? input("state") : 0;
        $nickname = input("nickname") ? input("nickname") : '';

        $where = array();
        if($state != 0){
            $where['state'] = $state;
        }
        if($nickname){
            $where['nickname'] = ['like','%'.$nickname.'%'];
        }
        $lists = db("member")->where($where)->order("level desc,collage_money desc")->paginate(30,false,['query'=>['nickname'=>$nickname]]);
        $pages = $lists->render();

        $this->assign('lists',$lists);
        $this->assign('pages',$pages);

        $this->assign('state',$state);
        $this->assign('nickname',$nickname);

        return view();
    }

    public function level_lists ()
    {
        $id = input("id");

        $data = db("member")->field("id,nickname,fx_top_a")->where("id",$id)->find();
        $lists = db("member")->field("id,nickname,intime")->where("fx_top_a",$id)->select();

        $this->assign('data',$data);
        $this->assign('lists',$lists);

        return view();
    }

    public function level_del ()
    {
        $id = input("id");
        
        db("member")->where("id",$id)->setField("fx_top_a",0);

        return "success";
    }

    public function fxChange()
    {
        $id = input("id");
        $fx = input("fx");

        db("member")->where("id",$id)->update(['fx'=>$fx]);

        return true;
    }

    /**
     * 成为店铺会员
     */
    public function shop()
    {
        $id = input("id");

        $data = db("member")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $shopimg = $data['shopimg'];
            $file = request()->file('shopimg');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $shopimg = "/uploads/" . date("Ymd", time()) . '/' . $url;
                    $image = \think\Image::open('.'.$shopimg);
                    $image->thumb(450, 450)->save('.'.$shopimg);
                }
            }

            $sfza = $data['sfza'];
            $file = request()->file('sfza');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $sfza = "/uploads/" . date("Ymd", time()) . '/' . $url;
                    $image = \think\Image::open('.'.$sfza);
                    $image->thumb(450, 450)->save('.'.$sfza);
                }
            }

            $sfzb = $data['sfzb'];
            $file = request()->file('sfzb');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $sfzb = "/uploads/" . date("Ymd", time()) . '/' . $url;
                    $image = \think\Image::open('.'.$sfzb);
                    $image->thumb(150, 150)->save('.'.$sfzb);
                }
            }
            $yyzz = $data['yyzz'];
            $file = request()->file('shopimg');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $yyzz = "/uploads/" . date("Ymd", time()) . '/' . $url;
                    $image = \think\Image::open('.'.$yyzz);
                    $image->thumb(450, 450)->save('.'.$yyzz);
                }
            }

            $shopurl = $data['shopurl'];
            if(!$shopurl) {
                require_once "../vendor/phpqrcode/phpqrcode.php";
                $value = "http://www.jiajiazxgg.com/index.php/index/index/index/id/" . $id;
                \QRcode::png($value, "./ewm/member$id.png", "L", 6, 2);

                $shopurl = "/ewm/member$id.png";
            }

            $info = array();
            $info['state'] = input("state");
            $info['shopname'] = input("shopname");
            $info['shopimg'] = $shopimg;
            $info['sfza'] = $sfza;
            $info['sfzb'] = $sfzb;
            $info['yyzz'] = $yyzz;
            $info['shopurl'] = $shopurl;
            $info['content'] = input("content");
            db("member")->where("id",$id)->update($info);

            header("location:".url("lists","state=".input("state")));
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    /**
     * 闭店
     */
    public function close()
    {
        $id = input("id");
        $dic = input("dic");

        db("member")->where("id",$id)->update(['state'=>$dic]);

        if($dic == 0) {
            $this->success('已闭店', url('lists'));
        }
    }

    /**
     * 二维码
     */
    public function ewm()
    {
        $id = input("id");

        $data = db("member")->where("id",$id)->find();

        if(!$data['shopurl']) {
            require_once "./vendor/phpqrcode/phpqrcode.php";
            $value = "http://www.jiajiazxgg.com/index.php/index/index/index/id/" . $id;
            \QRcode::png($value, "./ewm/member$id.png", "L", 6, 2);

            $data['shopurl'] = $shopurl = "/ewm/member$id.png";
            db("member")->where("id",$id)->update(['shopurl'=>$shopurl]);
        }

        $this->assign('data',$data);

        return view();
    }    
	
	public function ban()
    {
        $id = input("id");

        $data = db("member")->where("id",$id)->find();
		
		
        if(Request::instance()->isPost()){
            $arr = input();
            db("member")->where("id",$id)->update($arr);

            echo"<script>history.go(-1);</script>";


            //header("location:".url("level_edit","id=".$id));
            die();
        }
		        $this->assign('data',$data);

        return view();
    }

    /**
     * 交易记录
     */
    public function pay()
    {
        $id = input("id");
        $nickname = input("nickname");
        $orderNo = input("orderNo") ? input("orderNo") : '';
        $starttime = input("starttime");
        $endtime = input("endtime");
        $dic = input("dic") ? input("dic") : 0;

        $member = db("member")->where("id",$id)->find();

        //." and `close` = 0"

        $where = ["state"=>7,"status"=>0,"cid"=>$id];
        if($dic) $where = ["state"=>2,"status"=>1,"cid"=>$id];

        if($nickname) {
            $arr = db("member")->field("openid")->where("nickname","like","%{$nickname}%")->select();
            $str = implode(",",array_column($arr,"openid"));
            $where["openid"] = ["in",$str];
        }

        if($orderNo) $where["orderNo"] = ["like","%{$orderNo}%"];

        if($starttime && !$endtime) $where["paytime"] = [">",strtotime($starttime.'00:00:00')];
        if(!$starttime && $endtime) $where["paytime"] = ["<",strtotime($endtime.'23:59:59')];
        if($starttime && $endtime) $where["paytime"] = ["between",[strtotime($starttime.'00:00:00'),strtotime($endtime.'23:59:59')]];

        $lists = db("order")->where($where)->order("paytime desc")->select();

        if(Request::instance()->isPost()){
            $submit = input("submit");
            if($submit) {
                $index = 1;
                foreach($lists as $k => $v){
                    $nickname = db("member")->where("openid",$v['openid'])->value("nickname");

                    if ($v['fx_b'] == 0 && $v['money_b'] == 0){
                        $str_a = '未返现';
                    } else {
                        $str_a = '已返现';
                        if($v['money_b'] == 0){
                            $str_a .= xiaoshu($v['ggmoney']*$v['number']*$v['scale']);
                        } else {
                            $str_a .= xiaoshu($v['money_b']);
                        }
                        if($v['id'] > 2860){
                            $price = intval($v['ggmoney']*$v['number']*$v['scale']) - intval($v['money_b']);
                            if($price > 0){
                                $str_a .=' 剩余 '.xiaoshu($price).' 未反';
                            }
                        }
                    }

                    if($v['fx_a'] == 0){
                        $str_b = '已被返现'.xiaoshu($v['fxmoney']);
                    } else {
                        $str_b = '已全部返现';
                    }

                    $str_c = xiaoshu($v['ggmoney']*$v['fwf']);

                    $lists[$k]['index'] = $index;
                    $lists[$k]['shopname'] = $member['shopname'];
                    $lists[$k]['nickname'] = $nickname;
                    $lists[$k]['ggmoney'] = xiaoshu($v['ggmoney'],1);
                    $lists[$k]['str_a'] = $str_a;
                    $lists[$k]['str_b'] = $str_b;
                    $lists[$k]['str_c'] = $str_c <= 0.01 ? 0.01 : $str_c;
                    $lists[$k]['intime'] = date("Y-m-d H:i:s",$v['intime']);
                    $index++;
                }
                $indexKey = array('index', 'shopname', 'orderNo', 'nickname', 'ggmoney', 'str_a', 'str_b', 'str_c', 'intime');
                $indexName = array('ID','店铺','订单号','下单人','支付金额','返现','被返现','服务费','下单时间');
                $indexWidth = array('10','15','25','30','12','22','18','15','25');
                toExcel($lists, '订单数据'.date("Y-m-d", time()), $indexKey, $indexName, $indexWidth);
            }
        }

        $num = $money = $fwf = 0;
        foreach($lists as $v){
            $num++;
            $money += $v['ggmoney'];
            if($v['status'] == 1){
                $fwf += $v['fwf'];
            } else {
                $fwf += ($v['ggmoney'] * $v['fwf']) < 1 ? 1 : ($v['ggmoney'] * $v['fwf']);
            }
        }

        $this->assign('member',$member);
        $this->assign('lists',$lists);
        $this->assign('nickname',$nickname);
        $this->assign('orderNo',$orderNo);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);
        $this->assign('num',$num);
        $this->assign('money',$money);
        $this->assign('fwf',$fwf);
        $this->assign('dic',$dic);

        return view();
    }



    public function fx_lists()
    {
        $shopname = input("shopname") ? input("shopname") : '';
        $orderNo = input("orderNo") ? input("orderNo") : '';
        $starttime = input("starttime");
        $endtime = input("endtime");
        $dic = input("dic") ? input("dic") : 0;
        
        if($dic == 0){
            $where = '`state` = 7 and `status` = 0';
        } else {
            $where = '`state` >=2 and `status` = 1';
        }

        if($shopname){
            $lists = db("member")->where("shopname","like","%$shopname%")->select();

            if($lists) {
                $where .= ' and (';
                foreach ($lists as $v) {
                    $where .= '`cid` = ' . $v['id'] . ' or';
                }
                $where = substr($where, 0, strlen($where) - 3);
                $where .= ')';
            }

        }

        if($orderNo){
            $where .= ' and `orderNo` like "%'.$orderNo.'%"';
        }

        if($starttime){
            $where .= ' and `paytime` > '.strtotime($starttime.'00:00:00');
        }
        if($endtime){
            $where .= ' and `paytime` < '.strtotime($endtime.'23:59:59');
        }

        $lists = db("order")->where($where)->order("`paytime` asc")->paginate(500,false,['query'=>['shopname'=>$shopname,'orderNo'=>$orderNo,'starttime'=>$starttime,'endtime'=>$endtime]]);
        $pages = $lists->render();

        $this->assign('lists',$lists);
        $this->assign('pages',$pages);

        $this->assign('shopname',$shopname);
        $this->assign('orderNo',$orderNo);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);
        $this->assign('dic',$dic);

        return view();
    }

    public function pay_lists()
    {
        $shopname = input("shopname") ? input("shopname") : '';
        $orderNo = input("orderNo") ? input("orderNo") : '';
        $starttime = input("starttime");
        $endtime = input("endtime");

        $where = '`title` = "线下支付" and `ggid` = 0';
        if($shopname){
            $where .= ' and (';
            $lists = db("member")->where("shopname","like","%$shopname%")->select();
            foreach($lists as $v){
                $where .= '`cid` = '.$v['id'].' or';
            }
            $where = substr($where,0,strlen($where)-3);
            $where .= ')';
        }
        if($orderNo){
            $where .= ' and `orderNo` like "%'.$orderNo.'%"';
        }
        if($starttime){
            $where .= ' and `intime` > '.strtotime($starttime.'00:00:00');
        }
        if($endtime){
            $where .= ' and `intime` < '.strtotime($endtime.'23:59:59');
        }

        $lists = db("order")->where($where)->order("`intime` desc")->paginate(500,false,['query'=>['shopname'=>$shopname,'orderNo'=>$orderNo,'starttime'=>$starttime,'endtime'=>$endtime]]);
        $pages = $lists->render();

        $all = $allMoney = 0;
        $Lists = db("order")->where($where)->select();
        foreach($Lists as $v){
            $all++;
            if($v['state'] == 7) {
                $allMoney += $v['ggmoney'];
            }
        }

        if(Request::instance()->isPost()){
            $submit = input("submit");
            if($submit) {
                $lists = db("order")->where(['title'=>'线下支付','ggid'=>0])->order("intime desc")->select();
                $index = 1;
                foreach($lists as $k => $v){
                    $nickname = db("member")->where("openid",$v['openid'])->value("nickname");
                    $stateName = '';
                    switch ($v['state']){
                        case 1:$stateName = '未支付';break;
                        case 2:$stateName = '已支付';break;
                        case 7:$stateName = '已完成';break;
                        default:$stateName = '未支付';
                    }
                    $lists[$k]['index'] = $index;
                    $lists[$k]['ggmoney'] = xiaoshu($v['ggmoney'],1);
                    $lists[$k]['nickname'] = $nickname;
                    $lists[$k]['stateName'] = $stateName;
                    $lists[$k]['intime'] = date("Y-m-d H:i:s",$v['intime']);
                    $index++;
                }
                $indexKey = array('index', 'nickname', 'orderNo', 'title', 'ggmoney', 'number', 'stateName', 'intime');
                $indexName = array('ID','下单人微信昵称','订单号','商品','单价','数量','状态','下单时间');
                $indexWidth = array('10','30','25','15','12','10','20','25');
                toExcel($lists, '订单数据'.date("Y-m-d", time()), $indexKey, $indexName, $indexWidth);
            }
        }

        $this->assign('lists',$lists);
        $this->assign('pages',$pages);

        $this->assign('shopname',$shopname);
        $this->assign('orderNo',$orderNo);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);
        $this->assign('all',$all);
        $this->assign('allMoney',$allMoney);

        return view();
    }

    public function pay_succ()
    {
        $id = input("id");

        $data = db("order")->where("id", $id)->find();
        $website = db("website")->where("id",1)->find();
        $member = db("member")->where("id",$data['cid'])->find();

        $money = $data['ggmoney'];
        $orderId = $data['id'];
        $website['scale'] = $member['scale'] ? $member['scale'] : $website['scale'];
        $website['fwf'] = $member['fwf'] ? $member['fwf'] : $website['fwf'];

        $info = array();
        $info['openid'] = $data['openid'];
        $info['orderId'] = $orderId;
        $info['money'] = $money;
        $info['state'] = 2;
        $info['type'] = '-';
        $info['msg'] = "线下订单支付";
        $info['intime'] = time();
        db("record_pay")->insert($info);
        
        $info = array();
        $info['openid'] = $member['openid'];
        $info['orderId'] = $orderId;
        $info['money'] = $money;
        $info['scale'] = $website['scale'];
        $info['fwf'] = $website['fwf'];
        $info['state'] = 2;
        $info['type'] = '+';
        $info['msg'] = "线下订单支付";
        $info['intime'] = time();
        db("record_pay")->insert($info);

        $money = floor($money*(1-($website['scale']+$website['fwf'])));
        $money = $money < 1 ? 1 : $money;

        db("order")->where("id", $data['id'])->update(['state' => 7, 'paytime' => $data['intime'], 'scale'=>0,'fwf'=>$website['fwf'], 'transaction_id' => $data['transaction_id']]);
        db("member")->where("openid",$member['openid'])->setInc("money",$money);

        orderFx($data['cid']);
        
        return 'succ';
    }

    public function close_order()
    {
        $id = input("id");

        db("order")->where("id",$id)->update(['close'=>1]);

        return "success";
    }

    public function fd()
    {
        $id = input("id");

        $data = db("member")->where("id",$id)->find();

        if(Request::instance()->isPost()) {
            $scale = input("scale");
            $fwf = input("fwf");
            db("member")->where("id",$id)->update(['scale'=>$scale,'fwf'=>$fwf]);

            echo "<script>alert('设置成功');history.go(-1);</script>";
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    public function tx_lists()
    {
      /*   $nickname = input("nickname");
        $starttime = input("starttime");
        $endtime = input("endtime");
        $orderNo = input("orderNo");

        $where = 'rt.`id` > 0 and rt.`type` = 3';
        if($nickname){
            $where .= ' and m.`nickname` = "'.$nickname.'"';
        }
        if($starttime){
            $where .= ' and rt.`intime` > '.strtotime($starttime.'00:00:00');
        }
        if($endtime){
            $where .= ' and rt.`intime` < '.strtotime($endtime.'23:59:59');
        }
        if($orderNo){
            $where .= ' and rt.`orderNo` like "%'.$orderNo.'%"';
        }

        $lists = db("record_tixian")->alias("rt")
                ->join("member m","m.`openid` = rt.`openid`")
                ->field("rt.*")
                ->where($where)->order("rt.`id` desc")
                ->paginate(500,false,['query'=>['starttime'=>$starttime,'endtime'=>$endtime]]);
        $pages = $lists->render();

        if(Request::instance()->isPost()){
            $submit = input("submit");
            if($submit) {
                $lists = db("record_tixian")->where($where)->order("`id` desc")->select();
                $index = 1;
                foreach($lists as $k => $v){
                    $data = db("member")->where("openid",$v['openid'])->find();
                    $lists[$k]['index'] = $index;
                    $lists[$k]['money'] = xiaoshu($v['money'],1);
                    $lists[$k]['nickname'] = $data['nickname'];
                    $lists[$k]['shopname'] = $data['shopname'] ? $data['shopname'] : '普通会员';
                    $lists[$k]['intime'] = date("Y-m-d H:i:s",$v['intime']);
                    $index++;
                }
                $indexKey = array('index', 'nickname', 'shopname', 'orderNo', 'money', 'intime');
                $indexName = array('ID','会员昵称','店铺名称','订单号','提现金额','提现时间');
                $indexWidth = array('10','30','25','15','12','25');
                toExcel($lists, '订单数据'.date("Y-m-d", time()), $indexKey, $indexName, $indexWidth);
            }
        }

        $this->assign('lists',$lists);
        $this->assign('pages',$pages);

        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);
        $this->assign('nickname',$nickname);
        $this->assign('orderNo',$orderNo);

        return view(); */
		
		  $where = array();
		$time = input("time");
		
		if($time ==''){
			$time=date("Y-m-d");
		}

		//echo $time;die;
      
		$lists = db("record_tixian")->where(["state"=>0,"intime"=>["between", [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')]]])->order('intime desc')->paginate(50,false,["query"=>["time"=>$time]]);
		//var_dump($lists);die;

        $pages = $lists->render();
        if (Request::instance()->isPost()) {
            if(input("dc")) {
                $arr = [];
                $lists = db("record_tixian")->where(["state"=>[">",11],"intime"=>["between", [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')]]])->order('intime desc')->select();
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
	
	public function sjtx_lists()
    {
      
		
		  $where = array();
		$time = input("time");
		
		if($time ==''){
			$time=date("Y-m-d");
		}

		//echo $time;die;
      
		$lists = db("record_tixian")->where(["type"=>1,"state"=>0,"intime"=>["between", [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')]]])->order('intime desc')->paginate(50,false,["query"=>["time"=>$time]]);
		//var_dump($lists);die;

        $pages = $lists->render();
        if (Request::instance()->isPost()) {
            if(input("dc")) {
                $arr = [];
                $lists = db("record_tixian")->where(["type"=>1,"state"=>[">",11],"intime"=>["between", [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')]]])->order('intime desc')->select();
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

    public function money_edit ()
    {
        $id = input("id");

        $info = db("member")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $arr = input();
            $arr["money"] = $arr["money"]*100;
            $arr["collage_money"] = $arr["collage_money"]*100;
            unset($arr["id"]);

            db("member")->where("id",$id)->update($arr);

            header("location:".url("money_edit","id=".$id));
            die();
        }

        $this->assign('info',$info);

        return view();
    }    
	public function level_edit ()
    {
        $id = input("id");

        $info = db("member")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $arr = input();
			//echo $arr["fx_top_a"];die;
			if($arr["fx_top_a"] == 0){
				$arr["fx_top_a"]=null;
			}
            $arr["fx_top_a"] = $arr["fx_top_a"];
            unset($arr["id"]);
            db("member")->where("id",$id)->update($arr);

            echo"<script>history.go(-1);</script>";



            //header("location:".url("level_edit","id=".$id));
            die();
        }

        $this->assign('info',$info);

        return view();
    }

    public function collage_order ()
    {
        $id = input("id");

        $data = db("member")->where("id",$id)->find();
        $lists = db("order")->where(["id"=>[">",4625],"openid"=>$data["openid"],"collage"=>["in",'2,3']])->order("cid desc,paytime desc")->paginate(30);
        //$lists = array_merge($lists,$listsss);
        $pages = $lists->render();
        $this->assign('lists',$lists);
		$this->assign('openid',$data['openid']);
						$this->assign('data',$data);

        $this->assign('pages',$pages);

        return view();
    }
    public function collage_orders ()
    {
        $id = input("id");
        $data = db("member")->field("openid")->where("id",$id)->find();
		$lists = db("order_collage_item")->where(["openid"=>$data["openid"],"collage_state"=>[">=",2]])->order("title desc,pay_time desc")->paginate(30);

        //$lists = array_merge($lists,$listsss);
        $pages = $lists->render();
        $this->assign('lists',$lists);
		$this->assign('openid',$data['openid']);
        $this->assign('pages',$pages);

        return view();
    }
	
	    public function collage_orderss ()
    {
        $id = input("id");
        $data = db("member")->where("id",$id)->find();
		$lists = db("order_collage_item")->where(["openid"=>$data["openid"],"collage_state"=>["between", '2,3']])->order("title desc,pay_time desc")->paginate(30);

        //$lists = array_merge($lists,$listsss);
        $pages = $lists->render();
        $this->assign('lists',$lists);
		$this->assign('openid',$data['openid']);
				$this->assign('data',$data);

        $this->assign('pages',$pages);

        return view();
    }
	
	public function collage_ordersss ()
    {
        $id = input("id");
        $data = db("member")->where("id",$id)->find();
		$lists = db("order_collage_item")->where(["openid"=>$data["openid"],"collage_state"=>["between", '2,3'],"goodsid"=>150])->order("title desc,pay_time desc")->paginate(30);

        //$lists = array_merge($lists,$listsss);
        $pages = $lists->render();
        $this->assign('lists',$lists);
		$this->assign('openid',$data['openid']);
				$this->assign('data',$data);

        $this->assign('pages',$pages);

        return view();
    }	
	
	public function collage_ordersss_liushui ()
    {
        $id = input("id");
        $data = db("member")->where("id",$id)->find();
				$lists = db("record_collage")->where(["state"=>4,"openid"=>$data['openid']])->whereTime('ctime', 'd')->order('ctime desc')->paginate(50,false);

        //$lists = array_merge($lists,$listsss);
        $pages = $lists->render();
        $this->assign('lists',$lists);
		$this->assign('openid',$data['openid']);
				$this->assign('data',$data);

        $this->assign('pages',$pages);

        return view();
    }
	

    public function collage_order_error ()
    {
        $id = input("id");

        $data = db("member")->where("id",$id)->value("collage_error");
        $data = string2array($data);

        $arr = db("content")->field("id,title")->where(["collage"=>1])->order("id desc")->select();

        if(Request::instance()->isPost()) {
            $info = input("post.");

            db("member")->where("id",$id)->setField("collage_error",array2string($info));

            header("location:".url("collage_order_error","id=".$id));
            die();
        }

        $this->assign('arr',$arr);
        $this->assign('data',$data);

        return view();
    }

    public function collage_record ()
    {
        $id = input("id");
        $time = input("time");

        $openid = db("member")->where("id",$id)->value("openid");

        $lists = db("content")->field("id,title,collage_red_bag,collage_money_a,collage_money_c")->where("collage",1)->order("id desc")->select();
        foreach ($lists as $k => $v) {

            $count_all = 0;                     // 拼团订单数
            $count_success = 0;                 // 拼团成功数
            $count_error = 0;                   // 拼团失败数
            $count_error_time = 0;              // 到期自动退款数
            $count_error_stock = 0;             // 库存不足退款数
            $money_all = 0;                     // 拼团金额
            $money_success = 0;                 // 拼团成功金额
            $money_error = 0;                   // 拼团失败金额
            $money_error_time = 0;              // 到期自动退款金额
            $money_error_stock = 0;             // 库存不足退款金额
            $money_red_bag = 0;                 // 拼团红包金额
            $money_fx = 0;                      // 下级返现金额

            if ($time) {
                $arr = db("order")->where(["id"=>[">",4625],"openid"=>$openid,"cid"=>$v["id"],"status"=>4,"state"=>[">",1],"intime"=>["between", [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')]]])->select();
                foreach ($arr as $r) {
                    if ($r["collage"] == 2) {
                        $count_success++;
                        $money_success = $money_success + $r["ggmoney"];
                    }
                    if ($r["refund_id"] > 2) {
                        $count_error++;
                        $money_error = $money_error + $r["ggmoney"];
                    }
                    if ($r["collage"] == 4) {
                        $count_error_time++;
                        $money_error_time = $money_error_time + $r["ggmoney"];
                    }
                    if ($r["collage"] == 5) {
                        $count_error_stock++;
                        $money_error_stock = $money_error_stock + $r["ggmoney"];
                    }

                    $count_all++;
                    $money_all = $money_all + $r["ggmoney"];
                }

                $collage_red_bag = $v["collage_red_bag"] * 100;
                $collage_money_a = $v["collage_money_a"] * 100;
                $collage_money_c = $v["collage_red_bag"] * 100;

                $money_red_bag = db("record_pay")->where(["openid"=>$openid,"state"=>18,"money"=>$collage_red_bag,"intime"=>["between", [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')]]])->sum("money");
                $money_fx = db("record_pay")->where(["openid"=>$openid,"state"=>["in","13,15"],"money"=>["in","$collage_money_a,$collage_money_c"],"intime"=>["between", [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')]]])->sum("money");

                $lists[$k]["count_all"] = $count_all;
                $lists[$k]["count_success"] = $count_success;
                $lists[$k]["count_error"] = $count_error;
                $lists[$k]["count_error_time"] = $count_error_time;
                $lists[$k]["count_error_stock"] = $count_error_stock;
                $lists[$k]["money_all"] = $money_all;
                $lists[$k]["money_success"] = $money_success;
                $lists[$k]["money_error"] = $money_error;
                $lists[$k]["money_error_time"] = $money_error_time;
                $lists[$k]["money_error_stock"] = $money_error_stock;
                $lists[$k]["money_red_bag"] = $money_red_bag;
                $lists[$k]["money_fx"] = $money_fx;
            }
        }

        $this->assign('time',$time);
        $this->assign('lists',$lists);

        return view();
    }
	
	
	    public function refund_aaa ()
    {
        $id = input("id");

        $info = db("order_collage_item")->field("pid, gg_money, number, transaction_id,openid,orderNo,collage_red_bag,collage_state")->where("id",$id)->find();
		
		
		if($info['collage_state'] == 3){
			return '订单已经是失败订单';
		}
		
		
		
		//取transaction_id前六位 888800为余额支付订单否则为微信支付
		$isweixin= substr($info['transaction_id'] , 0 , 6);
		
		if($isweixin =='888800'){
			///
			$collage_money = ($info["gg_money"] * $info["number"])*100;		
			$out_refund_no = orderNo();
			$refund = db("member")->where("openid",$info['openid'])->setInc("collage_money", $collage_money);
			$refund_id ='666680'.time();
			if($refund){
				db("order_collage_item")->where("id",$id)->update(["collage_state"=>3, "refund_id"=>$refund_id, "refund_no"=>$out_refund_no]);

				$many=0;
				$info_record = [];
				$info_record['openid'] = $info["openid"];
				$info_record['orderNo'] = $info["orderNo"];
				$info_record['money'] = $info['collage_red_bag'];
				$info_record['state'] = 4;
				$info_record['state_many'] = $many;
				$info_record['type'] = '+';
				$info_record['msg'] = "拼团失败鼓励金";
				db("record_collage")->insert($info_record);
				
				db("member")->where("openid",$info["openid"])->setInc("collage_money",$info['collage_red_bag'] * 100);
				db("member")->where("openid",$info["openid"])->setInc("collage_money_a",$info['collage_red_bag']);


				return "success";
			}else{
				db("order_collage_item")->where("id",$id)->update(["refund_err_code_des"=>'更改订单状态为拼团失败出现错误', "refund_no"=>$out_refund_no]);
				return '拼团已满订单取消退回鼓励金失败';
					
			}
		}else{
			$weChat = get_wechat();
			$collage_money = $info["gg_money"] * $info["number"];
			$out_refund_no = orderNo();

			$url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
			$data = [
				'appid' => $weChat['appid'],
				'mch_id' => '1550831061',
				'nonce_str' => encrypt(32),
				'transaction_id' => $info["transaction_id"],
				'out_refund_no' => $out_refund_no,
				'total_fee' => $collage_money*100,
				'refund_fee' => $collage_money*100,
				'refund_desc' => '商家退款'
			];
			$data = array_filter($data);
			ksort($data);
			$str = '';
			foreach ($data as $k => $i) {
				$str .= $k . '=' . $i . '&';
			}
			$str .= 'key=8s5r5cfqd453e775dq95795cy7b09x9d';
			$data['sign'] = md5($str);
			$xml = arraytoxml($data);
			$res = curl($xml, $url);
			$res_arr = xmltoarray($res);
			$dirname = "./logs/refund/" . date("Ymd");
			if (!file_exists($dirname)) mkdir($dirname, 0755, true);
			$myFile = fopen($dirname . "/logs-" . date("H") . " o'clock.txt", "a");
			fwrite($myFile, "transaction_id:{$info['transaction_id']}\n\r" . $res);
			fwrite($myFile, "\n\r\n\r");
			fclose($myFile);
			if ($res_arr["return_code"] == "SUCCESS" && $res_arr["result_code"] == "SUCCESS") {
				db("order_collage_item")->where("id",$id)->update(["collage_state"=>3, "refund_id"=>$res_arr["refund_id"], "refund_no"=>$out_refund_no]);
				
				
				
				//db("order_collage_item")->where("id",$id)->update(["collage_state"=>3, "refund_id"=>$refund_id, "refund_no"=>$out_refund_no]);

				$many=0;
				$info_record = [];
				$info_record['openid'] = $info["openid"];
				$info_record['orderNo'] = $info["orderNo"];
				$info_record['money'] = $info['collage_red_bag'];
				$info_record['state'] = 4;
				$info_record['state_many'] = $many;
				$info_record['type'] = '+';
				$info_record['msg'] = "拼团失败鼓励金";
				db("record_collage")->insert($info_record);
				
				db("member")->where("openid",$info["openid"])->setInc("collage_money",$info['collage_red_bag'] * 100);
				db("member")->where("openid",$info["openid"])->setInc("collage_money_a",$info['collage_red_bag']);




			} else {
				if (isset($res_arr["err_code_des"])) {
					db("order_collage_item")->where("id",$id)->update(["refund_err_code_des"=>$res_arr["err_code_des"], "refund_no"=>$out_refund_no]);
					return $res_arr["err_code_des"];
				}
			}
			return "success";
		}
    }
	
	public function refund_aaaa ()
    {
        $id = input("id");
        $info = db("order")->where("id",$id)->find();
		if($info['collage'] == 3){
			return '订单已经是失败订单';
		}
		
			$weChat = get_wechat();
			$collage_money = $info["ggmoney"] * $info["number"];
			$out_refund_no = orderNo();

			$url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
			$data = [
				'appid' => $weChat['appid'],
				'mch_id' => '1550831061',
				'nonce_str' => encrypt(32),
				'transaction_id' => $info["transaction_id"],
				'out_refund_no' => $out_refund_no,
				'total_fee' => $collage_money*100,
				'refund_fee' => $collage_money*100,
				'refund_desc' => '商家退款'
			];
			$data = array_filter($data);
			ksort($data);
			$str = '';
			foreach ($data as $k => $i) {
				$str .= $k . '=' . $i . '&';
			}
			$str .= 'key=8s5r5cfqd453e775dq95795cy7b09x9d';
			$data['sign'] = md5($str);
			$xml = arraytoxml($data);
			$res = curl($xml, $url);
			$res_arr = xmltoarray($res);
			$dirname = "./logs/refund/" . date("Ymd");
			if (!file_exists($dirname)) mkdir($dirname, 0755, true);
			$myFile = fopen($dirname . "/logs-" . date("H") . " o'clock.txt", "a");
			fwrite($myFile, "transaction_id:{$info['transaction_id']}\n\r" . $res);
			fwrite($myFile, "\n\r\n\r");
			fclose($myFile);
			if ($res_arr["return_code"] == "SUCCESS" && $res_arr["result_code"] == "SUCCESS") {
				db("order")->where("id",$id)->update(["collage"=>3, "refund_id"=>$res_arr["refund_id"], "out_refund_no"=>$out_refund_no]);
				//db("order_collage_item")->where("id",$id)->update(["collage_state"=>3, "refund_id"=>$refund_id, "refund_no"=>$out_refund_no]);
				$many=0;
				$info_record = [];
				$info_record['openid'] = $info["openid"];
				$info_record['orderNo'] = $info["orderNo"];
				$info_record['money'] = $info['collage_red_bag'];
				$info_record['state'] = 4;
				$info_record['state_many'] = $many;
				$info_record['type'] = '+';
				$info_record['msg'] = "拼团失败鼓励金";
				db("record_collage")->insert($info_record);
				db("member")->where("openid",$info["openid"])->setInc("collage_money",$info['collage_red_bag'] * 100);
				db("member")->where("openid",$info["openid"])->setInc("collage_money_a",$info['collage_red_bag']);
			} else {
				if (isset($res_arr["err_code_des"])) {
					db("order")->where("id",$id)->update(["refund_err_code_des"=>$res_arr["err_code_des"], "refund_no"=>$out_refund_no]);
					return $res_arr["err_code_des"];
				}
			}
			return "success";
		
    }	
	public function refund_aaaaa ()
    {
        $id = input("id");
        $info = db("order")->where("id",$id)->find();
		if($info['collage'] == 3){
			return '订单已经是失败订单';
		}
			$out_refund_no = orderNo();
			db("order")->where("id",$id)->update(["collage"=>3, "refund_id"=>$out_refund_no, "out_refund_no"=>$out_refund_no]);
				//db("order_collage_item")->where("id",$id)->update(["collage_state"=>3, "refund_id"=>$refund_id, "refund_no"=>$out_refund_no]);
			return "success";
		
    }	
	public function refund_aaaaaa ()
    {
        $id = input("id");
        $info = db("order_collage_item")->where("id",$id)->find();
		if($info['collage_state'] == 3){
			return '订单已经是失败订单';
		}
			$out_refund_no = orderNo();
			db("order_collage_item")->where("id",$id)->update(["collage_state"=>3, "refund_id"=>$out_refund_no, "refund_no"=>$out_refund_no]);
				//db("order_collage_item")->where("id",$id)->update(["collage_state"=>3, "refund_id"=>$refund_id, "refund_no"=>$out_refund_no]);
			return "success";
		
    }
	
	
		public function shunxu_edit ()
    {
        $id = input("id");

        $info = db("order_collage_item")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $arr = input();
            $arr["collage_count"] = $arr["collage_count"] - 1;
            unset($arr["id"]);
            db("order_collage_item")->where("id",$id)->update($arr);
            echo"<script>history.go(-1);</script>";
            die();
        }

        $this->assign('info',$info);

        return view();
    }		
	
	public function shunxu_edits ()
    {
        $id = input("id");

        $info = db("order")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $arr = input();
            $arr["collage_count"] = $arr["collage_count"] - 1;
            unset($arr["id"]);
            db("order")->where("id",$id)->update($arr);
            echo"<script>history.go(-1);</script>";
            die();
        }

        $this->assign('info',$info);

        return view();
    }
	
		public function order_del()
    {
        $id = input("id");
        $info = db("order")->where("id",$id)->find();
		if(!$info['collage']){
			return '订单不存在';
		}
			db("order")->where("id",$id)->delete();
				//db("order_collage_item")->where("id",$id)->update(["collage_state"=>3, "refund_id"=>$refund_id, "refund_no"=>$out_refund_no]);
			return "success";
		
    }		
	
	public function order_dels()
    {
        $id = input("id");
        $info = db("order_collage_item")->where("id",$id)->find();
		if(!$info['collage_state']){
			return '订单不存在';
		}
			db("order_collage_item")->where("id",$id)->delete();
				//db("order_collage_item")->where("id",$id)->update(["collage_state"=>3, "refund_id"=>$refund_id, "refund_no"=>$out_refund_no]);
			return "success";
    }	
	
	
	
	public function order_del_liuhsui()
    {
        $id = input("id");
		
		//echo $id;die;
        $info = db("record_collage")->where("id",$id)->find();
		if(!$info){
			return '记录不存在';
		}
			db("record_collage")->where("id",$id)->delete();
				//db("order_collage_item")->where("id",$id)->update(["collage_state"=>3, "refund_id"=>$refund_id, "refund_no"=>$out_refund_no]);
			return "success";
    }
	
	public function tixian_list(){
		
		$id = input("id");

        $data = db("member")->where("id",$id)->find();
        $lists = db("record_tixian")->where(["openid"=>$data["openid"]])->order("intime desc")->paginate(30);
        //$lists = array_merge($lists,$listsss);
        $pages = $lists->render();
        $this->assign('lists',$lists);
		$this->assign('openid',$data['openid']);
						$this->assign('data',$data);

        $this->assign('pages',$pages);

        return view();
		
		
		
		
	}
	
	
	
}
