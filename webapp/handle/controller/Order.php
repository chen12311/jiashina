<?php

namespace app\handle\controller;
use think\Controller;
use think\Request;
use think\Loader;
use think\db\Query;

class Order extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 订单信息
     */
    public function index()
    {
        $openid = input("openid");
        $orderNo = input("orderNo");

        $lists = db("order")->where("out_trade_no",$orderNo)->select();
        $money = 0;
        foreach($lists as $k => $v){
            $lists[$k]['ggmoney'] = xiaoshu($v['ggmoney']);
            $money = $money + $v['ggmoney'] * $v['number'];
        }

        $address = db("address")->where("openid",$openid)->find();
        $kdMoney = 0;
        if($address){
            $kdMoney = db("address_provinces")->where("name",$address['province'])->value("money");
        }

        $data = array();
        $data['lists'] = $lists;
        $data['money'] = xiaoshu($money);
        $data['address'] = $address;
        $data['kdMoney'] = $kdMoney;

        return json_encode($data);
    }

    /**
     * 选择地址
     */
    public function address()
    {
        $info = input();
        $id = $info["id"] ?? 0;
        unset($info["id"]);

        $data = db("address")->where("openid",$info["openid"])->find();
        if($data){
            db("address")->where("id",$data['id'])->update($info);
        }else{
            $info['intime'] = time();
            db("address")->insert($info);
        }

        if ($id) {
            db("order")->where("id",$id)->update(["name"=>$info["name"],"phone"=>$info["phone"],"address"=>$info["address"]]);
            return "success";
        } else {
            $money = db("address_provinces")->where("name",$info['province'])->value("money");

            return xiaoshu($money);
        }
    }

    /**
     * 提交订单-修改订单信息
     */
    public function add()
    {
        $openid = input("openid");
        $type = input("type");
        $orderNo = input("orderNo");
        $content = input("content");

        $start_time = strtotime(date("Y-m-d 09:00:00",time()));
        $end_time = strtotime(date("Y-m-d 23:00:00",time()));

        if ($start_time > time() || $end_time < time()) return "error1";

        $order_arr = db("order")->field("id,cid,collageNo")->where("orderNo", $orderNo)->find();
        $count = db("order")->where(["cid"=>$order_arr["cid"],"collageNo"=>$order_arr["collageNo"],"collage_team"=>1,"openid"=>["neq",0]])->count("id");
        if ($count) {
            db("order")->where("id",$order_arr["id"])->setField("collageNo",collageNo());
        }

        $data = db("address")->where("openid",$openid)->find();
        $money = db("address_provinces")->where("name",$data['province'])->value("money");

        $info = array();
        $info['name'] = $data['name'];
        $info['phone'] = $data['phone'];
        $info['address'] = $data['address'];
        $info['kdmoney'] = $money;
        $info['content'] = $content;
//        $info['state'] = 1;

        $lists = db("order")->where("out_trade_no",$orderNo)->select();
        foreach($lists as $k => $v) {
            if($v) {
                db("order")->where("orderNo",$v['orderNo'])->update($info);
            }
        }

        return 'succ';
    }
	
	public function zhuanmai_list(){
		$openid = input("openid");
        $data = db("order_collage_item")->field("openid,out_trade_no,collage_state,goodsid,id")->where("openid",$openid)->where("collage_state",2)->whereTime('ctime', '>=', '2022-08-08')->order('id asc')->select();
		$aaaa=[];
		$aaaasss=[];
		$a=0;
        foreach ($data as $k=>$v){
			$a++;
            $zuixin = db("order_collage_item")->where(["openid"=>$openid,"id"=>[">",$v['id']],"collage_state"=>['in','2,3']])->order('id desc')->whereTime('ctime', '>=', '2022-08-08')->where("goodsid",$v["goodsid"])->find();
			if($zuixin['collage_count']<4){
				$wewewwe=$zuixin['collage_count'];
				
				
				$aaaaaa=db("order_collage_item")->where(["openid"=>$openid,"collage_state"=>['in','2,3']])->whereTime('ctime', '>=', '2022-08-08')->where("goodsid",$v["goodsid"])->select();
				foreach(array_slice($aaaaaa,-$wewewwe) as $kk=>$vv){
						array_push($aaaasss,array($vv));
				}
					//array_push($aaaa,$aaaaa);
				
			}else{
				//echo 201212322;
				$aaaaa=db("order_collage_item")->where(["openid"=>$openid,"collage_state"=>['in','2']])->whereTime('ctime', '>=', '2022-08-08')->where("goodsid",$v["goodsid"])->select();
					array_push($aaaa,$aaaaa);
					
			}
			
			
				/* if($zuixin['collage_count']<4){
					$aaaaaa = db("order_collage_item")->where(["openid"=>$openid,"id"=>[">",$v['id']],"collage_state"=>['in','2']])->whereTime('ctime', '>=', '2022-08-08')->where("goodsid",$v["goodsid"])->select();
					$dataaa= array_slice($aaaaaa,-1);
					if($dataaa){
						array_push($aaaasss,$dataaa);
					}
					echo 1;
				}else{				
					echo 2;
					$aaaaa=db("order_collage_item")->where(["openid"=>$openid,"id"=>$v['id'],"collage_state"=>['in','2']])->whereTime('ctime', '>=', '2022-08-08')->where("goodsid",$v["goodsid"])->find();
					array_push($aaaa,$aaaaa);
				} */
        }
		//echo $a;die;
		
		    //    $datassss = db("order_collage_item")->where("openid",$openid)->where(["collage_state"=>['in','2,3']])->whereTime('ctime', '>=', '2022-08-08')->order('id asc')->select();

		//var_dump($aaaaa);die;
		$lists = array_filter(array_merge($aaaasss,$aaaa));
		$data = array();
        $data['lists'] = array(array_unique($lists, SORT_REGULAR));
      //  $data['lists'] = $datassss;
        return json_encode($data);
		
	}
	
	
	
	
		///商品列表
	public function zhuanmai_goodss_list(){
		 $openid = input("openid");
		 $order_lists = db("order_collage_item")->Distinct(true)->field('goodsid')->where(["openid"=>$openid,"collage_state"=>["in",'2'],"goodsid"=>[">",0]])->whereTime('ctime', '>=', '2022-08-09')->order("pay_time desc")->select();
		 $aaa=[];
		 foreach($order_lists as $k=>$v){
			 		$content = db("content")->where(['id'=>$v['goodsid']])->select();
						array_push($aaa,$content);
		 }
		 
		 $order_listss = db("order")->Distinct(true)->field('goodsid')->where(["openid"=>$openid,"collage"=>["in",'2'],"goodsid"=>[">",0]])->whereTime('intime', '>=', '2022-08-09')->order("paytime desc")->select();
		 $bbb=[];
		 foreach($order_listss as $k=>$v){
			$contenst = db("content")->where(['id'=>$v['goodsid']])->select();
			array_push($bbb,$contenst);
		 }

		$lists = array_filter(array_merge($aaa,$bbb));
		$data = array();
        $data['list'] = $lists;
        return json_encode($data);
	}
	public function zhuanmai_listsss(){
		$openid = input("openid");
		$goodsid = input("goodsid");

		 $zuixin = db("order_collage_item")->where(["openid"=>$openid,"collage_state"=>["in",'2,3'],"goodsid"=>$goodsid])->whereTime('ctime', '>=', '2022-08-09')->order("id desc")->find();
		 $detaaa = db("order_collage_item")->where(["openid"=>$openid,"collage_state"=>["in",'2,3'],"goodsid"=>$goodsid])->whereTime('ctime', '>=', '2022-08-09')->order("id desc")->select();
		 		 $detaaab = db("order_collage_item")->where(["openid"=>$openid,"collage_state"=>["in",'2'],"goodsid"=>$goodsid])->whereTime('ctime', '>=', '2022-08-09')->order("id desc")->select();

			if($zuixin < 4){
				$jianshao=$zuixin['collage_count']+1;
				//echo $jianshao;die;
				$lists=  array_slice($detaaa,$jianshao);
			}else{
				$lists= $detaaa;
			}

			if($zuixin['collage_state'] == 2){
				$xin = 0;
				//成功不能转卖
			}else{
				$xin=1;
				//失败可以转卖
			}
			

				$data = array();
        $data['lists'] = $lists;
				$data['listsssss'] = $detaaab;

		 $data['xin'] = $xin;
      //  $data['lists'] = $datassss;
        return json_encode($data);
		
	}
	
	public function zhuanmai(){
		$openid = input("openid");
		$orderNo = input("orderNo");

        $order = db("order_collage_item")->where(["openid"=>$openid,"orderNo"=>$orderNo])->find();
		
		if($order['zhuanmai'] == 1){
			$data['code']=1111;
			$data['msg']="转卖失败";
		}
		
		if($order){
			$mai=  db("order_collage_item")->where("orderNo",$orderNo)->update(["zhuanmai"=>1,'address'=>'裕华街道裕华金街B座603']);
		
			$money=db("content")->where(["id"=>$order['goodsid']])->value('zhuanmai_money')*100;
//			$money=100;
			//修改地址？
			
                if ($mai) {
                    $res = withdraw($orderNo, $openid, $money, Request::instance()->ip());
                    $data = xmltoarray($res);

                    if ($data['return_code'] == 'SUCCESS' && $data['result_code'] == 'SUCCESS') {
                        $info = array();
                        $info['orderNo'] = $orderNo;
                        $info['openid'] = $openid;
                        $info['money'] = $money;
                        $info['state'] = 0;
                        $info['type'] = 1;
                        $info['intime'] = time();
                        db("record_zhuanmai")->insert($info);
                    } else {
						db("order_collage_item")->where("orderNo",$orderNo)->update(["zhuanmai"=>0]);
                    }
                }
			
			//zhuanmai =1 
			$data['code']=0000;
			$data['msg']="转卖成功";
		}else{
			$data['code']=1111;
			$data['msg']="转卖失败";
		}

		return json_encode($data);

	}	
	
	public function fahuo(){
		$openid = input("openid");
		$orderNo = input("orderNo");

        $order = db("order_collage_item")->where(["openid"=>$openid,"orderNo"=>$orderNo])->find();
		
		if($order['zhuanmai'] == 1){
			$data['code']=1111;
			$data['msg']="已转卖";
		}
		
				
		if($order['zhuanmai'] == 2){
			$data['code']=1111;
			$data['msg']="已选择发货";
		}
		
		
		if($order){
			$mai=  db("order_collage_item")->where("orderNo",$orderNo)->update(["zhuanmai"=>2]);
		
//			$money=100;
			//修改地址？
			
			//zhuanmai =1 
			$data['code']=0000;
			$data['msg']="选择发货成功";
		}else{
			$data['code']=1111;
			$data['msg']="选择发货失败";
		}

		return json_encode($data);

	}
}