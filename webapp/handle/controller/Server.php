<?php

namespace app\handle\controller;
use think\Controller;
use think\Request;
use think\Loader;
use think\db\Query;
use index\Temp;

class Server extends Controller
{
    public function index ()
    {
        $arr = db("agent_game_setup")->field("scale_k,scale_l")->where("id",1)->find();

        if($arr["scale_k"] > 0 && $arr["scale_l"] > 0) {
            if($lists = db("member")->field("id, openid, agent_game_day_money, agent_game_number")->where(["agent_game" => 1, "agent_game_level"=>[">=",1], "agent_game_day_money" => ["<", $arr["scale_l"]]])->select()) {
                foreach ($lists as $v) {
                    $money = $arr["scale_k"] * $v["agent_game_number"];
                    $result = $arr["scale_l"] * $v["agent_game_number"] - $v["agent_game_day_money"];
                    if ($result > 0 && $result < $money) $money = $result;

                    $info = array();
                    $info['openid'] = $v["openid"];
                    $info['money'] = $money * 100;
                    $info['state'] = 11;
                    $info['type'] = '+';
                    $info['msg'] = "游戏代理商每天返现";
                    $info['intime'] = time();
                    db("record_pay")->insert($info);

//                    db("member")->where("openid", $v["openid"])->setInc("money", $money * 100);
                    db("member")->where("openid", $v["openid"])->setInc("agent_game_money", $money * 100);
                    db("member")->where("openid", $v["openid"])->setInc("agent_game_day_money", $money);
                }
            }
        }
    }

    public function refund_index_a ()
    {
        $weChat = get_wechat();

        $where = ["id"=>[">",4445], "state"=>2, "status"=>4];
        $lists = db("order")->field("collageNo")->where($where)->group("collageNo")->order("id asc")->select();

        $str = implode(",",array_column($lists,"collageNo"));
        $lists = db("order")->field("id, openid, out_trade_no, ggmoney, number, paytime, collageNo, transaction_id")->where(["state"=>2, "collageNo"=>["in",$str]])->select();

        foreach ($lists as $v) {
            $collage_money = $v["ggmoney"] * $v["number"];
            $out_refund_no = orderNo();

            $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
            $data = [
                'appid' => $weChat['appid'],
                'mch_id' => '1550831061',
                'nonce_str' => encrypt(32),
                'transaction_id' => $v["transaction_id"],
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
            fwrite($myFile, "transaction_id:{$v['transaction_id']}\n\r" . $res);
            fwrite($myFile, "\n\r\n\r");
            fclose($myFile);

            db("order")->where("id",$v["id"])->setField("out_refund_no",$out_refund_no);
            if ($res_arr["return_code"] == "SUCCESS" && $res_arr["result_code"] == "SUCCESS") {
                db("order")->where("id",$v["id"])->update(["state"=>7, 'collage'=>4, "refund_id"=>$res_arr["refund_id"], "out_refund_no"=>$out_refund_no, "refund_err_code_des"=>""]);
            }

            if(isset($res_arr["err_code_des"])) db("order")->where("id",$v["id"])->setField("refund_err_code_des",$res_arr["err_code_des"]);

            usleep(mt_rand(500000,1500000));
        }

//        db("order")->where(["id"=>[">",4445],"status"=>4,"state"=>1])->delete();

        return "success";
    }

    // public function refund_index ()
    // {
    //     $weChat = get_wechat();

    //     $starttime = date("Y-m-d 00:00:00");
    //     $endtime = date("Y-m-d 23:59:59");

    //     $lists = db("order_collage_item")->field("id,gg_money,number,transaction_id")->where(["collage_state"=>1,"pay_time"=>["between","$starttime,$endtime"]])->order("id desc")->select();
    //     foreach ($lists as $v) {
    //         $collage_money = $v["gg_money"] * $v["number"];
    //         $out_refund_no = orderNo();

    //         $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
    //         $data = [
    //             'appid' => $weChat['appid'],
    //             'mch_id' => '1550831061',
    //             'nonce_str' => encrypt(32),
    //             'transaction_id' => $v["transaction_id"],
    //             'out_refund_no' => $out_refund_no,
    //             'total_fee' => $collage_money*100,
    //             'refund_fee' => $collage_money*100,
    //             'refund_desc' => '商家退款'
    //         ];

    //         $data = array_filter($data);
    //         ksort($data);

    //         $str = '';
    //         foreach ($data as $k => $i) {
    //             $str .= $k . '=' . $i . '&';
    //         }
    //         $str .= 'key=8s5r5cfqd453e775dq95795cy7b09x9d';

    //         $data['sign'] = md5($str);
    //         $xml = arraytoxml($data);
    //         $res = curl($xml, $url);
    //         $res_arr = xmltoarray($res);

    //         $dirname = "./logs/refund/" . date("Ymd");
    //         if (!file_exists($dirname)) mkdir($dirname, 0755, true);
    //         $myFile = fopen($dirname . "/logs-" . date("H") . " o'clock.txt", "a");
    //         fwrite($myFile, "transaction_id:{$v['transaction_id']}\n\r" . $res);
    //         fwrite($myFile, "\n\r\n\r");
    //         fclose($myFile);

    //         if ($res_arr["return_code"] == "SUCCESS" && $res_arr["result_code"] == "SUCCESS") {
    //             db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>4, "refund_id"=>$res_arr["refund_id"], "refund_no"=>$out_refund_no]);
    //         } else {
    //             if (isset($res_arr["err_code_des"])) {
    //                 db("order_collage_item")->where("id",$v["id"])->update(["refund_err_code_des"=>$res_arr["err_code_des"], "refund_no"=>$out_refund_no]);
    //                 return $res_arr["err_code_des"];
    //             }
    //         }

    //         usleep(mt_rand(500000,1500000));
    //     }

    //     db("order_collage")->where(["collage_state"=>0,"ctime"=>["between","$starttime,$endtime"]])->setField("collage_state",2);

    //     return "success";
    // }
    
        public function refund_index ()
    {
        $weChat = get_wechat();

        $starttime = date("Y-m-d 00:00:00");
        $endtime = date("Y-m-d 23:59:59");

        $lists = db("order_collage_item")->field("id,gg_money,number,transaction_id,openid,orderNo,title")->where(["collage_state"=>1,"pay_time"=>["between","$starttime,$endtime"]])->order("id desc")->select();
        foreach ($lists as $v) {
            
            $isweixin= substr($v['transaction_id'] , 0 , 6);
            		
        	if($isweixin =='888800'){
        	    
        	    $collage_money = $v["gg_money"] * $v["number"];
    			$out_refund_no = orderNo();
    			$refund = db("member")->where("openid",$v['openid'])->setInc("collage_money", $collage_money*100);
    			$refund_id ='666680'.time();
    			if($refund){
    				 db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>4, "refund_id"=>$refund_id, "refund_no"=>$out_refund_no]);
    				 
    				$info_record = [];
                    $info_record['openid'] = $v["openid"];
                    $info_record['orderNo'] = $v["orderNo"];
                    $info_record['money'] = $collage_money;
                    $info_record['state'] = 6;
                    $info_record['state_many'] = 0;
                    $info_record['type'] = '+';
                    $info_record['msg'] = "拼团已满订单取消退回鼓励金";
                    db("record_collage")->insert($info_record);
    				//return "success";
    			}else{
    				  db("order_collage_item")->where("id",$v["id"])->update(["refund_err_code_des"=>'拼团已满订单取消退回鼓励金失败', "refund_no"=>$out_refund_no]);
    				return '拼团已满订单取消退回鼓励金失败';
    					
    			}
			
        	    
        	}else{
        	    
        	    $collage_money = $v["gg_money"] * $v["number"];
                $out_refund_no = orderNo();

                $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
                $data = [
                    'appid' => $weChat['appid'],
                    'mch_id' => '1550831061',
                    'nonce_str' => encrypt(32),
                    'transaction_id' => $v["transaction_id"],
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
                fwrite($myFile, "transaction_id:{$v['transaction_id']}\n\r" . $res);
                fwrite($myFile, "\n\r\n\r");
                fclose($myFile);
    
                if ($res_arr["return_code"] == "SUCCESS" && $res_arr["result_code"] == "SUCCESS") {
                    db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>4, "refund_id"=>$res_arr["refund_id"], "refund_no"=>$out_refund_no]);
                } else {
                    if (isset($res_arr["err_code_des"])) {
                        db("order_collage_item")->where("id",$v["id"])->update(["refund_err_code_des"=>$res_arr["err_code_des"], "refund_no"=>$out_refund_no]);
                        return $res_arr["err_code_des"];
                    }
                }
                
        	}
			
									$member = db("member")->where("openid",$v["openid"])->find();
						$modelsasasa = controller('Wxnofiy');
						$aaaa =$modelsasasa->tuan_tk($v["openid"],$v["orderNo"],$member['nickname'],$v['title'],$v["gg_money"]);
						
						
            usleep(mt_rand(500000,1500000));
        }

        db("order_collage")->where(["collage_state"=>0,"ctime"=>["between","$starttime,$endtime"]])->setField("collage_state",2);

        return "success";
    }
	
	
	
	public function kou(){
		
		$lists = db("collage_money_mony_news")->select();
		$aaaa=0;
		  foreach ($lists as $v) {
			 // $zong= $v['goods_a']+$v['goods_b']+$v['goods_c']+$v['goods_d']+$v['goods_e']+$v['goods_f']+$v['goods_g']+$v['goods_h'];
		   //  db("collage_money_mony_news")->where("openid",$v["openid"])->update(["yikou"=>$v['zong']*0.5]); 
			// die;
			
			
			 
		//	 db("member")->where("openid",$v['openid'])->setInc("collage_money", ($v['yikou'])*100);
			// db("collage_money_mony_news")->where("openid",$v["openid"])->update(["yikou"=>$v['yikou']-$v['yikou']]); 
			
			/*     db("member")->where("openid",$v['openid'])->setDec("collage_money", ($v['zong']*0.5)*100);


    				$info_record = [];
                    $info_record['openid'] = $v["openid"];
                    $info_record['orderNo'] = orderNo();
                    $info_record['money'] = $v['zong']*0.5;
                    $info_record['state'] = 8;
                    $info_record['state_many'] = 0;
                    $info_record['type'] = '-';
                    $info_record['msg'] = "扣除异常鼓励金";
                    db("record_collage")->insert($info_record);
					
					
					$info_recorda = [];
                    $info_recorda['openid'] = $v["openid"];
                    $info_recorda['orderNo'] = orderNo();
                    $info_recorda['money'] = $v['zong']*0.5;
                    $info_recorda['state'] = 1;
                    $info_recorda['state_many'] = 0;
                    $info_recorda['type'] = '-';
                    $info_recorda['msg'] = "扣除异常鼓励金";
                    db("record_collage_tui")->insert($info_recorda);
					
			 
			usleep(mt_rand(500000,1500000)); */
		  }
	}
	
	
	public function sdsdsd(){
		
		$info["goods_id"]=132;
		$info["openid"]='oXgpTt4blHx7gn6psoxXtzoBFA94';

		
	  $count = db("order_collage_item")->alias("a")
            ->join("yado_order_collage b", "a.pid = b.id")
            ->where(["b.collage_state" => 1, "b.goods_id" => $info["goods_id"], "a.openid" => $info["openid"], "a.collage_state" => 2])
			->whereTime('a.ctime','-1 minute')
            ->find();
			
			var_dump($count);die;
					$timeaa = "2022-07-16 01:56:03";

					$next =  strtotime ('+1 minute', strtotime ($count['ctime']));
					
					if(strtotime($count['ctime']) < $next ){
						
						echo '当前时间'.$count['ctime'].'小于'.date("Y-m-d H:i:s",strtotime ('+1 minute', strtotime ($count['ctime'])));
					}else{
						echo 222222;
					}
//echo $next;die;
		//	echo 	$count['ctime'];
			die;
		//->whereTime('start_time', '<=', time());
	
			
        if ($count) {
            return "有进行中的拼团";
        }
	
	
		echo 11121;
	}
	
	
	//用户升级团长合伙人
	public function level(){
		$lists = db("member")->select();
		foreach($lists as $v){
		//	
			switch ($v['level']){
				case 0:
					$sum = db("member")->where(['fx_top_a'=>$v['id']])->count();
					if($sum >= 31){
						db("member")->where("id",$v['id'])->update(['level' =>1]);
					}
					break;
				case 1:
					$sum = db("member")->where(['fx_top_a'=>$v['id'],'level'=>1])->count();
					if($sum >= 3){
						db("member")->where("id",$v['id'])->update(['level' =>2]);
					}					
					break;
				default: 
			}
		}
		echo 'ok';
	}
	
	
	
	//合伙人分佣
	public function hhr(){
		$lists = db("member")->where(['level'=>2])->select();
				$hehuorenmoney=173;

		foreach($lists as $v){
			$aaa = db("record_collage")->where(['openid'=>$v['openid'],'state'=>10])->whereTime('ctime', 'd')->find();
			if(!$aaa){
				$info_record = [];
				$info_record['openid'] = $v['openid'];
				$info_record['orderNo'] = time();
				$info_record['money'] = $hehuorenmoney;
				$info_record['state'] = 10;
				$info_record['type'] = '+';
				$info_record['msg'] = "合伙人收益";
				db("record_collage")->insert($info_record);
				db("member")->where("openid",$v['openid'])->setInc("collage_money", $hehuorenmoney * 100);
				db("member")->where("openid",$v['openid'])->setInc("collage_money_a", $hehuorenmoney);
					echo 'ok';
			}else{
				echo 'fail,今日已返';
			}
		}
		
				
		//die;
		///
		//	oXgpTtxP5guvFQkIC_VG-nR_naME
			
/* 		
		$aaa = db("record_collage")->where(['openid'=>'oXgpTtzZO4ucAw2-fLaOIMhR_r8s','state'=>10])->whereTime('ctime', 'd')->find();
		
		$bbb = db("record_collage")->where(['openid'=>'oXgpTt_1zk6LdjNJHICcCc49tlQA','state'=>10])->whereTime('ctime', 'd')->find();
		$ccc = db("record_collage")->where(['openid'=>'oXgpTtxP5guvFQkIC_VG-nR_naME','state'=>10])->whereTime('ctime', 'd')->find();


		if(!$aaa){
			$info_record = [];
            $info_record['openid'] = 'oXgpTtzZO4ucAw2-fLaOIMhR_r8s';
            $info_record['orderNo'] = time();
            $info_record['money'] = $hehuorenmoney;
            $info_record['state'] = 10;
            $info_record['type'] = '+';
            $info_record['msg'] = "合伙人收益";
            db("record_collage")->insert($info_record);
			
            db("member")->where("openid",'oXgpTtzZO4ucAw2-fLaOIMhR_r8s')->setInc("collage_money", $hehuorenmoney * 100);
            db("member")->where("openid",'oXgpTtzZO4ucAw2-fLaOIMhR_r8s')->setInc("collage_money_a", $hehuorenmoney);
		}else{
			echo 'fail';
		}
		


		if(!$bbb){
			$info_records = [];
            $info_records['openid'] = 'oXgpTt_1zk6LdjNJHICcCc49tlQA';
            $info_records['orderNo'] =  time();
            $info_records['money'] = $hehuorenmoney;
            $info_records['state'] = 10;
            $info_records['type'] = '+';
            $info_records['msg'] = "合伙人收益";
            db("record_collage")->insert($info_records);
			
            db("member")->where("openid",'oXgpTt_1zk6LdjNJHICcCc49tlQA')->setInc("collage_money", $hehuorenmoney * 100);
            db("member")->where("openid",'oXgpTt_1zk6LdjNJHICcCc49tlQA')->setInc("collage_money_a", $hehuorenmoney);
		}else{
			echo 'fail';
		}
		
		
		if(!$ccc){
			$info_records = [];
            $info_records['openid'] = 'oXgpTtxP5guvFQkIC_VG-nR_naME';
            $info_records['orderNo'] =  time();
            $info_records['money'] = $hehuorenmoney;
            $info_records['state'] = 10;
            $info_records['type'] = '+';
            $info_records['msg'] = "合伙人收益";
            db("record_collage")->insert($info_records);
			
            db("member")->where("openid",'oXgpTtxP5guvFQkIC_VG-nR_naME')->setInc("collage_money", $hehuorenmoney * 100);
            db("member")->where("openid",'oXgpTtxP5guvFQkIC_VG-nR_naME')->setInc("collage_money_a", $hehuorenmoney);
		}else{
			echo 'fail';
		} */
		
		
		
		/* 	$info_record = [];
            $info_record['openid'] = 'oXgpTtzZO4ucAw2-fLaOIMhR_r8s';
            $info_record['orderNo'] = time();
            $info_record['money'] = $hehuorenmoney;
            $info_record['state'] = 10;
            $info_record['type'] = '+';
            $info_record['msg'] = "合伙人收益";
            db("record_collage")->insert($info_record);
			
            db("member")->where("openid",'oXgpTtzZO4ucAw2-fLaOIMhR_r8s')->setInc("collage_money", $hehuorenmoney * 100);
            db("member")->where("openid",'oXgpTtzZO4ucAw2-fLaOIMhR_r8s')->setInc("collage_money_a", $hehuorenmoney);
		
		
		
			$info_records = [];
            $info_records['openid'] = 'oXgpTt_1zk6LdjNJHICcCc49tlQA';
            $info_records['orderNo'] =  time();
            $info_records['money'] = $hehuorenmoney;
            $info_records['state'] = 10;
            $info_records['type'] = '+';
            $info_records['msg'] = "合伙人收益";
            db("record_collage")->insert($info_records);
			
            db("member")->where("openid",'oXgpTt_1zk6LdjNJHICcCc49tlQA')->setInc("collage_money", $hehuorenmoney * 100);
            db("member")->where("openid",'oXgpTt_1zk6LdjNJHICcCc49tlQA')->setInc("collage_money_a", $hehuorenmoney);
		
					  */

	}
		
		//计划任务 1小时退款
	public function hour_refund_index()
    {
        $weChat = get_wechat();
        $lists = db("order_collage")->where(["collage_state"=>0,"open_time"=>"0000-00-00 00:00:00"])->whereTime('ctime', 'd')->where("'".date("Y-m-d H:i:s",time())."' > ".'DATE_ADD(`ctime`,INTERVAL 180 minute) ')->order("id desc")->select(); 
		foreach ($lists as $v) {
			//设置订单状态为失败
			$sszt = db("order_collage")->where(["id"=>$v['id']])->setField("collage_state",2);
			//退款
			$collage_item = db("order_collage_item")->field("id,gg_money,number,transaction_id,openid,orderNo,title")->where(["pid"=>$v['id']])->order("id desc")->select();
			
			  foreach ($collage_item as $vv) {
            
				$isweixin= substr($vv['transaction_id'] , 0 , 6);
						
				if($isweixin =='888800'){
					$collage_money = $vv["gg_money"] * $vv["number"];
					$out_refund_no = orderNo();
					$refund = db("member")->where("openid",$vv['openid'])->setInc("collage_money", $collage_money*100);
					$refund_id ='666680'.time();
					if($refund){
						 db("order_collage_item")->where("id",$vv["id"])->update(["collage_state"=>4, "refund_id"=>$refund_id, "refund_no"=>$out_refund_no]);
						$info_record = [];
						$info_record['openid'] = $vv["openid"];
						$info_record['orderNo'] = $vv["orderNo"];
						$info_record['money'] = $collage_money;
						$info_record['state'] = 6;
						$info_record['state_many'] = 0;
						$info_record['type'] = '+';
						$info_record['msg'] = "拼团已满订单取消退回鼓励金";
						db("record_collage")->insert($info_record);
						//return "success";
					}else{
						  db("order_collage_item")->where("id",$vv["id"])->update(["refund_err_code_des"=>'拼团已满订单取消退回鼓励金失败', "refund_no"=>$out_refund_no]);
						return '拼团已满订单取消退回鼓励金失败';
					}
				}else{
					
					$collage_money = $vv["gg_money"] * $vv["number"];
					$out_refund_no = orderNo();

					$url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
					$data = [
						'appid' => $weChat['appid'],
						'mch_id' => '1550831061',
						'nonce_str' => encrypt(32),
						'transaction_id' => $vv["transaction_id"],
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
					fwrite($myFile, "transaction_id:{$vv['transaction_id']}\n\r" . $res);
					fwrite($myFile, "\n\r\n\r");
					fclose($myFile);
		
					if ($res_arr["return_code"] == "SUCCESS" && $res_arr["result_code"] == "SUCCESS") {
						db("order_collage_item")->where("id",$vv["id"])->update(["collage_state"=>4, "refund_id"=>$res_arr["refund_id"], "refund_no"=>$out_refund_no]);
					} else {
						if (isset($res_arr["err_code_des"])) {
							db("order_collage_item")->where("id",$vv["id"])->update(["refund_err_code_des"=>$res_arr["err_code_des"], "refund_no"=>$out_refund_no]);
							return $res_arr["err_code_des"];
						}
					}
					
				}
						$member = db("member")->where("openid",$vv["openid"])->find();
						$modelsasasa = controller('Wxnofiy');
						$aaaa =$modelsasasa->tuan_tk($vv["openid"],$vv["orderNo"],$member['nickname'],$vv['title'],$vv["gg_money"]);
		
		
				usleep(mt_rand(500000,1500000));
			}
		}
		        return "success"; 

	
    }
			
		
	public function qzjjj(){
						//		db("member")->where('1=1')->update(['collage_success' =>null]);

	}
	
	
	public function ttttt(){
		
		
		     //       $lists = db("order_collage")->field("id,collage_state,five,ctime")->where(["goods_id" => 138, "collage_state" => 0])->order('five DESC,collage_number DESC,ctime ASC')->select();
					
					
			//		var_dump($lists);

	}
	
	
	public function xftx(){
		
		                //    db("member")->where('1=1')->update(["tixian"=>1]);
		
	}
	
	public function spidxg(){
		// 	$lists = db("content")->where(['collage'=>1])->select();

		//foreach($lists as $v){
			//db("order")->where("title",$v['title'])->update(['goodsid' =>$v['id']]);
	//		db("order")->where("title",'【正心莲】非物质文化遗产')->update(['goodsid' =>136]);
			
		//} 
		
	}
	
	public function asdddd(){
				$lists = db("member")->where(['level'=>2])->select();
				var_dump($lists);

	}
	
	public function asssss(){
								$tuanzhangshouyi= db("record_collage")->where(["openid"=>'oXgpTtxP5guvFQkIC_VG-nR_naME',"state"=>9])->select();

		var_dump($tuanzhangshouyi);
	}
	
	
	public function asdsasda(){
		$openid='oXgpTt8DSS6HCJe7FOAgqIlIIrPc';
		$v["goodsid"]=148;
		            $zuixin = db("order_collage_item")->where(["openid"=>$openid,"collage_state"=>['in','2,3']])->order('id desc')->whereTime('ctime', '>=', '2022-08-08')->where("goodsid",$v["goodsid"])->fetchSql()->find();

							$aaaaa=db("order_collage_item")->where(["openid"=>'oXgpTt8DSS6HCJe7FOAgqIlIIrPc',"collage_state"=>['in','2']])->whereTime('ctime', '>=', '2022-08-08')->where("goodsid",148)->fetchSql()->select();
							echo $zuixin;

		
	}
	
	
	public function iiiiiii(){
		
		
		$modelsasasa = controller('Wxnofiy');
		$aaaa =$modelsasasa->tuan_success('oXgpTt4blHx7gn6psoxXtzoBFA94','2207122356139971','【王浩】','测试商品勿拍','0.10');
		
		
		
		
		//$modelss = controller('handle/Wxnofiy', 'controller');

		//$modelss = controller('Wxnofiy');
		

	}
	
	
	
	
}