<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Order extends Controller
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
        $state = input("state") ? input("state") : 1;
        $orderNo = input("orderNo");
        $starttime = input("starttime");
        $endtime = input("endtime");

        $where = "`state` > 0 and `ggid` != 0";
        if ($state == 7) {
            $where .= " and (`state` = 5 or `state` = 7)";
        } else {
            $where .= " and `state` = " . $state;
        }
        if ($orderNo) {
            $where .= " and `orderNo` like '%" . $orderNo . "%'";
        }

        if($starttime){
            $where .= ' and `paytime` > '.strtotime($starttime.'00:00:00');
        }
        if($endtime){
            $where .= ' and `paytime` < '.strtotime($endtime.'23:59:59');
        }

        $lists = db("order")->where($where)->order("intime desc")->paginate(30,false,['query'=>['state'=>$state, 'orderNo'=>$orderNo, "starttime"=>$starttime, "endtime"=>$endtime]]);
        $pages = $lists->render();

        if(Request::instance()->isPost()){
            $submit = input("submit");

            if($submit) {
                $lists = db("order")->where($where)->order("intime desc")->select();
                $index = 1;
                foreach($lists as $k => $v){
                    $nickname = db("member")->where("openid",$v['openid'])->value("nickname");
                    $stateName = '';
                    switch ($v['state']){
                        case 1:$stateName = '未支付';break;
                        case 2:$stateName = '已支付,待发货';break;
                        case 3:$stateName = '已发货，待收货';break;
                        case 4:$stateName = '已收货，未评价';break;
                        case 5:$stateName = '已收货，已评价';break;
                        case 6:$stateName = '6';break;
                        case 7:$stateName = '已完成';break;
                        default;
                    }
                    if($stateName == 6){
                        $data = db("order_refund")->where("orderid",$r['id'])->find();
                        switch ($data['state']){
                            case 0:$stateName = '售后/退款 待审核';break;
                            case 1:$stateName = '售后/退款 审核成功';break;
                            default:$stateName = '售后/退款 审核失败';
                        }
                    }
                    if($v['status'] == 1){$stateName = '线上刷单 '.$stateName;}
                    $lists[$k]['index'] = $index;
                    $lists[$k]['ggmoney'] = xiaoshu($v['ggmoney'],1);
                    $lists[$k]['nickname'] = $nickname;
                    $lists[$k]['stateName'] = $stateName;
                    $lists[$k]['address'] = "河北省保定市".$v["address"];
                    $lists[$k]['intime'] = date("Y-m-d H:i:s",$v['intime']);
                    $index++;
                }
                $indexKey = array('index', 'nickname', 'orderNo', 'title', 'ggtitle', 'ggmoney', 'number', 'stateName', 'name', 'phone', 'address', 'intime');
                $indexName = array('ID','下单人微信昵称','订单号','商品','商品规格','单价','数量','状态','收货人姓名','收货人电话','收货人地址','下单时间');
                $indexWidth = array('10','30','25','60','40','12','10','20','15','15','50','25');
                toExcel($lists, '订单数据'.date("Y-m-d", time()), $indexKey, $indexName, $indexWidth);
            }
        }

        $this->assign('lists',$lists);
        $this->assign('pages',$pages);
        $this->assign('state',$state);
        $this->assign('orderNo',$orderNo);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);

        return view();
    }

    public function fh()
    {
        $id = input("id");

        if(Request::instance()->isPost()){
            $status = db("order")->where("id",$id)->value("status");

            $info = input();
            $info['state'] = $status == 4 ? 4 : 3;
            $info['fhtime'] = time();
            db("order")->where("id",$id)->update($info);

            header("location:".url('lists','state=4&status='.$status));
            die();
        }

        return view();
    }

    public function collage_order ()
    {
        $state = input("state") ? input("state") : 8;
        $nickname = input("nickname");
        $openid = input("openid");
        $cid = input("cid");
        $orderNo = input("orderNo");
        $starttime = input("starttime");
        $endtime = input("endtime");

        $where = ["id"=>[">",4625],"status"=>4, "state"=>[">",1], "collage"=>["<",4], "intime"=>[">=",strtotime('2022-07-01 00:00:00')]];
        if ($cid) $where["cid"] = $cid;
        switch ($state) {
            case -1:$where["openid"] = $openid;break;
            case 1:$where["state"] = 1;$where["status"] = 4;$where["transaction_id"] = ["neq",""];break;
            case 2:$where["state"] = 3;break;
            case 3:$where["state"] = 4;break;
            case 4:$where["state"] = 5;break;
            case 7:$where["collage"] = 3;break;
            case 8:$where["state"] = ["in","2,9"];break;
            default:$where["state"] = 6;break;
        }

        if ($nickname) {
            $arr = db("member")->field("openid")->where("nickname",$nickname)->select();
            $str = implode(",",array_column($arr,"openid"));
            $where["openid"] = ["in",$str];
        }

        if ($orderNo) $where["orderNo"] = ["like","%{$orderNo}%"];
        if ($starttime && !$endtime) $where["paytime"] = [">=",strtotime($starttime.'00:00:00')];
        if (!$starttime && $endtime) $where["paytime"] = ["<=",strtotime($endtime.'23:59:59')];
        if ($starttime && $endtime) $where["paytime"] = ["between",[strtotime($starttime.'00:00:00'),strtotime($endtime.'23:59:59')]];


        $lists = db("order")->where($where)->order($state == 1 ? "cid asc" : "intime asc")->paginate(40,false,['query'=>['state'=>$state, 'nickname'=>$nickname, 'openid'=>$openid, 'cid'=>$cid, 'orderNo'=>$orderNo, "starttime"=>$starttime, "endtime"=>$endtime]]);
        $pages = $lists->render();

        if(Request::instance()->isPost()){
            $submit = input("submit");
            if ($submit) {
                $lists = db("order")->where($where)->order("intime desc")->select();
                $index = 1;

                foreach($lists as $k => $v){
                    $nickname = db("member")->where("openid",$v['openid'])->value("nickname");
                    $stateName = '';
                    switch ($v['state']){
                        case 1:$stateName = '未支付';break;
                        case 2:$stateName = '已支付,待发货';break;
                        case 3:$stateName = '拼团成功，待发货';break;
                        case 4:$stateName = '已发货，待收货';break;
                        case 5:$stateName = '已收货，已评价';break;
                        case 6:$stateName = '6';break;
                        case 7:$stateName = '拼团失败';break;
                        default;
                    }
                    if($stateName == 6){
                        $data = db("order_refund")->where("orderid",$r['id'])->find();
                        switch ($data['state']){
                            case 0:$stateName = '售后/退款 待审核';break;
                            case 1:$stateName = '售后/退款 审核成功';break;
                            default:$stateName = '售后/退款 审核失败';
                        }
                    }
                    $address = $v["address"];
                    if (!stristr($address,"保定市"))  $address = "保定市".$address;
                    if (!stristr($address,"河北省"))  $address = "河北省".$address;

                    $lists[$k]['index'] = $index;
                    $lists[$k]['ggmoney'] = xiaoshu($v['ggmoney'],1);
                    $lists[$k]['nickname'] = $nickname;
                    $lists[$k]['stateName'] = $stateName;
                    $lists[$k]['address'] = $address;
                    $lists[$k]['intime'] = date("Y-m-d H:i:s",$v['intime']);
                    $index++;
                }
                $indexKey = array('index', 'nickname', 'orderNo', 'title', 'ggtitle', 'ggmoney', 'number', 'stateName', 'name', 'phone', 'address', 'intime');
                $indexName = array('ID','下单人微信昵称','订单号','商品','商品规格','单价','数量','状态','收货人姓名','收货人电话','收货人地址','下单时间');
                $indexWidth = array('10','30','25','60','40','12','10','20','15','15','50','25');
                toExcel($lists, '订单数据'.date("Y-m-d", time()), $indexKey, $indexName, $indexWidth);
            }
        }

        $this->assign('lists',$lists);
        $this->assign('pages',$pages);
        $this->assign('state',$state);
        $this->assign('nickname',$nickname);
        $this->assign('cid',$cid);
        $this->assign('openid',$openid);
        $this->assign('orderNo',$orderNo);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);

        return view();
    }

    public function show()
    {
        $id = input("id");

        $data = db("order_collage_item")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $info = array();
            $info['fail'] = input("content");
            $info['state'] = input("state");
            $info['shtime'] = time();

            db("order_refund")->where("orderid",$id)->update($info);

            if(input("state") == 1) {
                $money = $data['ggmoney'] * $data['number'];
                db("member")->where("openid", $data['openid'])->setInc("money", $money);
            }

            header("location:".url("lists","state=6"));
            die();
        }
        $this->assign('data',$data);

        return view();
    }

    public function stateChange()
    {
        $id = input("id");
        $state = input("state");

        db("order_sc")->where("id",$id)->update(['state' => $state,'fhtime' => time()]);

        return 'succ';
    }

    public function close_order()
    {
        $id = input("id");

        db("order")->where("id",$id)->update(['close'=>1]);

        return "success";
    }
    
    public function collage_order_b() {
        $lists = db("record_collage")->field("id,money")->where("state",1)->select();
        foreach ($lists as $k => $v) {
            db("record_collage")->where("id",$v["id"])->setField("money",$v["money"]*100);
        }
    }

    public function collage_order_a ()
    {
        $state = input("state") ? input("state") : 1;
        $nickname = input("nickname");
        $orderNo = input("orderNo");
        $starttime = input("starttime");
        $endtime = input("endtime");

        $where = [];

        switch ($state) {
            case 1:$where["collage_state"] = 1;break;
            case 2:$where["collage_state"] = 2;break;
            case 3:$where["collage_state"] = 3;break;
            case 4:$where["collage_state"] = [">",3];break;
            case 5:$where["collage_state"] = 2;break;
        }
        if ($nickname) {
            $arr = db("member")->field("openid")->where("nickname","like","%$nickname%")->select();
            $str = implode(",",array_column($arr,"openid"));
            $where["openid"] = ["in",$str];
        }
        if ($orderNo) $where["orderNo"] = ["like","%{$orderNo}%"];
        if ($starttime && !$endtime) $where["pay_time"] = [">=",$starttime.' 00:00:00'];
        if (!$starttime && $endtime) $where["pay_time"] = ["<=",$endtime.' 23:59:59'];
        if ($starttime && $endtime) $where["pay_time"] = ["between",[$starttime.' 00:00:00',$endtime.' 23:59:59']];
		if($state == 1){
			$orderaa ='title desc,pay_time desc,openid desc';
		}else{
			$orderaa ='pay_time desc';
		}
        $lists = db("order_collage_item")->where($where)->group("id")->order($orderaa)->paginate(40,false,['query'=>['state'=>$state, 'nickname'=>$nickname, 'orderNo'=>$orderNo, "starttime"=>$starttime, "endtime"=>$endtime]]);
        $pages = $lists->render();

        if(Request::instance()->isPost()){
            $submit = input("submit");
            if ($submit) {
                $lists = db("order_collage_item")->where($where)->order("title desc")->select();
                $index = 1;

                foreach($lists as $k => $v){
                    $nickname = db("member")->where("openid",$v['openid'])->value("nickname");
                    $stateName = '';
					
					//拼团状态 0未支付 1已支付 2拼团成功 3拼团失败 4商家退款 5库存不足，商家退款 6订单为多余订单，商家退款 7后台手动操作
                    /* switch ($v['collage_state']){
                        case 0:$stateName = '未支付';break;
                        case 1:$stateName = '已支付,待发货';break;
                        case 2:$stateName = '拼团成功，待发货';break;
                        case 3:$stateName = '已发货，待收货';break;
                        case 4:$stateName = '已收货，已评价';break;
                        case 5:$stateName = '6';break;
                        case 6:$stateName = '拼团失败';break;
						case 7:$stateName = '拼团失败';break;
                        default;
                    }
                    if($stateName == 6){
                        $data = db("order_refund")->where("orderid",$r['id'])->find();
                        switch ($data['state']){
                            case 0:$stateName = '售后/退款 待审核';break;
                            case 1:$stateName = '售后/退款 审核成功';break;
                            default:$stateName = '售后/退款 审核失败';
                        }
                    } */
                    $address = $v["address"];
                    if (!stristr($address,"保定市"))  $address = "保定市".$address;
                    if (!stristr($address,"河北省"))  $address = "河北省".$address;

                    $lists[$k]['index'] = $index;
                    $lists[$k]['ggmoney'] = xiaoshu($v['gg_money']*100);
                    $lists[$k]['nickname'] = $nickname;
                    $lists[$k]['stateName'] = $stateName;
                    $lists[$k]['orderNo'] = '['.$v['orderNo'].']';
                    $lists[$k]['address'] = $address;
                    $lists[$k]['ctime'] = $v['ctime'];
                    $index++;
                }
                $indexKey = array('index', 'nickname', 'orderNo', 'title','ggmoney', 'number','name', 'phone', 'address', 'ctime');
                $indexName = array('ID','下单人微信昵称','订单号','商品','单价','数量','收货人姓名','收货人电话','收货人地址','下单时间');
                $indexWidth = array('10','30','25','60','12','10','15','15','50','25');
				
				
		/* 		  $indexKey = array('index', 'nickname', 'orderNo', 'title', 'gg_title', 'ggmoney', 'number', 'stateName', 'name', 'phone', 'address', 'ctime');
                $indexName = array('ID','下单人微信昵称','订单号','商品','商品规格','单价','数量','状态','收货人姓名','收货人电话','收货人地址','下单时间');
                $indexWidth = array('10','30','25','60','40','12','10','20','15','15','50','25'); */
				//var_dump($lists);die;
                toExcel($lists, '订单数据'.date("Y-m-d", time()), $indexKey, $indexName, $indexWidth);
            }
        }
		
		$listssss = db("order_collage")->whereTime('ctime', 'd')->select();

        $this->assign('lists',$lists);
        $this->assign('listssss',$listssss);
        $this->assign('pages',$pages);
        $this->assign('state',$state);
        $this->assign('nickname',$nickname);
        $this->assign('orderNo',$orderNo);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);

        return view();
    }


public function collage_order_c ()
    {
        $state = input("state") ? input("state") : 1;
        $nickname = input("nickname");
        $orderNo = input("orderNo");
        $starttime = input("starttime");
        $endtime = input("endtime");

        $where = [];

        switch ($state) {
            case 1:$where["collage_state"] = 1;break;
            case 2:$where["collage_state"] = 2;break;
            case 3:$where["collage_state"] = 3;break;
            case 4:$where["collage_state"] = [">",3];break;
            case 5:$where["collage_state"] = 3;break;
        }
        if ($nickname) {
            $arr = db("member")->field("openid")->where("nickname","like","%$nickname%")->select();
            $str = implode(",",array_column($arr,"openid"));
            $where["openid"] = ["in",$str];
        }
        if ($orderNo) $where["orderNo"] = ["like","%{$orderNo}%"];
        if ($starttime && !$endtime) $where["pay_time"] = [">=",$starttime.' 00:00:00'];
        if (!$starttime && $endtime) $where["pay_time"] = ["<=",$endtime.' 23:59:59'];
        if ($starttime && $endtime) $where["pay_time"] = ["between",[$starttime.' 00:00:00',$endtime.' 23:59:59']];
		if($state == 1){
			$orderaa ='title desc,pay_time desc,openid desc';
		}else{
			$orderaa ='pay_time desc';
		}
        $lists = db("order_collage_item")->where($where)->where(["pay_time"=>[">=",$starttime.'2022-10-08 00:00:00']])->group("id")->order($orderaa)->paginate(40,false,['query'=>['state'=>$state, 'nickname'=>$nickname, 'orderNo'=>$orderNo, "starttime"=>$starttime, "endtime"=>$endtime]]);
        $pages = $lists->render();

        if(Request::instance()->isPost()){
            $submit = input("submit");
            if ($submit) {
                $lists = db("order_collage_item")->where($where)->order("title desc")->select();
                $index = 1;

                foreach($lists as $k => $v){
                    $nickname = db("member")->where("openid",$v['openid'])->value("nickname");
                    $stateName = '';
					
					//拼团状态 0未支付 1已支付 2拼团成功 3拼团失败 4商家退款 5库存不足，商家退款 6订单为多余订单，商家退款 7后台手动操作
                    /* switch ($v['collage_state']){
                        case 0:$stateName = '未支付';break;
                        case 1:$stateName = '已支付,待发货';break;
                        case 2:$stateName = '拼团成功，待发货';break;
                        case 3:$stateName = '已发货，待收货';break;
                        case 4:$stateName = '已收货，已评价';break;
                        case 5:$stateName = '6';break;
                        case 6:$stateName = '拼团失败';break;
						case 7:$stateName = '拼团失败';break;
                        default;
                    }
                    if($stateName == 6){
                        $data = db("order_refund")->where("orderid",$r['id'])->find();
                        switch ($data['state']){
                            case 0:$stateName = '售后/退款 待审核';break;
                            case 1:$stateName = '售后/退款 审核成功';break;
                            default:$stateName = '售后/退款 审核失败';
                        }
                    } */
                    $address = $v["address"];
                    if (!stristr($address,"保定市"))  $address = "保定市".$address;
                    if (!stristr($address,"河北省"))  $address = "河北省".$address;

                    $lists[$k]['index'] = $index;
                    $lists[$k]['ggmoney'] = xiaoshu($v['gg_money']*100);
                    $lists[$k]['nickname'] = $nickname;
                    $lists[$k]['stateName'] = $stateName;
                    $lists[$k]['address'] = $address;
                    $lists[$k]['ctime'] = $v['ctime'];
                    $index++;
                }
                $indexKey = array('index', 'nickname', 'orderNo', 'title','ggmoney', 'number','name', 'phone', 'address', 'ctime');
                $indexName = array('ID','下单人微信昵称','订单号','商品','单价','数量','收货人姓名','收货人电话','收货人地址','下单时间');
                $indexWidth = array('10','30','25','60','12','10','15','15','50','25');
				
				
		/* 		  $indexKey = array('index', 'nickname', 'orderNo', 'title', 'gg_title', 'ggmoney', 'number', 'stateName', 'name', 'phone', 'address', 'ctime');
                $indexName = array('ID','下单人微信昵称','订单号','商品','商品规格','单价','数量','状态','收货人姓名','收货人电话','收货人地址','下单时间');
                $indexWidth = array('10','30','25','60','40','12','10','20','15','15','50','25'); */
				//var_dump($lists);die;
                toExcel($lists, '订单数据'.date("Y-m-d", time()), $indexKey, $indexName, $indexWidth);
            }
        }
        $this->assign('lists',$lists);
        $this->assign('pages',$pages);
        $this->assign('state',$state);
        $this->assign('nickname',$nickname);
        $this->assign('orderNo',$orderNo);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);

        return view();
    }
	
    /**
     * @return string
     */
    public function refund ()
    {
        $id = input("id");

        $info = db("order")->field("id, openid, out_trade_no, ggmoney, number, state, paytime, collageNo, transaction_id, refund_id, refund_err_code_des")->where("id",$id)->find();

        if ($info["state"] == 2 || $info["state"] == 1) {
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
                'total_fee' => $collage_money,
                'refund_fee' => $collage_money,
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

            db("order")->where("id", $info["id"])->setField("out_refund_no",$out_refund_no);

            if ($res_arr["return_code"] == "SUCCESS" && $res_arr["result_code"] == "SUCCESS") {
                db("order")->where("id", $info["id"])->update(["state" => 7, 'collage' => 4, "refund_id" => $res_arr["refund_id"], "refund_err_code_des" => ""]);
            } else {
                if (isset($res_arr["err_code_des"])) {
                    db("order")->where("id", $info["id"])->setField("refund_err_code_des", $res_arr["err_code_des"]);

                    return $res_arr["err_code_des"];
                }
            }
        }

        return "success";
    }

    public function refund_a ()
    {
        $id = input("id");

        $info = db("order_collage_item")->field("pid, gg_money, number, transaction_id,openid,orderNo")->where("id",$id)->find();
		
		//取transaction_id前六位 888800为余额支付订单否则为微信支付
		$isweixin= substr($info['transaction_id'] , 0 , 6);
		
		if($isweixin =='888800'){
			///
			$collage_money = ($info["gg_money"] * $info["number"])*100;		
			$out_refund_no = orderNo();
			$refund = db("member")->where("openid",$info['openid'])->setInc("collage_money", $collage_money);
			$refund_id ='666680'.time();
			if($refund){
				db("order_collage_item")->where("id",$id)->update(["collage_state"=>4, "refund_id"=>$refund_id, "refund_no"=>$out_refund_no]);
				$info_record = [];
                $info_record['openid'] = $info["openid"];
                $info_record['orderNo'] = $info["orderNo"];
                $info_record['money'] = $collage_money / 100;
                $info_record['state'] = 6;
                $info_record['state_many'] = 0;
                $info_record['type'] = '+';
                $info_record['msg'] = "拼团已满订单取消退回鼓励金";
                db("record_collage")->insert($info_record);
				return "success";
			}else{
				db("order_collage_item")->where("id",$id)->update(["refund_err_code_des"=>'拼团已满订单取消退回鼓励金失败', "refund_no"=>$out_refund_no]);
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
				db("order_collage_item")->where("id",$id)->update(["collage_state"=>4, "refund_id"=>$res_arr["refund_id"], "refund_no"=>$out_refund_no]);
			} else {
				if (isset($res_arr["err_code_des"])) {
					db("order_collage_item")->where("id",$id)->update(["refund_err_code_des"=>$res_arr["err_code_des"], "refund_no"=>$out_refund_no]);
					return $res_arr["err_code_des"];
				}
			}
			return "success";
		}
		

       

       
    }
}