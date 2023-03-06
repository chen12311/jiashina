<?php



namespace app\handle\controller;



use http\Params;

use think\Controller;

use think\Request;

use think\Loader;
use think\Env;

use think\db\Query;



class Member extends Controller

{

    public function __construct()

    {

        parent::__construct();

    }

    /**

     * 会员首页

     */

    public function index()
    {
        $openid = input("openid");

        $data = db("member")->where("openid",$openid)->find();
        $data['kfphone'] = getData("basic",['id'=>1],"phone");
        $data['collage_count'] = string2array($data['collage_count']);
		
		
		/* if($data['openid']=='oXgpTt4Y-t0HBocYfhxits0F8A74' || $data['openid']=='oXgpTt_1zk6LdjNJHICcCc49tlQA' || $data['openid']=='oXgpTtzZO4ucAw2-fLaOIMhR_r8s'){
			$data['level']=2;
		}else{
			$data['level']=0;
		}  */
		
	/* 	if($data['openid']=='oXgpTt4Y-t0HBocYfhxits0F8A74'||$data['openid']=='oXgpTt_1zk6LdjNJHICcCc49tlQA'||$data['openid']=='oXgpTtzZO4ucAw2-fLaOIMhR_r8s'}){
			$data['level']=2;
		}else{
			$data['level']=0;
		} */

        return json_encode($data);
    }

    /**
     * 我的订单列表

     */

    public function order_lists()
    {
        $openid = input("openid");
        $state = input("state") ? input("state") : 1;
        
        $member = db("member")->where("openid",$openid)->find();
        $arr_a = $member["collage_count"] ? string2array($member["collage_count"]) : [];
        $arr_b = $member["collage_success"] ? string2array($member["collage_success"]) : [];

        if ($state == 1) return json_encode([]);

		        if ($state == 3) return json_encode([]);

        $wheress = '`openid` = "'.$openid.'"';
			if($state == 2){
				 $wheress .= ' and  `collage_state` < 4';
			} elseif($state == 4){
				 $wheress .= ' and `collage_state` >= 4';
				//return json_encode([]);

			}
			//$wheress=
        $order_lists = db("order_collage_item")->field("id,orderNo,title,gg_money as ggmoney,number,collage_tips,collage_state,ctime,collage_count,collage_error")->where($wheress)->where(["openid"=>$openid,"collage_state"=>[">",0]])->order("pay_time desc")->select();
        foreach ($order_lists as $i => $r) {
            $order_lists[$i]["a"] = 1;
            $order_lists[$i]["status"] = 4;
			$order_lists[$i]['intime']=$r['ctime'];
			//$order_lists[$i]['title']='（第'.$r['collage_count']+1.'单）'.$r['title'];
			
			if($r['collage_error'] >0){
				$order_lists[$i]['title']=$r['title'];
			}else{
				$order_lists[$i]['title']='（第'.($r['collage_count'] + 1) .'单）'.$r['title'];
			}
 
      /*       switch ($r["collage_state"]) {
                case 1:$stateName = "拼团中";break;
                case 2:$stateName = "拼团失败，退款在返现资料中领取";break;
                case 3:$stateName = "拼团成功";break;
                default:$stateName = "商家退款";break;
            } */


			switch ($r["collage_state"]) {
                case 1:$stateName = "拼团中";break;
                case 3:$stateName = "拼团失败";break;
                case 2:$stateName = "拼团成功";break;
                default:$stateName = "商家退款";break;
            }
			
		 	if($r['collage_error'] >0 && $r["collage_state"]>1 &&$r["collage_state"]<4){
				$stateName="特殊订单";
			} 
            $order_lists[$i]["stateName"] = $stateName;

            if($r["collage_state"] == 2 && !$r["collage_tips"]) db("order_collage_item")->where("id",$r["id"])->setField("collage_tips","1");
        }

        $where = '`openid` = "'.$openid.'" and (`status` = 0 || `status` = 2 || `status` = 4)';
        if($state == 1){
            $where .= ' and `state` = 1';
        }elseif($state == 2){
            $where .= ' and `state` > 1  and `collage` < 4';
        }elseif($state == 3){
            $where .= ' and `state` = 4';
        }elseif($state == 4){
            $where .= ' and `collage` = 4';
        }elseif($state == 5){
            $where .= ' and `state` = 7';
        }elseif($state == 6){
			
		}

        $lists = db("order")->where($where)->order("`paytime` desc")->select();
        foreach ($lists as $k => $v) {
            $cid = $v['cid'];

            $lists[$k]["a"] = 0;
            $lists[$k]['thumb'] = $v['thumb'];

            if($v['ggid'] == '0') $lists[$k]['thumb'] = '/img/xxzf.jpg';

            if($v['status'] == 4) {
                switch ($v['state']) {
                    case 1:$stateName = '拼团失败';break;
                    case 2:$stateName = '拼团中';break;
                    case 3:$stateName = '拼团成功';break;
                    case 7:$stateName = $v["collage"] == 4 ? "商家退款" : '拼团失败';
                        break;
                    default:$stateName = '';break;
                }

                if(!$v["collage_tips"]) db("order")->where("id",$v["id"])->setField("collage_tips","1");
            } else {
                switch ($v['state']) {
                    case 1:$stateName = '等待买家付款';break;
                    case 2:$stateName = '等待发货中';break;
                    case 3:$stateName = '等待收货中';break;
                    case 4:$stateName = '等待买家评价';break;
                    case 5:$stateName = '交易成功';break;
                    case 7:$stateName = '交易成功';break;
                    default:$stateName = '';break;
                }
            }



			 $lists[$k]['intime']=date("Y-m-d H:i:s",$v['intime']);


			$lists[$k]['title']='（第'.($v['collage_count'] + 1) .'单）'.$v['title'];

			
            if($v['state'] == 6){
                $state = getData("order_refund",['orderid'=>$v['id']],"state");
                switch ($state){
                    case 0:$stateName = '待审核';break;
                    case 1:$stateName = '审核成功';break;
                    case 2:$stateName = '审核失败';break;
                    default:break;
                }
                $lists[$k]['tkstate'] = $state;
            }


			if($v['collage_error_many']== 1){
				$stateName = '拼团成功,系统判定此订单为异常订单';
			}
			
			
            $lists[$k]['fx_name'] = '';
            if($v["status"] < 3) {
                if ($lists[$k]['close'] == 1) {
                    $lists[$k]['fx_name'] = '订单返现关闭';
                } else {
                    if ($lists[$k]['fx_a'] == 1) {
                        $lists[$k]['fx_name'] = '返现完成，共返现' . xiaoshu($lists[$k]['fxmoney']);
                    } elseif ($lists[$k]['fxmoney'] != 0) {
                        $lists[$k]['fx_name'] = '返现中，已返现 ' . xiaoshu($lists[$k]['fxmoney']);
                    } else {
                        $whereA = '`fx_a` = 0 and `close` = 0 and `state` = 7 and `status` = 0 and `cid` = ' . $cid;
                        $id = db("order")->where($whereA)->order("`paytime` asc")->value("id");

                        if ($id) {
                            $whereB = '`id` > ' . $id . ' and `id` <= ' . $v['id'] . ' and `state` = 7 and `status` = 0 and `close` = 0 and `cid` = ' . $cid;
                            $count = db("order")->where($whereB)->order("id desc")->count();

                            if ($count == 0) {
                                $lists[$k]['fx_name'] = '即将返现';
                            } else {
                                $lists[$k]['fx_name'] = $count . '订单后开始返现';
                            }
                        } else {
                            $lists[$k]['fx_name'] = '即将返现';
                        }
                    }
                }

                if($v['status'] == 2){
                    $lists[$k]['fx_name'] = '';
                }
            }


            $lists[$k]['shopName'] = '';
            $lists[$k]['stateName'] = $stateName;
            $lists[$k]['ggmoney'] = xiaoshu($v['ggmoney']);

            if($v['ggid'] == 0){
                $lists[$k]['shopName'] = db("member")->where("id",$v['cid'])->value('shopname');
            }
        }

        $lists = array_merge($order_lists,$lists);
		
	//collage =2  state=3 status=4 openid

        return json_encode($lists);
    }

	/**
	*统计
	*/
	public function order_tj(){
			$openid = input("openid");
			$chenggonga = db("order")->where(["id"=>[">",4625],"openid" => $openid, 'collage' => 2, "state" => 3, "status" => 4])->sum('ggmoney');
		    $chenggongb = db("order_collage_item")->where(["openid" => $openid, 'collage_state' => 2])->sum('gg_money')*100;
			$chenggongdingdana = db("order")->where(["id"=>[">",4625],"openid" => $openid, 'collage' => 2, "state" => 3, "status" => 4])->count();
			$chenggongdingdanb = db("order_collage_item")->where(["openid" => $openid, 'collage_state' => 2])->count();

			$shibaidingdana = db("order")->where(["id"=>[">",4625],"openid" => $openid, 'collage' => 3,"status" => 4])->count();
			$shibaidingdanb = db("order_collage_item")->where(["openid" => $openid, 'collage_state' => 3])->count();


			$shibaidingdanaa = db("order")->where(["id"=>[">",4625],"openid" => $openid, 'collage' => 3, "status" => 4,"out_refund_no"=>["neq",'']])->sum('ggmoney');
		    $shibaidingdanab = db("order_collage_item")->where(["openid" => $openid, 'collage_state' => 3])->sum('gg_money')*100;

			
			
			
			//$error=	db("order")->where(["id"=>[">",4625],"openid" => $openid, 'collage' => 2, "state" => 3, "status" => 4,"collage_error_many"=>1])->sum('ggmoney');

						//var_dump($error);die;

			$data['chenggongdingdan']=$chenggongdingdana+$chenggongdingdanb;
			$data['shibaidingdan']=$shibaidingdana+$shibaidingdanb;
			$data['chenggongjine']= xiaoshu($chenggonga+$chenggongb);
			$data['yingfan']=xiaoshu(($shibaidingdanaa+$shibaidingdanab)*0.25);
			$data['tixianjine']=xiaoshu(db("record_tixian")->where(["openid" => $openid, 'type' => 3])->sum('money'));
			//$data['yifanglj']=xiaoshu(db("record_pay")->where("openid",$openid)->where("state",18)->sum('money') + $error);
			
						$data['yifanglj']=xiaoshu(db("record_pay")->where("openid",$openid)->where("state",18)->sum('money')) +xiaoshu(db("record_collage")->where("openid",$openid)->where("state",4)->sum('money') * 100) ;

		//	$data['yifanglj']=xiaoshu(db("record_pay")->where("openid",$openid)->where("state",18)->sum('money') +xiaoshu(db("record_collage")->where("openid",$openid)->where("state",4)->sum('money') * 100)+ $error);
			$data['tuiguangjin']=xiaoshu(db("record_pay")->where("openid",$openid)->where("msg",'拼团失败推广金')->sum('money'))+xiaoshu(db("record_pay")->where("openid",$openid)->where("msg",'拼团成功推广金')->sum('money'))+xiaoshu(db("record_collage")->where("openid",$openid)->where("state",2)->sum('money')*100)+xiaoshu(db("record_collage")->where("openid",$openid)->where("state",3)->sum('money')*100);

		
		/*/成功订单数
		失败订单数
		成功金额
		失败金额（鼓励金）*25
		拼团提现金额
		已返鼓励金
		
		*/
		    return json_encode($data);

	}

    /**
     * 我的订单详情
     */
    public function order_show()
    {
        $id = input("id");
        $a = input("a");

        if ($a) {
            $data = db("order_collage_item")->where("id",$id)->find();
            $data['shopName'] = '';
            $data['kdmoney'] = 0;
            $data['ggmoney'] = $data["gg_money"] * 100;

            switch ($data['collage_state']) {
                case 1:$stateName = '拼团中';break;
                case 2:$stateName = '拼团成功';break;
                case 3:$stateName = '拼团失败，退款在返现资料中领取';break;
                default:$stateName = '商家退款';break;
            }

            $data['state'] = 2;
            $data['stateName'] = $stateName;
            $data['ggmoney'] = $data['gg_money'];
            $data['intime'] = $data['ctime'];
            $data['paytime'] = $data['pay_time'];
            $data['fhtime'] = '';
            $data['shtime'] = "";
            $data['pjtime'] = '';
        } else {

            $data = getData("order", ['id' => $id]);
            $data['shopName'] = '';

            if ($data['status'] == 4) {
                switch ($data['state']) {
                    case 1:$stateName = '拼团失败';break;
                    case 2:$stateName = '拼团中';break;
                    case 3:$stateName = '拼团成功';break;
                    case 7:$stateName = $data["collage"] == 4 ? "商家退款" : '拼团失败，退款在返现资料中领取';break;
                    default:$stateName = '';break;
                }
            } else {
                switch ($data['state']) {
                    case 1:$stateName = '等待买家付款';break;
                    case 2:$stateName = '等待发货中';break;
                    case 3:$stateName = '等待收货中';break;
                    case 4:$stateName = '等待买家评价';break;
                    case 5:$stateName = '交易成功';break;
                    case 7:$stateName = '交易成功'; break;
                    default:break;
                }
            }

            if ($data['state'] == 6) {
                $array = getData("order_refund", ['orderid' => $id]);

                switch ($array['state']) {
                    case 0:$stateName = '待审核';break;
                    case 1:$stateName = '审核成功';break;
                    case 2:$stateName = '审核失败';break;
                    default:break;
                }
                $data['tkstate'] = $array['state'];
                $data['tkcontent'] = $array['content'];
                $data['tkorderNo'] = $array['orderNo'];
                $data['tkfail'] = $array['fail'];
                $data['tktime'] = date("Y-m-d H:i:s", $array['intime']);
                $data['tkshtime'] = date("Y-m-d H:i:s", $array['shtime']);
            }

            $data['stateName'] = $stateName;
            $data['ggmoney'] = xiaoshu($data['ggmoney']);
            $data['intime'] = date("Y-m-d H:i:s", $data['intime']);
            $data['paytime'] = date("Y-m-d H:i:s", $data['paytime']);
            $data['fhtime'] = $data['fhtime'] ? date("Y-m-d H:i:s", $data['fhtime']) : '';
            $data['shtime'] = date("Y-m-d H:i:s", $data['shtime']);
            $data['pjtime'] = $data['pjtime'] ? date("Y-m-d H:i:s", $data['pjtime']) : '';

            if ($data['ggid'] == '0') {
                $data['thumb'] = '/img/xxzf.jpg';
                $data['shopName'] = db("member")->where("id", $data['cid'])->value('shopname');
            }
        }

        return json_encode($data);
    }


    /**

     * 我的订单取消订单

     */

    public function order_del()

    {

        $id = input("id");



        db("order")->delete($id);



        return "succ";

    }



    /**

     * 我的订单收货

     */

    public function order_sh()

    {

        $id = input("id");



        db("order")->where("id",$id)->update(['state'=>4,'shtime'=>time()]);



        return "succ";

    }



    /**

     * 我的订单评价

     */

    public function order_pj()

    {

        $id = input("id");



        $data = db("order")->field("`ggtitle`,`thumb`")->where("id",$id)->find();



        return json_encode($data);

    }



    /**

     * 我的订单保存评价

     */

    public function order_pj_add()

    {

        $id = input("id");



        $files = request()->file();

        $imgarr = array();

        if($files) {

            foreach ($files as $file) {

                $infos = $file->move('./uploads');

                if ($infos) {

                    $url = $infos->getFilename();

                    $imgarr[] = "/uploads/" . date("Ymd", time()) . '/' . $url;

                }

            }

        }



        $info = array();

        $info['starA'] = input("starA");

        $info['starB'] = input("starB");

        $info['contentPj'] = input("content");

        $info['imgarr'] = array2string($imgarr);

        $info['pjtime'] = time();

        $info['state'] = 7;

        db("order")->where("id",$id)->update($info);



        return 'succ';

    }



    /**

     * 我的订单售后退款

     */

    public function order_refund()

    {

        $id = input("id");



        $data = db("order")->field("`title`,`thumb`,`ggtitle`,`ggmoney`,`number`")->where("id",$id)->find();

        $data['ggmoney'] = xiaoshu($data['ggmoney']);



        return json_encode($data);

    }



    /**

     * 我的订单售后退款

     */

    public function order_refund_add()
    {
        $id = input("id");

        $files = request()->file();

        $imgarr = array();
        if($files) {
            foreach ($files as $file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $imgarr[] = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }
        }

        $info = array();
        $info['orderNo'] = orderNo();
        $info['orderid'] = $id;
        $info['content'] = input("content");
        $info['imgarr'] = array2string($imgarr);
        $info['state'] = 0;
        $info['intime'] = time();

        db("order_refund")->insert($info);
        db("order")->where("id",$id)->update(['state'=>6]);

        return 'succ';
    }



    /**

     * 个人资料

     */

    public function edit()

    {

        $openid = input("openid");



        $info = array();

        $info['name'] = input("name");

        $info['birthday'] = input("birthday");

        $info['phone'] = input("phone");



        db("member")->where("openid",$openid)->update($info);



        return 'succ';

    }



    /**

     * 返现记录

     */

    public function fx_lists()
    {

        $openid = input("openid");

//        $lists = db("record_fx")->where("openid",$openid)->order("id desc")->select();
//
//        foreach($lists as $k => $v){
//
//            $data = db("order")->where("id",$v['orderid'])->find();
//
//            $lists[$k]['title'] = $data['title'];
//
//            $lists[$k]['thumb'] = $data['thumb'] ? $data['thumb'] : '/img/xxzf.jpg';
//
//            $lists[$k]['ggtitle'] = $data['ggtitle'];
//
//            $lists[$k]['ggmoney'] = xiaoshu($data['ggmoney']);
//
//            $lists[$k]['number'] = $data['number'];
//
//            $lists[$k]['money'] = xiaoshu($v['money']);
//
//            $lists[$k]['intime'] = date("Y-m-d H:i:s",$v['intime']);
//
//        }

        $money = db("member")->where("openid",$openid)->value("money");

        $data = array();
        $data['lists'] = "";
        $data['money'] = xiaoshu($money);

        return json_encode($data);
    }

    public function agent_money_lists()
    {
        $openid = input("openid");

//        $lists = db("record_pay")->where(["openid"=>$openid,"state"=>["in","7,8,9,10,11"]])->order("id desc")->select();

//        foreach($lists as $k => $v){
//            $data = db("order")->where("id",$v['orderid'])->find();
//
//            $lists[$k]['title'] = $data['title'];
//            $lists[$k]['thumb'] = $data['thumb'] ? $data['thumb'] : '/img/xxzf.jpg';
//            $lists[$k]['ggtitle'] = $data['ggtitle'];
//            $lists[$k]['ggmoney'] = xiaoshu($data['ggmoney']);
//            $lists[$k]['number'] = $data['number'];
//            $lists[$k]['money'] = xiaoshu($v['money']);
//            $lists[$k]['intime'] = date("Y-m-d H:i:s",$v['intime']);
//        }

        $money = db("member")->where("openid",$openid)->value("agent_game_money");

        $data = array();
        $data['lists'] = "";
        $data['money'] = xiaoshu($money);

        return json_encode($data);
    }

    public function collage_lists ()
    {
        $openid = input("openid");

//        $lists = db("record_pay")->where(["openid"=>$openid,"state"=>12])->order("id desc")->select();

//        foreach($lists as $k => $v){
//            $data = db("order")->where("id",$v['orderid'])->find();
//
//            $lists[$k]['title'] = $data['title'];
//            $lists[$k]['thumb'] = $data['thumb'] ? $data['thumb'] : '/img/xxzf.jpg';
//            $lists[$k]['ggtitle'] = $data['ggtitle'];
//            $lists[$k]['ggmoney'] = xiaoshu($data['ggmoney']);
//            $lists[$k]['number'] = $data['number'];
//            $lists[$k]['money'] = xiaoshu($v['money']);
//            $lists[$k]['intime'] = date("Y-m-d H:i:s",$v['intime']);
//        }

        $money = db("member")->where("openid",$openid)->value("collage_money");

        $data = array();
        $data['lists'] = "";
        $data['money'] = xiaoshu($money);

        return json_encode($data);
    }

    /**

     * 提现申请

     */

    public function tixian()
    {
        $type = input("type");
        $openid = input("openid");

        switch ($type) {
            case 1:$field="money";break;
            case 2:$field="agent_game_money";break;
            case 3:$field="collage_money";break;
            default:;break;
        }

        $money = db("member")->where("openid",$openid)->value($field);

        return xiaoshu($money);
    }



    /**

     * 提现申请保存

     */

    public function tixian_add()
    {
        $type = input("type");
        $openid = input("openid");
        $money = (input("money") ? input("money") : 0) * 100;
        $money = intval($money);
        $orderNo = orderNo();

       //????????????  $tixian = db("member")->where()->value("tixian");
        $tixian = db("website")->where("id",1)->value("tixian");
        if ($tixian) {
//        if ($openid == "oXgpTt4Y-t0HBocYfhxits0F8A74" || $openid == "oXgpTtzB4w5LybP_FXXdlFdFsadY" || $openid == "oXgpTtwrCLYzYK7p8K6Pus-nmqr8") {
            switch ($type) {
                case 1:
                    $field = "money";
                    break;
                case 2:
                    $field = "agent_game_money";
                    break;
                case 3:
                    $field = "collage_money";
                    break;
                default:
                    $field = "";
                    break;
            }

            $member = db("member")->where("openid", $openid)->find();
			/* if($type==1){
				$day= date("j");
				$arr=array('5','10','15','20','25','28');
				$aa = in_array($day,$arr);
				if(!$aa){
					$data["err_code_des"] = "当前不在可提现日期";
					return json_encode($data);
					die();
				}
			}	 */
            if ($member[$field] < $money) {
               $data["err_code_des"] = "操作频繁，请稍后";
				return json_encode($data);
                die();
            }
			
			if($member['tixian'] == 0){
				$data["err_code_des"] = "提现失败";
				return json_encode($data);
                die();
			}
			

            if ($field) {
                $res = db("member")->where("openid", $openid)->setDec($field, $money);

                if ($res) {
                    $res = withdraw($orderNo, $openid, $money, Request::instance()->ip());
                    $data = xmltoarray($res);

                    if ($data['return_code'] == 'SUCCESS' && $data['result_code'] == 'SUCCESS') {
                        $info = array();
                        $info['orderNo'] = $orderNo;
                        $info['openid'] = $openid;
                        $info['money'] = $money;
                        $info['state'] = 0;
                        $info['type'] = $type;
                        $info['intime'] = time();
                        db("record_tixian")->insert($info);
                    } else {
                        db("member")->where("openid", $openid)->setInc($field, $money);
                    }
                }

                return json_encode($data);
            }
        } else {
            $data["err_code_des"] = "操作频繁";
            return json_encode($data);
        }
    }



    /**

     * 提现申请列表

     */

    public function tixian_lists()
    {
        $type = input("type");
        $openid = input("openid");
        $status = input("status");

		$where['openid'] = $openid;
		$wheres['openid'] = $openid;

		
		//$status 1提现记录 2资金流水
		if($status == 1){
			switch ($type){
				case 1:
				    $where['type'] = 1;
				//商城提现记录
					break;
				case 2:
				    $where['type'] = 2;

				//游戏提现记录
					break;
				case 3:
				    $where['type'] = 3;
				//拼团提现记录
					break;
				default:
			}
			$lists =  db("record_tixian")->where($where)->order("id desc")->select();
			foreach ($lists as $k => $v) {
				$lists[$k]['money'] = xiaoshu($v['money']);
				$lists[$k]['intime'] = date("Y-m-d H:i:s", $v['intime']);
			}

		}elseif($status == 2){
			switch ($type){
				case 1:
				    $where['type'] = 1;
					$lists = db("record_business")->where($wheres)->order("id desc")->select();
					foreach ($lists as $i => $r) {
						$lists[$i]['money'] = $r['money'];
						$lists[$i]['intime'] = $r['ctime'];
					}

				//商城资金流水
					break;
				case 2:
				    $where['type'] = 2;
					return json_encode([]);
				//余额资金流水
					break;
				case 3:
				    $where["state"] = ["in","13,15,18"];
				    $wheres['state'] = ["in","2,3,4,9,10"];
					$lists = db("record_pay")->where($where)->order("id desc")->select();
					     foreach ($lists as $k => $v) {
							$lists[$k]['money'] = xiaoshu($v['money']);
							$lists[$k]['intime'] = date("Y-m-d H:i:s", $v['intime']);
						}
					$listss = db("record_collage")->where($wheres)->order("id desc")->select();
					foreach ($listss as $i => $r) {
						$listss[$i]['money'] = $r['money'];
						$listss[$i]['intime'] = $r['ctime'];
					}
					$lists=array_merge($listss,$lists);
				//拼团资金流水
					break;
				default:
			}
			
		}
       /*  if ($type == 3) {
            $db = db("record_tixian");
        } else {
            $where["state"] = ["in","13,15,18"];
            $db = db("record_pay");
        }

        $lists = $db->where($where)->order("id desc")->select();

        foreach ($lists as $k => $v) {
            $lists[$k]['money'] = xiaoshu($v['money']);
            $lists[$k]['intime'] = date("Y-m-d H:i:s", $v['intime']);
        }
		
		if($type != 3){
			$wheres = ["openid"=>$openid];
			$wheres["state"] = ["in","2,3,4,9,10"];
			$listss = db("record_collage")->where($wheres)->order("id desc")->select();

			foreach ($listss as $i => $r) {
				$listss[$i]['money'] = $r['money'];
				$listss[$i]['intime'] = $r['ctime'];
			}
			

			$lists=array_merge($listss,$lists);
			
		} */

        return json_encode($lists);
    }



    /**

     * 我的收藏

     */

    public function like()

    {

        $openid = input("openid");



        $lists = db("like")->field("`id`,`cid`")->where("openid",$openid)->select();

        foreach($lists as $k => $v){

            $contentData = getData("content",['id'=>$v["cid"]]);

            unset($contentData['id']);

            $contentData['money'] = xiaoshu($contentData['money']);

            $lists[$k] = $contentData;

            $lists[$k]['id'] = $v['id'];

            $lists[$k]['cid'] = $v['cid'];

        }



        return json_encode($lists);

    }



    /**

     * 删除收藏

     */

    public function like_del()

    {

        $id = input("id");



        if($id) {

            db("like")->where("id", "in", $id)->delete();

        }



        return 'succ';

    }



    /**

     * 通知公告

     */

    public function notice()

    {

        $lists = db("notice")->order("id desc")->select();



        $where = array();

        $where['openid'] = input("openid");

        foreach($lists as $k => $v){

            $lists[$k]['intime'] = date("Y-m-d H:i:s",$v['intime']);

        }



        return json_encode($lists);

    }



    /**

     * 通知公告删除

     */

    public function notice_del()

    {

        $info = array();

        $info['zid'] = input("id");

        $info['openid'] = input("openid");

        db("notice_del")->insert($info);



        return "succ";

    }



    public function notice_show()
    {

        $id = input("id");
        $openid = input("openid");

        $where = ['openid'=>$openid,'zid'=>$id];
        $count = db("notice_del")->where($where)->count();

        if(!$count) {
            db("notice_del")->insert($where);
            db("member")->where("openid", $openid)->setDec("noticeNum");
        }

        $data = db("notice")->where("id",$id)->find();
        $data['intime'] = date("Y-m-d H:i:s",$data['intime']);

        return json_encode($data);
    }



    /**

     * 店铺申请

     */

    public function shop_add()

    {

        $openid = input("openid");

        $shopname = input("shopname");



        $data = db("member")->where("openid",$openid)->find();



        $shopimg = $data['shopimg'];

        $file = request()->file("shopimg");

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

                $image->thumb(450, 450)->save('.'.$sfzb);

            }

        }



        $yyzz = $data['yyzz'];

        $file = request()->file('yyzz');

        if ($file) {

            $infos = $file->move('./uploads');

            if ($infos) {

                $url = $infos->getFilename();

                $yyzz = "/uploads/" . date("Ymd", time()) . '/' . $url;

                $image = \think\Image::open('.'.$yyzz);

                $image->thumb(450, 450)->save('.'.$yyzz);

            }

        }



        $info = array();

        $info['state'] = -1;

        $info['shopname'] = $shopname;

        $info['shopimg'] = $shopimg;

        $info['sfza'] = $sfza;

        $info['sfzb'] = $sfzb;

        $info['yyzz'] = $yyzz;

        db("member")->where("openid",$openid)->update($info);



        return "succ";

    }



    // 线下支付记录

    public function shop_order_lists(){

        $openid = input("openid");



        $id = db("member")->where("openid",$openid)->value("id");



        $num = $sumMoney = 0;

        $lists = db("order")->where(["cid"=>$id,"state"=>7,"ggid"=>0])->order("id desc")->select();

        foreach($lists as $k => $v){

            $money = floor($v['ggmoney']*(1-($v['scale']+$v['fwf'])));

            $money = $money < 1 ? 1 : $money;

            $lists[$k]['intime'] = date("Y-m-d H:i:s",$v['intime']);

            $lists[$k]['money'] = xiaoshu($v['ggmoney']);

            $lists[$k]['nickname'] = db("member")->where("openid",$v['openid'])->value("nickname");

            $lists[$k]['money_n'] = xiaoshu($money);



            $num++;

            $sumMoney+=$money;

        }



        $data = array();

        $data['lists'] = $lists;

        $data['num'] = $num;

        $data['money'] = xiaoshu($sumMoney);



        return json_encode($data);

    }



    /**

     * 代理商申请

     */

    public function agent_add()
    {

        $openid = input("openid");

        $phone_a = input("phone_a");



        $data = db("member")->where("openid",$openid)->find();



        $sffa_a = $data['sffa_a'];

        $file = request()->file("sffa_a");

        if ($file) {

            $infos = $file->move('./uploads');

            if ($infos) {

                $url = $infos->getFilename();

                $sffa_a = "/uploads/" . date("Ymd", time()) . '/' . $url;

                $image = \think\Image::open('.'.$sffa_a);

                $image->thumb(450, 450)->save('.'.$sffa_a);

            }

        }



        $sffb_a = $data['sffb_a'];

        $file = request()->file('sffb_a');

        if ($file) {

            $infos = $file->move('./uploads');

            if ($infos) {

                $url = $infos->getFilename();

                $sffb_a = "/uploads/" . date("Ymd", time()) . '/' . $url;

                $image = \think\Image::open('.'.$sffb_a);

                $image->thumb(450, 450)->save('.'.$sffb_a);

            }

        }



        $info = array();

        $info['agent'] = -1;

        $info['phone_a'] = $phone_a;

        $info['sffa_a'] = $sffa_a;

        $info['sffb_a'] = $sffb_a;

        db("member")->where("openid",$openid)->update($info);



        return "succ";

    }



    /**

     * 刷单记录

     */

    public function share_order_lists() {

        $openid = input("openid");

        $type = input("type");



        $num = $sumMoney = 0;

        $where = '`openid` = "'.$openid.'" and `status` = 1 and `state` >= 2';

        $lists = db("order")->where($where)->order("`paytime` desc")->select();

        foreach($lists as $k => $v) {

            $cid = $v['cid'];

            $stateName = '交易成功';

            $money = floor($v['ggmoney'] - $v['scale'] - $v['fwf']);



            $num++;

            $sumMoney+=$money;



            $lists[$k]['fx_name'] = '';

            if ($lists[$k]['close'] == 1) {

                $lists[$k]['fx_name'] = '订单返现关闭';

            } else {

                if ($lists[$k]['fx_a'] == 1) {

                    $lists[$k]['fx_name'] = '返现完成';

                } elseif ($lists[$k]['fxmoney'] != 0) {

                    $lists[$k]['fx_name'] = '返现中';

                }

            }



            $lists[$k]['shopName'] = '';

            $lists[$k]['stateName'] = $stateName;

            $lists[$k]['ggmoney'] = xiaoshu($v['ggmoney']);

            $lists[$k]['money_n'] = xiaoshu($money);

            $lists[$k]['shopName'] = db("member")->where("id", $v['cid'])->value('shopname');

            $lists[$k]['nickname'] = db("member")->where("openid",$v['openid'])->value("nickname");

            $lists[$k]['intime'] = date("Y-m-d H:i:s",$v['intime']);

        }



        $data = array();

        $data['lists'] = $lists;

        $data['num'] = $num;

        $data['money'] = xiaoshu($sumMoney);



        return json_encode($data);

    }



    /**

     * 分销二维码

     */

    public function fxewm()
    {
        $openid = input("openid");

        $member = db("member")->where("openid",$openid)->find();

		//echo Env::get('root_path');die;

        if(!$member['fxewm']) {
            require_once "../vendor/phpqrcode/phpqrcode.php";

            $value = "http://www.jiajiazxgg.com/index.html#/?user_id=" . $member['id'];

            \QRcode::png($value, "../public/ewm/memberUserId$member[id].png", "L", 6, 2);

            $member['fxewm'] = $fxewm = "/ewm/memberUserId$member[id].png";

            db("member")->where("openid",$openid)->setField("fxewm",$fxewm);
        }

        return $member['fxewm'];
    }

    public function agree()
    {
    	$content = db("content_agree")->where("id",input("id"))->value("content");

    	return $content;
    }

    public function fenxiao_lists()
    {
        $id = input("id");

        $lists = db("member")->where("fx_top_a",$id)->select();

        return json_encode($lists);
    }

    // 游戏代理商 start
    /**
     * 游戏代理商申请
     */
    public function agent_game_add()
    {
        $openid = input("openid");
        $name = input("name");
        $phone = input("phone");
        $address = input("address");

        $data = db("member")->field("agent_game_sfz_a,agent_game_sfz_b")->where("openid",$openid)->find();

        $agent_game_sfz_a = $data['agent_game_sfz_a'];
        $file = request()->file("agent_game_sfz_a");
        if ($file) {
            $infos = $file->move('./uploads');
            if ($infos) {
                $url = $infos->getFilename();
                $agent_game_sfz_a = "/uploads/" . date("Ymd", time()) . '/' . $url;
                $image = \think\Image::open('.'.$agent_game_sfz_a);
                $image->thumb(450, 450)->save('.'.$agent_game_sfz_a);
            }
        }

        $agent_game_sfz_b = $data['agent_game_sfz_b'];
        $file = request()->file('agent_game_sfz_b');
        if ($file) {
            $infos = $file->move('./uploads');
            if ($infos) {
                $url = $infos->getFilename();
                $agent_game_sfz_b = "/uploads/" . date("Ymd", time()) . '/' . $url;
                $image = \think\Image::open('.'.$agent_game_sfz_b);
                $image->thumb(450, 450)->save('.'.$agent_game_sfz_b);
            }
        }

        $info = array();
        $info['agent_game'] = 1;
        $info['agent_game_name'] = $name;
        $info['agent_game_phone'] = $phone;
        $info['agent_game_address'] = $address;
        $info['agent_game_sfz_a'] = $agent_game_sfz_a;
        $info['agent_game_sfz_b'] = $agent_game_sfz_b;
        db("member")->where("openid",$openid)->update($info);

        return "succ";
    }


    public function agree_game()
    {
        $content = db("content_agree")->where("id",input("id"))->value("content");

        return $content;
    }

    // 游戏代理商 end

    // 保险开始
    /**
     * 保险首页
     */
    public function insure ()
    {
        $openid = input("openid");

        $lists = db("position")->field("`cid`")->where("state",1)->order("id desc")->limit(10)->select();
        $arr = array();$i = 0;
        foreach ($lists as $k => $v) {
            $contentData = getData("content", ['id' => $v["cid"]]);
            $contentData['money'] = xiaoshu($contentData['money']);

            if($contentData['order_type'] == 0){
                $arr[$i] = $contentData;
                $i++;
            }
        }

        $bannerList = db("fragment")->where(['catid'=>-1,'state'=>1])->order("id desc")->select();

        $insureLists = '';
        $money = 0;
        $duetime = '';
        if($openid){
            $data = [];
            $data['openid'] = $openid;
            $id = db("insure")->where($data)->value("id");
            if($id == 0){
                $data['insure_health_id'] = 0;
                $data['intime'] = date("Y-m-d H:i:s");
                $id = db("insure")->insertGetId($data);
            }

            $count = db("insure_lists")->where("insure_id",$id)->count();
            if($count == 0){
                $data = [];
                $data['insure_id'] = $id;
                $data['name'] = '';
                $data['phone'] = '';
                $data['sfz_a'] = '';
                $data['sfz_b'] = '';
                $data['status'] = '自己';
                db("insure_lists")->insert($data);
            }

            $insureLists = db("insure_lists")->field("`id`,`status`")->where("insure_id",$id)->order("id asc")->select();
            $insure_health_info = db("insure_health")->field('`money`,`duetime`')->where(['openid'=>$openid,'duetime'=>['>=',date("Y-m-d H:i:s")]])->find();
            $money = $insure_health_info['money'] ? $insure_health_info['money'] : 0;
            $duetime = $insure_health_info['duetime'] ? $insure_health_info['duetime'] : '';
        }

        $data = array();
        $data['list'] = $arr;
        $data['money'] = xiaoshu($money*2);
        $data['duetime'] = $duetime;
        $data['bannerList'] = $bannerList;
        $data['insureLists'] = $insureLists;

        return json_encode($data);
    }

    /**
     * 保险投保（添加家人身份）
     */
    public function insure_add()
    {
        $data = input();

        $insure_id = db("insure")->where("openid",$data['openid'])->value("id");

        $sfza = '';
        $file = request()->file("sfza");
        if ($file) {
            $infos = $file->move('./uploads');
            if ($infos) {
                $url = $infos->getFilename();
                $sfza = "/uploads/" . date("Ymd", time()) . '/' . $url;
                $image = \think\Image::open('.'.$sfza);
                $image->thumb(450, 450)->save('.'.$sfza);
            }
        }

        $sfzb = '';
        $file = request()->file('sfzb');
        if ($file) {
            $infos = $file->move('./uploads');
            if ($infos) {
                $url = $infos->getFilename();
                $sfzb = "/uploads/" . date("Ymd", time()) . '/' . $url;
                $image = \think\Image::open('.'.$sfzb);
                $image->thumb(450, 450)->save('.'.$sfzb);
            }
        }

        $data['sfz_a'] = $sfza;
        $data['sfz_b'] = $sfzb;
        $data['insure_id'] = $insure_id;
        $data['intime'] = date("Y-m-s H:i:s");
        unset($data['openid']);

        db("insure_lists")->insert($data);

        return "succ";
    }
    // 保险结束
	
	
	
	///商品列表
	public function goodss_list(){
		
		 $openid = input("openid");
		 
		 $order_lists = db("order_collage_item")->Distinct(true)->field('goodsid')->where(["openid"=>$openid,"collage_state"=>["in",'1,2,3'],"goodsid"=>[">",0]])->order("pay_time desc")->select();
		 $aaa=[];
		 foreach($order_lists as $k=>$v){
			 		$content = db("content")->where(['id'=>$v['goodsid']])->select();
						array_push($aaa,$content);
		 }
		 
		 $order_listss = db("order")->Distinct(true)->field('goodsid')->where(["openid"=>$openid,"collage"=>["in",'2,3'],"goodsid"=>[">",0]])->order("paytime desc")->select();
		 $bbb=[];
		 foreach($order_listss as $k=>$v){
			 		$contenst = db("content")->where(['id'=>$v['goodsid']])->select();
						array_push($bbb,$contenst);
		 }
		 
		 
		 		$lists = array_filter(array_merge($aaa,$bbb));

		 
	//	$lists = array_filter($aaa);
		//$lists = array_flip($aaa);

		$data = array();
        $data['list'] = $lists;
        return json_encode($data);
	}
	
	public function goods_order_list(){
		        $data = input();
				        $openid = input("openid");
        $state = input("state") ? input("state") : 1;
		//$goodsid=$data['goodsid'];
        
        $member = db("member")->where("openid",$openid)->find();
        $arr_a = $member["collage_count"] ? string2array($member["collage_count"]) : [];
        $arr_b = $member["collage_success"] ? string2array($member["collage_success"]) : [];

        if ($state == 1) return json_encode([]);

		        if ($state == 3) return json_encode([]);

        $wheress = '`openid` = "'.$openid.'"';
			if($state == 2){
				 $wheress .= ' and  `collage_state` < 4';
			} elseif($state == 4){
				 $wheress .= ' and `collage_state` >= 4';
				//return json_encode([]);

			}
			//$wheress=
        $order_lists = db("order_collage_item")->field("id,orderNo,title,gg_money as ggmoney,number,collage_tips,collage_state,ctime,collage_count,collage_error,goodsid,pid")->where($wheress)->where(["openid"=>$openid,"collage_state"=>[">",0],"goodsid"=>$data['goodsid']])->order("pay_time desc")->select();
        foreach ($order_lists as $i => $r) {
            $order_lists[$i]["a"] = 1;
            $order_lists[$i]["status"] = 4;
			$order_lists[$i]['intime']=$r['ctime'];
			//$order_lists[$i]['title']='（第'.$r['collage_count']+1.'单）'.$r['title'];
			
			if($r['collage_error'] >0){
				$order_lists[$i]['title']=$r['title'];
			}else{
				$order_lists[$i]['title']='（第'.($r['collage_count'] + 1) .'单）'.$r['title'];
			}
 
      /*       switch ($r["collage_state"]) {
                case 1:$stateName = "拼团中";break;
                case 2:$stateName = "拼团失败，退款在返现资料中领取";break;
                case 3:$stateName = "拼团成功";break;
                default:$stateName = "商家退款";break;
            } */


			switch ($r["collage_state"]) {
                case 1:$stateName = "拼团中";break;
                case 3:$stateName = "拼团失败";break;
                case 2:$stateName = "拼团成功";break;
                default:$stateName = "商家退款";break;
            }
			
		 	if($r['collage_error'] >0 && $r["collage_state"]>1 &&$r["collage_state"]<4){
				$stateName="特殊订单";
			} 
            $order_lists[$i]["stateName"] = $stateName;

            if($r["collage_state"] == 2 && !$r["collage_tips"]) db("order_collage_item")->where("id",$r["id"])->setField("collage_tips","1");
        }

        $where = '`openid` = "'.$openid.'" and (`status` = 0 || `status` = 2 || `status` = 4)';
        if($state == 1){
            $where .= ' and `state` = 1';
        }elseif($state == 2){
            $where .= ' and `state` > 1  and `collage` < 4';
        }elseif($state == 3){
            $where .= ' and `state` = 4';
        }elseif($state == 4){
            $where .= ' and `collage` = 4';
        }elseif($state == 5){
            $where .= ' and `state` = 7';
        }elseif($state == 6){
			
		}

        $lists = db("order")->where($where)->where(["goodsid"=>$data['goodsid']])->order("`paytime` desc")->select();
        foreach ($lists as $k => $v) {
            $cid = $v['cid'];

            $lists[$k]["a"] = 0;
            $lists[$k]['thumb'] = $v['thumb'];

            if($v['ggid'] == '0') $lists[$k]['thumb'] = '/img/xxzf.jpg';

            if($v['status'] == 4) {
                switch ($v['state']) {
                    case 1:$stateName = '拼团失败';break;
                    case 2:$stateName = '拼团中';break;
                    case 3:$stateName = '拼团成功';break;
                    case 7:$stateName = $v["collage"] == 4 ? "商家退款" : '拼团失败';
                        break;
                    default:$stateName = '';break;
                }

                if(!$v["collage_tips"]) db("order")->where("id",$v["id"])->setField("collage_tips","1");
            } else {
                switch ($v['state']) {
                    case 1:$stateName = '等待买家付款';break;
                    case 2:$stateName = '等待发货中';break;
                    case 3:$stateName = '等待收货中';break;
                    case 4:$stateName = '等待买家评价';break;
                    case 5:$stateName = '交易成功';break;
                    case 7:$stateName = '交易成功';break;
                    default:$stateName = '';break;
                }
            }



			 $lists[$k]['intime']=date("Y-m-d H:i:s",$v['intime']);


			$lists[$k]['title']='（第'.($v['collage_count'] + 1) .'单）'.$v['title'];

			
            if($v['state'] == 6){
                $state = getData("order_refund",['orderid'=>$v['id']],"state");
                switch ($state){
                    case 0:$stateName = '待审核';break;
                    case 1:$stateName = '审核成功';break;
                    case 2:$stateName = '审核失败';break;
                    default:break;
                }
                $lists[$k]['tkstate'] = $state;
            }


			if($v['collage_error_many']== 1){
				$stateName = '拼团成功,系统判定此订单为异常订单';
			}
			
			
            $lists[$k]['fx_name'] = '';
            if($v["status"] < 3) {
                if ($lists[$k]['close'] == 1) {
                    $lists[$k]['fx_name'] = '订单返现关闭';
                } else {
                    if ($lists[$k]['fx_a'] == 1) {
                        $lists[$k]['fx_name'] = '返现完成，共返现' . xiaoshu($lists[$k]['fxmoney']);
                    } elseif ($lists[$k]['fxmoney'] != 0) {
                        $lists[$k]['fx_name'] = '返现中，已返现 ' . xiaoshu($lists[$k]['fxmoney']);
                    } else {
                        $whereA = '`fx_a` = 0 and `close` = 0 and `state` = 7 and `status` = 0 and `cid` = ' . $cid;
                        $id = db("order")->where($whereA)->order("`paytime` asc")->value("id");

                        if ($id) {
                            $whereB = '`id` > ' . $id . ' and `id` <= ' . $v['id'] . ' and `state` = 7 and `status` = 0 and `close` = 0 and `cid` = ' . $cid;
                            $count = db("order")->where($whereB)->order("id desc")->count();

                            if ($count == 0) {
                                $lists[$k]['fx_name'] = '即将返现';
                            } else {
                                $lists[$k]['fx_name'] = $count . '订单后开始返现';
                            }
                        } else {
                            $lists[$k]['fx_name'] = '即将返现';
                        }
                    }
                }

                if($v['status'] == 2){
                    $lists[$k]['fx_name'] = '';
                }
            }


            $lists[$k]['shopName'] = '';
            $lists[$k]['stateName'] = $stateName;
            $lists[$k]['ggmoney'] = xiaoshu($v['ggmoney']);

            if($v['ggid'] == 0){
                $lists[$k]['shopName'] = db("member")->where("id",$v['cid'])->value('shopname');
            }
        }

        $lists = array_merge($order_lists,$lists);
		
	//collage =2  state=3 status=4 openid

        return json_encode($lists);

		
	}
}