<?php

namespace app\handle\controller;
use think\Controller;
use think\Request;
use think\Loader;
use think\db\Query;
use index\Temp;

class Collage extends Controller
{
    private $db,$o_t_db; 
    private $m_db;
    public function __construct()
    {
        parent::__construct();

        $this->db = db("order_collage");
        $this->o_t_db = db("order_collage_item");
        $this->m_db = db("member");
    }

    public function index (int $goods_id = 0, string $openid = "")
    {
        $lists = $this->db->field("id,openid,collageNo,collage_number")->where(["goods_id"=>$goods_id,"collage_state"=>0])->order("id asc")->select();

        $state = 0;
        foreach ($lists as $k => $v) {
            $arr = $this->o_t_db->field("openid")->where(["pid"=>$v["id"],"collage_state"=>1])->order("pay_time asc")->select();
            if(!count($arr) || count($arr) >= 4) {
                unset($lists[$k]);
                continue;
            } else {
                $v["openid"] = $arr[0]["openid"];
            }

            $seconds = strtotime(date("Y-m-d 23:59:59",time())) - time();
            if ($seconds <= 0) {
                unset($lists[$k]);
                continue;
            }

            $lists[$k]["time"] = "";
            $lists[$k]["seconds"] = $seconds;
            $lists[$k]["count"] = 3 - min(count($arr), 3);
			
			
			
			// $sadsadsadsa = $this->o_t_db->where(["pid"=>$v["id"],"collage_state"=>1])->order("pay_time asc")->select();

			//	var_dump($arr);die;
            $lists[$k]["member"] = $this->m_db->field("usertx,nickname")->where("openid", $v["openid"])->find();
			
			
			$wftssss= $this->o_t_db->where(["pid"=>$v["id"],"collage_state"=>1])->field("openid")->order("pay_time asc")->select();
				$sdaadas =[];
			
				foreach($wftssss as $vv){
					$asdsadsddd =  $this->m_db->field("usertx,nickname")->where("openid", $vv["openid"])->find();
					array_push($sdaadas,$asdsadsddd);
					
					
				}

            $lists[$k]["members"] =  $sdaadas;


            if (!$state && $openid == $v["openid"]) $state = 1;

            if (!$state) {
                $count = $this->o_t_db->where(["pid"=>$v["id"],"openid"=>$openid,"collage_state"=>1])->count("id");
                if ($count) $state = 1;
            }
        }

        $data = [];
        $data["lists"] = array_values($lists);
        $data["state"] = $state;

        return json_encode($data);
    }

    public function get_address (int $goods_id = 0,string $openid = "")
    {
        $address = db("address")->where("openid",$openid)->find();

        $lists = db("content")->where("id",$goods_id)->select();
        foreach ($lists as $k => $v) {
            $lists[$k]["money"] = xiaoshu($v["money"]);
        }

        $data = [];
        $data['lists'] = $lists;
        $data["address"] = $address;
        $data['kdMoney'] = 0;

        return json_encode($data);
    }

    public function collage_add ()
    {
        $info = input();
		die;
        $count = $this->o_t_db->alias("a")
                    ->join("yado_order_collage b","a.pid = b.id")
                    ->where(["b.collage_state"=>0,"b.goods_id"=>$info["goods_id"],"a.openid"=>$info["openid"],"a.collage_state"=>1])
                    ->count("a.id");
        if ($count) return "有进行中的拼团";
		die;
        $start_time = strtotime(date("Y-m-d 09:00:00",time()));
        $end_time = strtotime(date("Y-m-d 23:00:00",time()));

        if ($start_time > time() || $end_time < time()) return "error1";
        
        $member = db("member")->field("collage_count, collage_success, collage_error")->where("openid", $info["openid"])->find();
        $arr_a = $member["collage_count"] ? string2array($member["collage_count"]) : [];
        $arr_b = $member["collage_success"] ? string2array($member["collage_success"]) : [];
        $arr_c = $member["collage_error"] ? string2array($member["collage_error"]) : [];

        $a = $arr_a[$info["goods_id"]] ?? 0;
        $b = $arr_b[$info["goods_id"]] ?? 0;
        $c = $arr_c[$info["goods_id"]] ?? 0; 

//      判断是否中过奖 1有，不能在中奖 0没有，可以中奖  $c后台设置必须失败
        $win = $a ? ($a == 4 ? 1 : $b) : 0;
        $win = $c ? 1 : $win;
		
// 		$win =1;
		
		$id = 0;
        $lists = db("order_collage")->field("id")->where(["goods_id"=>$info["goods_id"],"collage_state"=>0])->select();
		die;
        foreach ($lists as $v) {
            $arr = db("order_collage_item")->field("collage_win")->where(["pid"=>$v["id"],"collage_state"=>1])->order("pay_time asc")->select();

            $number = 0;
            $success = 0;
            foreach ($arr as $r) {
                $number = $number + 1;
                if ($r["collage_win"]) $success = 1;
            }

            if ($number) {
                // 有成功人，只有失败人可以进团
                // 没有成功人，次团人数少于三人不限制人进团，大于等于三人只有成功人可以进团
                if ($success) {
                    if ($win) {
                        $id = $v["id"];
                    } else {
                        if (!isset($arr_a[$info["goods_id"]])) {
                            $id = $v["id"];
                            $win = 1;
                        }
                    }
                } else {
                    if ($number >= 3) {
                        if (!$win) $id = $v["id"];
                    } else {
                        $id = $v["id"];
                    }
                }
            }

            if ($id) break;
        }


        if (!$id) {
            $id = $this->db->insertGetid([
                "openid"=>$info["openid"],
                "goods_id"=>$info["goods_id"],
                "collageNo"=>$this->collage_no(),
                "collage_win"=>$win ? 0 : 1
            ]);
        }

        $orderNo = $this->orderNo();
        $this->o_t_db->insert([
            "pid"=>$id,
            "openid"=>$info["openid"],
            "orderNo"=>$orderNo,
            "out_trade_no"=>$orderNo,
            "title"=>$info["title"],
            "thumb"=>$info["thumb"],
            "gg_id"=>0,
            "gg_title"=>"",
            "gg_money"=>$info["gg_money"],
            "number"=>1,
            "name"=>$info["name"],
            "phone"=>$info["phone"],
            "address"=>$info["address"],
            "content"=>$info["content"],
            "collage_win"=>$win ? 0 : 1,
            "collage_error"=>$c,
            "collage_red_bag"=>$info["collage_red_bag"]
        ]);

        return $orderNo;
    }







 /* public function collage_addx ()
    {
        $info = input();
        $count =  db("order_collage_item")->alias("a")
                    ->join("yado_order_collage b","a.pid = b.id")
                    ->where(["b.collage_state"=>0,"b.goods_id"=>$info["goods_id"],"a.openid"=>$info["openid"],"a.collage_state"=>1])
                    ->count("a.id");
        if ($count){
			return "有进行中的拼团";
		}

        $start_time = strtotime(date("Y-m-d 09:00:00",time()));
        $end_time = strtotime(date("Y-m-d 23:00:00",time()));
       // if ($start_time > time() || $end_time < time()) return "error1";
		
        $member = db("member")->field("collage_count, collage_success, collage_error")->where("openid", $info["openid"])->find();
        $collage_count = $member["collage_count"] ? string2array($member["collage_count"]) : [];
        $collage_success = $member["collage_success"] ? string2array($member["collage_success"]) : [];
        $collage_error = $member["collage_error"] ? string2array($member["collage_error"]) : [];
		
        $collage_count = $collage_count[$info["goods_id"]] ?? 0;
        $collage_success = $collage_success[$info["goods_id"]] ?? 0;
        $collage_error = $collage_error[$info["goods_id"]] ?? 0; 
		//collage_count 0 1 2 3 4
	// 		if($collage_count == 4 && $collage_success == 1){
			$win = 0;
	//	}elseif($collage_count==0 && $collage_success== 0){
	//		$win = 0;
	//	}else{
	//		$win = 1; 
	//	} 
		if($collage_count==0 && $collage_success== 0){
			$win = 1;
		}else{
			$win = 0; 
		}
		///////1中奖 0不中
		
	
	//	echo$win;die;
		
		$id = 0;
        $lists = db("order_collage")->field("id,collage_state")->where(["goods_id"=>$info["goods_id"],"collage_state"=>0])->select();
        foreach ($lists as $v) {
            $arr = db("order_collage_item")->field("collage_win")->where(["pid"=>$v["id"],"collage_state"=>1])->order("pay_time asc")->select();
			
			if($arr){
				$or = db("order_collage_item")->where(["pid"=>$v["id"],"openid"=>$info["openid"],"collage_state"=>0])->find();
				if($or){
					return $or['orderNo'];
				}

			}
            $number = 0;
            $success = 0;
            foreach ($arr as $r) {
                $number = $number + 1;
                if ($r["collage_win"] == 1) {
					$success = 1;
				}
            }
            if ($number) {
                if ($success == 1) {
					//echo $v['id'];
					//echo '有成功人';
					// 有成功人，只有失败可以进团
                    if ($win == 0) {
                        $id = $v["id"];
                    }
                } else {
					//echo '无成功人';
					// 没有成功人，次团人数少于三人不限制人进团，大于等于三人只有成功人可以进团
                    if ($number >= 3) {
						//echo '大于三人';
						$status=db("order_collage")->where(["id"=>$v["id"]])->find();
						$nmuss= db("order_collage_item")->where(["pid"=>$v["id"],"collage_state"=>1])->count();
						if($status['collage_state'] >0 && $nmuss >= 3){
														//echo 'vvvvv';

						}elseif ($win == 1){
							$id = $v["id"];
							//echo 'zzzzz';
						} 
                    } else {
						// echo '小于三人';
                        $id = $v["id"];
                    }
                }
            }
            if ($id){
				break;
			} 
        }


        if (!$id) {
            $id = $this->db->insertGetid([
                "openid"=>$info["openid"],
                "goods_id"=>$info["goods_id"],
                "collageNo"=>$this->collage_no(),
                "collage_win"=>$win
            ]);
        }

        $orderNo = $this->orderNo();
        $this->o_t_db->insert([
            "pid"=>$id,
            "openid"=>$info["openid"],
            "orderNo"=>$orderNo,
            "out_trade_no"=>$orderNo,
            "title"=>$info["title"],
            "thumb"=>$info["thumb"],
            "gg_id"=>0,
            "gg_title"=>"",
            "gg_money"=>$info["gg_money"],
            "number"=>1,
            "name"=>$info["name"],
            "phone"=>$info["phone"],
            "address"=>$info["address"],
            "content"=>$info["content"],
            "collage_win"=>$win,
            "collage_error"=>$collage_error,
            "collage_red_bag"=>$info["collage_red_bag"]
        ]);

        return $orderNo;
    } */
	
	
	
	
	
	
	////////////////////202207222350
	
	
	/* 
	    public function collage_addx()
    {
        $info = input();
        $count = db("order_collage_item")->alias("a")
            ->join("yado_order_collage b", "a.pid = b.id")
            ->where(["b.collage_state" => 0, "b.goods_id" => $info["goods_id"], "a.openid" => $info["openid"], "a.collage_state" => 1])
            ->count("a.id");
        if ($count) {
            return "有进行中的拼团";
        }
		
		sleep(1);
        $start_time = strtotime(date("Y-m-d 08:30:00", time()));
        $end_time = strtotime(date("Y-m-d 23:00:00", time()));
        if ($start_time > time() || $end_time < time()) return "error1";
        $member = db("member")->field("collage_count, collage_success, collage_error")->where("openid", $info["openid"])->find();
        $collage_count = $member["collage_count"] ? string2array($member["collage_count"]) : [];
        $collage_success = $member["collage_success"] ? string2array($member["collage_success"]) : [];
        $collage_error = $member["collage_error"] ? string2array($member["collage_error"]) : [];

        $collage_count = $collage_count[$info["goods_id"]] ?? 0;
        $collage_success = $collage_success[$info["goods_id"]] ?? 0;
        $collage_error = $collage_error[$info["goods_id"]] ?? 0;
        //collage_count 0 1 2 3 4
        if ($collage_count == 0 && $collage_success == 0) {
            $win = 1;
        } else {
            $win = 0;
        }
        ///////1中奖 0不中
        //	echo$win;die;
		//新商品 xin=1;
        $id = 0;
        if ($collage_count == 4) {
			//echo 1111;die;
            $lists = db("order_collage")->field("id,collage_state,five")->where(["goods_id" => $info["goods_id"], "collage_state" => 0, 'five' => 1])->select();
            foreach ($lists as $v) {
                $arr = db("order_collage_item")->field("collage_win")->where(["pid" => $v["id"], "collage_state" => 1])->order("pay_time asc")->select();
                if ($arr) {
                    $or = db("order_collage_item")->where(["pid" => $v["id"], "openid" => $info["openid"], "collage_state" => 0])->find();
                    if ($or) {
                        return $or['orderNo'];
                    }
                }
				$count = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
				if($count >= 4){
				}
                $number = 0;
                $success = 0;
                foreach ($arr as $r) {
                    $number = $number + 1;
                    if ($r["collage_win"] == 1) {
                        $success = 1;
                    }
                }
                if ($number) {
                    if ($success == 1) {
                        //echo $v['id'];
                        //echo '有成功人';
                        // 有成功人，只有失败可以进团 
                        if ($win == 0 && $v['five'] == 1) {
                            $id = $v["id"];
                        }
                    } else {
                        //echo '无成功人';
                        // 没有成功人，次团人数少于三人不限制人进团，大于等于三人只有成功人可以进团
                        if ($v['five'] == 1) {
                            if ($number >= 3) {
                                //echo '大于三人';
                                $status = db("order_collage")->where(["id" => $v["id"]])->find();
                                $nmuss = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
                                if ($status['collage_state'] > 0 && $nmuss >= 3) {
                                    //echo 'vvvvv';
                                } elseif ($win == 1) {
                                    $id = $v["id"];
                                    //echo 'zzzzz';
                                }
                            } else {
                                // echo '小于三人';
                                $id = $v["id"];
                            }
                        } else {
                            if ($number >= 3) {
                                //echo '大于三人';
                                $status = db("order_collage")->where(["id" => $v["id"]])->find();
                                $nmuss = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
                                if ($status['collage_state'] > 0 && $nmuss >= 3) {
                                    //echo 'vvvvv';
                                } elseif ($win == 1) {
                                    $id = $v["id"];
                                    //echo 'zzzzz';
                                }
                            }
                        }
                    }
                }
		
                if ($id) {
                    break;
                }
            }
            if (!$id) {
                $id = $this->db->insertGetid([
                    "openid" => $info["openid"],
                    "goods_id" => $info["goods_id"],
                    "five" => 1,
                    "collageNo" => $this->collage_no(),
                    "collage_win" => $win
                ]);
            }
        } else {
            $lists = db("order_collage")->field("id,collage_state,five,ctime")->where(["goods_id" => $info["goods_id"], "collage_state" => 0])->order('five DESC,collage_number DESC,ctime ASC')->select();
            foreach ($lists as $v) {
                $arr = db("order_collage_item")->field("collage_win")->where(["pid" => $v["id"], "collage_state" => 1])->order("pay_time asc")->select();
                if ($arr) {
                    $or = db("order_collage_item")->where(["pid" => $v["id"], "openid" => $info["openid"], "collage_state" => 0])->find();
                    if ($or) {
                        return $or['orderNo'];
                    }
                }
				$count = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
				if($count >= 4){
				}
                $number = 0;
                $success = 0;
                foreach ($arr as $r) {
                    $number = $number + 1;
                    if ($r["collage_win"] == 1) {
                        $success = 1;
                    }
                }
                if ($number) {
                    if ($success == 1) {
                        //echo $v['id'];
                        //echo '有成功人';
                        // 有成功人，只有失败可以进团
                        if ($win == 0 && $v['five'] == 0) {
                            $id = $v["id"];
                        }
                    } else {
                      //  echo '无成功人';
                        // 没有成功人，次团人数少于三人不限制人进团，大于等于三人只有成功人可以进团
                        if ($v['five'] == 0) {
                            if ($number >= 3) {
                                //echo '大于三人';
                                $status = db("order_collage")->where(["id" => $v["id"]])->find();
                                $nmuss = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
                                if ($status['collage_state'] > 0 && $nmuss >= 3) {
                                    //echo 'vvvvv';
                                } elseif ($win == 1) {
                                    $id = $v["id"];
                                    //echo 'zzzzz';
                                }
                            } else {
                               //  echo '小于三人';
                                $id = $v["id"];
                            }
                        } else {
                            if ($number >= 3) {
                                //echo '五人大于三人';
                                $status = db("order_collage")->where(["id" => $v["id"]])->find();
                                $nmuss = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
                                if ($status['collage_state'] > 0 && $nmuss >= 3) {
                                    //echo 'vvvvv';
                                } elseif ($win == 1 ) {
                                    $id = $v["id"];
                                    //echo 'zzzzz';
                                }
                            }else{
								  //echo '五人小于三人';
							//	$nmuss = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1,"collage_win"=>1])->count();
							//	if($win == 1 && $v['five'] == 1 && $nmuss < 1){
								//  $id = $v["id"];
								//}
							}
                        }
                    }
                }
                if ($id) {
                    break;
                }
            }
            if (!$id) {
                $id = $this->db->insertGetid([
                    "openid" => $info["openid"],
                    "goods_id" => $info["goods_id"],
                    "five" => 0,
                    "collageNo" => $this->collage_no(),
                    "collage_win" => $win
                ]);
            }
        }
        $orderNo = $this->orderNo();
        $this->o_t_db->insert([
            "pid" => $id,
            "openid" => $info["openid"],
            "orderNo" => $orderNo,
            "out_trade_no" => $orderNo,
            "title" => $info["title"],
            "thumb" => $info["thumb"],
            "gg_id" => 0,
            "gg_title" => "",
            "gg_money" => $info["gg_money"],
            "number" => 1,
            "name" => $info["name"],
            "phone" => $info["phone"],
            "address" => $info["address"],
            "content" => $info["content"],
            "collage_win" => $win,
            "collage_error" => $collage_error,
            "collage_red_bag" => $info["collage_red_bag"]
        ]);
        return $orderNo;
    }
 */

	//2022年7月27日12:09:49
	public function collage_addxss()
    {
		
        $info = input();
        $count = db("order_collage_item")->alias("a")
            ->join("yado_order_collage b", "a.pid = b.id")
            ->where(["b.collage_state" => 0, "b.goods_id" => $info["goods_id"], "a.openid" => $info["openid"], "a.collage_state" => 1])
            ->count("a.id");
        if ($count) {
            return "有进行中的拼团";
        }
		 $zxccc = db("order_collage_item")->where(["openid"=>$info["openid"],"collage_state"=>["in","1,2,3,"]])->find();
		if(time()< strtotime("+1minute", strtotime($zxccc['pay_time']))){
			 return "有进行中的拼团";
		} 
		sleep(1);
        $start_time = strtotime(date("Y-m-d 09:00:00", time()));
        $end_time = strtotime(date("Y-m-d 23:00:00", time()));
        if ($start_time > time() || $end_time < time()) return "error1";
        $member = db("member")->field("collage_count, collage_success, collage_error")->where("openid", $info["openid"])->find();
        $collage_count = $member["collage_count"] ? string2array($member["collage_count"]) : [];
        $collage_success = $member["collage_success"] ? string2array($member["collage_success"]) : [];
        $collage_error = $member["collage_error"] ? string2array($member["collage_error"]) : [];

        $collage_count = $collage_count[$info["goods_id"]] ?? 0;
        $collage_success = $collage_success[$info["goods_id"]] ?? 0;
        $collage_error = $collage_error[$info["goods_id"]] ?? 0;

        //collage_count 0 1 2 3 4
				/*if 中没中过奖品 如果没中过并且为第四单则必须中奖 win=1
				如果不是第四单并且中过奖 必须不中
				如果不是第四单并且没中过奖 随机 中或者不中 
		*/
		//判断 当前是第五单 并且 没中过
		if($collage_count == 4 && $collage_success == 0){
		//	echo '当前是第五单 并且 没中过';
			//必中
			$win=1; 
			$bb=1;
		}elseif($collage_success >= 1){ //判断如果中过奖
		//	echo '中过奖';
			//必须不中
			$win=0;
			$bb=0;
		}
		if ($collage_success == 0){
			$win=rand(0,1);
			$bb=3;
		}
				
		if($collage_error >0){
			$win=0;
			$bb=0;
		}
		
		
		//echo $win;die;
	/* 	
        if ($collage_count == 0 && $collage_success == 0) {
            $win = 1;
        } else {
            $win = 0;
        } */
        ///////1中奖 0不中
        //	echo$win;die;
		//新商品 xin=1;
 		/* $order_count = db("order_collage")->where(["goods_id" => $info["goods_id"], "collage_state" => 1])->count();
		if($info['goods_id'] == 132){
			$xinx=1;
		}else{
			$xinx=0;
		}
		$win =1;
		if($xinx == 1 && $win==1){
			
			//锁20单
            $id = $this->db->insertGetid([
                "openid" => $info["openid"],
                "goods_id" => $info["goods_id"],
                "five" => 0,
				"collage_number"=>3,
                "collageNo" => $this->collage_no(),
                "collage_win" => $win
            ]);
			$orderNo = $this->orderNo();
			$this->o_t_db->insert([
				"pid" => $id,
				"openid" => $info["openid"],
				"orderNo" => $orderNo,
				"out_trade_no" => $orderNo,
				"title" => $info["title"],
				"thumb" => $info["thumb"],
				"gg_id" => 0,
				"gg_title" => "",
				"gg_money" => $info["gg_money"],
				"number" => 1,
				"name" => $info["name"],
				"phone" => $info["phone"],
				"address" => $info["address"],
				"content" => $info["content"],
				"collage_win" => $win,
				"collage_error" => $collage_error,
				"collage_red_bag" => $info["collage_red_bag"]
			]); 
			return $orderNo;
			  
		}  */
		
		 $id = 0;
            $lists = db("order_collage")->field("id,collage_state,five,ctime")->where(["goods_id" => $info["goods_id"], "collage_state" => 0])->order('ctime ASC,five DESC,collage_number DESC')->select();
            foreach ($lists as $v) {
                $arr = db("order_collage_item")->field("collage_win")->where(["pid" => $v["id"], "collage_state" => 1])->order("pay_time asc")->select();
				$count = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
				if($count >= 4){
				}
                $number = 0;
                $success = 0;
                foreach ($arr as $r) {
                    $number = $number + 1;
                    if ($r["collage_win"] == 1) {
                        $success = 1;
                    }
                }
                if ($number) {
                    if ($success == 1) {
                        //echo $v['id'];
                        //echo '有成功人';
                        // 有成功人，只有失败可以进团
                        if ($win == 0) {
                            $id = $v["id"];
                        }
                    } else {
                      //  echo '无成功人';
                      // 没有成功人，次团人数少于三人不限制人进团，大于等于三人只有成功人可以进团
					  
					    if($bb == 3){
					        					   // if($bb == 3&&$collage_count >0){

							$win = 1;
						} 
						if ($number >= 3) {
                          //echo '大于三人';
                          $status = db("order_collage")->where(["id" => $v["id"]])->find();
                          $nmuss = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
							if ($status['collage_state'] > 0 && $nmuss >= 3) {
                              //echo 'vvvvv';
							} elseif ($win == 1) {
                              $id = $v["id"];
                              //echo 'zzzzz';
							}
						} else {
							//  echo '小于三人';
							$id = $v["id"];
						}
                    }
                }
                if ($id) {
                    break;
                }
            }
            if (!$id) {
                $id = $this->db->insertGetid([
                    "openid" => $info["openid"],
                    "goods_id" => $info["goods_id"],
                    "five" => 0,
                    "collageNo" => $this->collage_no(),
                    "collage_win" => $win
                ]);
            }
        
        $orderNo = $this->orderNo();
        $this->o_t_db->insert([
            "pid" => $id,
            "openid" => $info["openid"],
            "orderNo" => $orderNo,
            "out_trade_no" => $orderNo,
            "title" => $info["title"],
            "thumb" => $info["thumb"],
            "gg_id" => 0,
            "gg_title" => "",
            "gg_money" => $info["gg_money"],
            "number" => 1,
			"goodsid" => $info["goods_id"],
            "name" => $info["name"],
            "phone" => $info["phone"],
            "address" => $info["address"],
            "content" => $info["content"],
            "collage_win" => $win,
            "collage_error" => $collage_error,
            "collage_red_bag" => $info["collage_red_bag"]
        ]);
        return $orderNo;
    }
	
	//2022年8月29日12:09:49
	public function collage_addx220907()
    {
		 $info = input();
        $count = db("order_collage_item")->alias("a")
            ->join("yado_order_collage b", "a.pid = b.id")
            ->where(["b.collage_state" => 0, "b.goods_id" => $info["goods_id"], "a.openid" => $info["openid"], "a.collage_state" => 1])
            ->count("a.id");
        if ($count) {
            return "有进行中的拼团";
        }
		 $zxccc = db("order_collage_item")->where(["openid"=>$info["openid"],"collage_state"=>["in","1,2,3,"]])->find();
		if(time()< strtotime("+1minute", strtotime($zxccc['pay_time']))){
			 return "有进行中的拼团";
		} 
		//die;
		sleep(1);
        $start_time = strtotime(date("Y-m-d 09:00:00", time()));
        $end_time = strtotime(date("Y-m-d 23:00:00", time()));
       // if ($start_time > time() || $end_time < time()) return "error1";
        $member = db("member")->field("collage_count, collage_success, collage_error")->where("openid", $info["openid"])->find();
        $collage_count = $member["collage_count"] ? string2array($member["collage_count"]) : [];
        $collage_success = $member["collage_success"] ? string2array($member["collage_success"]) : [];
        $collage_error = $member["collage_error"] ? string2array($member["collage_error"]) : [];

        $collage_count = $collage_count[$info["goods_id"]] ?? 0;
        $collage_success = $collage_success[$info["goods_id"]] ?? 0;
        $collage_error = $collage_error[$info["goods_id"]] ?? 0;

        //collage_count 0 1 2 3 4
				/*if 中没中过奖品 如果没中过并且为第四单则必须中奖 win=1
				如果不是第四单并且中过奖 必须不中
				如果不是第四单并且没中过奖 随机 中或者不中 
		*/
		//判断 当前是第五单 并且 没中过
/* 		if($collage_count == 4 && $collage_success == 0){
		//	echo '当前是第五单 并且 没中过';
			//必中
			$win=1; 
			$bb=1;
		}elseif($collage_success >= 1){ //判断如果中过奖
		//	echo '中过奖';
			//必须不中
			$win=0;
			$bb=0;
		}
		if ($collage_success == 0){
			$win=rand(0,1);
			$bb=3;
		}
				
		if($collage_error >0){
			$win=0;
			$bb=0;
		}
		*/
		$id = 0; 
		if($collage_count == 0){
			$win = 1;
		}else{
			$win = 0;
		}
		
		//die;
		
        if ($collage_count == 4) {
			//echo 1111;die;
            $lists = db("order_collage")->field("id,collage_state,five")->where(["goods_id" => $info["goods_id"], "collage_state" => 0, 'five' => 1])->select();
            foreach ($lists as $v) {
                $arr = db("order_collage_item")->field("collage_win")->where(["pid" => $v["id"], "collage_state" => 1])->order("pay_time asc")->select();
                if ($arr) {
                    $or = db("order_collage_item")->where(["pid" => $v["id"], "openid" => $info["openid"], "collage_state" => 0])->find();
                    if ($or) {
                        return $or['orderNo'];
                    }
                }
				$count = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
				if($count >= 4){
				}
                $number = 0;
                $success = 0;
                foreach ($arr as $r) {
                    $number = $number + 1;
                    if ($r["collage_win"] == 1) {
                        $success = 1;
                    }
                }
                if ($number) {
                    if ($success == 1) {
                        //echo $v['id'];
                        //echo '有成功人';
                        // 有成功人，只有失败可以进团 
                        if ($win == 0 && $v['five'] == 1) {
                            $id = $v["id"];
                        }
                    } else {
                        //echo '无成功人';
                        // 没有成功人，次团人数少于三人不限制人进团，大于等于三人只有成功人可以进团
                        if ($v['five'] == 1) {
                            if ($number >= 3) {
                                //echo '大于三人';
                                $status = db("order_collage")->where(["id" => $v["id"]])->find();
                                $nmuss = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
                                if ($status['collage_state'] > 0 && $nmuss >= 3) {
                                    //echo 'vvvvv';
                                } elseif ($win == 1) {
                                    $id = $v["id"];
                                    //echo 'zzzzz';
                                }
                            } else {
                                // echo '小于三人';
                                $id = $v["id"];
                            }
                        } else {
                            if ($number >= 3) {
                                //echo '大于三人';
                                $status = db("order_collage")->where(["id" => $v["id"]])->find();
                                $nmuss = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
                                if ($status['collage_state'] > 0 && $nmuss >= 3) {
                                    //echo 'vvvvv';
                                } elseif ($win == 1) {
                                    $id = $v["id"];
                                    //echo 'zzzzz';
                                }
                            }
                        }
                    }
                }
		
                if ($id) {
                    break;
                }
            }
            if (!$id) {
                $id = $this->db->insertGetid([
                    "openid" => $info["openid"],
                    "goods_id" => $info["goods_id"],
                    "five" => 1,
                    "collageNo" => $this->collage_no(),
                    "collage_win" => $win
                ]);
            }
        } else {
			

				$lists = db("order_collage")->field("id,collage_state,five,ctime")->where(["goods_id" => $info["goods_id"], "collage_state" => 0])->order('five DESC,collage_number DESC,ctime ASC')->select();

					foreach ($lists as $v) {
						$arr = db("order_collage_item")->field("collage_win,collage_count")->where(["pid" => $v["id"], "collage_state" => 1])->order("pay_time asc")->select();
						if ($arr) {
							$or = db("order_collage_item")->where(["pid" => $v["id"], "openid" => $info["openid"], "collage_state" => 0])->find();
							if ($or) {
								return $or['orderNo'];
							}
						}
						$count = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
						if($count >= 4){
						}
						$number = 0;
						$success = 0;
						$dan=0;
						foreach ($arr as $r) {
							$number = $number + 1;
							if ($r["collage_win"] == 1) {
								$success = 1;
							}
							if ($r["collage_count"] == $collage_count) {
								$dan = $dan + 1;
							}
							
						}
						if ($number) {
							if ($success == 1) {
								//echo $v['id'];
								//echo '有成功人';
								// 有成功人，只有失败可以进团 
								if ($win == 0 && $dan < 1 &&$v['five'] == 0) {
									$id = $v["id"];
								}
							} else {
								//echo '无成功人';
								// 没有成功人，次团人数少于三人不限制人进团，大于等于三人只有成功人可以进团
									if ($number >= 3) {
										//echo '大于三人';
										$status = db("order_collage")->where(["id" => $v["id"]])->find();
										$nmuss = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
										if ($status['collage_state'] > 0 && $nmuss >= 3) {
											//echo 'vvvvv';
										} elseif ($win == 1) {
											$id = $v["id"];
											//echo 'zzzzz';
										}
									} else {
										// echo '小于三人';
										if($dan < 1 &&$v['five'] == 0){
											$id = $v["id"];
										}
									
									}
							}
						}
				
						if ($id) {
							break;
						}
					}
					if (!$id) {
						$id = $this->db->insertGetid([
							"openid" => $info["openid"],
							"goods_id" => $info["goods_id"],
							"five" => 0,
							"collageNo" => $this->collage_no(),
							"collage_win" => $win
						]);
					}
        }
		
		
        $orderNo = $this->orderNo();
        $this->o_t_db->insert([
            "pid" => $id,
            "openid" => $info["openid"],
            "orderNo" => $orderNo,
            "out_trade_no" => $orderNo,
            "title" => $info["title"],
            "thumb" => $info["thumb"],
            "gg_id" => 0,
            "gg_title" => "",
            "gg_money" => $info["gg_money"],
            "number" => 1,
			"goodsid" => $info["goods_id"],
            "name" => $info["name"],
            "phone" => $info["phone"],
            "address" => $info["address"],
            "content" => $info["content"],
            "collage_win" => $win,
            "collage_error" => $collage_error,
            "collage_red_bag" => $info["collage_red_bag"]
        ]);
        return $orderNo;
      
    }


//220907
	public function collage_addx20221004()
    {
		
		//die;
        $info = input();
        $count = db("order_collage_item")->alias("a")
            ->join("yado_order_collage b", "a.pid = b.id")
            ->where(["b.collage_state" => 0, "b.goods_id" => $info["goods_id"], "a.openid" => $info["openid"], "a.collage_state" => 1])
            ->count("a.id");
        if ($count) {
            return "有进行中的拼团";
        }
		 $zxccc = db("order_collage_item")->where(["openid"=>$info["openid"],"collage_state"=>["in","1,2,3,"]])->find();
		if(time()< strtotime("+1minute", strtotime($zxccc['pay_time']))){
			 return "有进行中的拼团";
		} 
		//die;
		sleep(1);
        $start_time = strtotime(date("Y-m-d 09:00:00", time()));
        $end_time = strtotime(date("Y-m-d 23:00:00", time()));
        if ($start_time > time() || $end_time < time()) return "error1";
        $member = db("member")->field("collage_count, collage_success, collage_error,collage_count_xin")->where("openid", $info["openid"])->find();
        $collage_count = $member["collage_count"] ? string2array($member["collage_count"]) : [];
        $collage_count_xin = $member["collage_count_xin"] ? string2array($member["collage_count_xin"]) : [];
        $collage_success = $member["collage_success"] ? string2array($member["collage_success"]) : [];
        $collage_error = $member["collage_error"] ? string2array($member["collage_error"]) : [];

        $collage_count = $collage_count[$info["goods_id"]] ?? 0;
        $collage_count_xin = $collage_count_xin[$info["goods_id"]] ?? 0;
        $collage_success = $collage_success[$info["goods_id"]] ?? 0;
        $collage_error = $collage_error[$info["goods_id"]] ?? 0;

        //collage_count 0 1 2 3 4
				/*if 中没中过奖品 如果没中过并且为第四单则必须中奖 win=1
				如果不是第四单并且中过奖 必须不中
				如果不是第四单并且没中过奖 随机 中或者不中 
		*/
		
		//collage_count_xin
		
		
		
		$id = 0; 
		
		
				//判断 当前是第五单 并且 没中过
		if($collage_count == 3 && $collage_success == 0){
		//	echo '当前是第五单 并且 没中过';
			//必中
			$win=1; 
			$bb=1;
		}elseif($collage_success >= 1){ //判断如果中过奖
		//	echo '中过奖';
			//必须不中
			$win=0;
			$bb=0;
		}
		if ($collage_success == 0){
			$win=rand(0,1);
			$bb=3;
		}
				
		if($collage_error >0){
			$win=0;
			$bb=0;
		}
		
		
		$goodsss = db("content")->where(["id" => $info["goods_id"]])->find();

		/*
		
		先判断collage_count_xin 是否等于0 如果大于0那么此人是老用户 
		
		如果是老用户执行之前逻辑？直到 collage_count_xin 为零？？？
		
		
		*/
		/*
			判断是否第一单 并且collage_count_xin为0 是执行新逻辑 并且设置 collage_count_xin字段为 1
			
			如果不是第一单 并且 collage_count_xin字段为0 那么是老逻辑
			
			如果不是第一单 但是collage_count_xin为1 那么是新逻辑
			
			所以
			
			先判断 是否第一单 并且 collage_count_xin 等于0 符合 执行新逻辑 不符合 执行新逻辑 那么就是不管是不是第一单 collage_count_xin为1 就是新逻辑
			
			
			判断collage_count_xin是否为1 1执行新逻辑 0 执行老逻辑 
			那么
			 1 新逻辑继续走
			 
			 0 判断是否第五单？如果第五单 结束时 执行修改 collage_count_xin为1 
			 
			 回调分2种 新订单与老订单 新订单走新的
			 
			 老订单 判断第五单执行 即可
		
		*/
		
		//if($collage_count > 0 && $collage_count_xin == 0 ){ //判断当前是否大于第一单 并且新单字段为0 此时 用户老逻辑没有走完 继续执行老逻辑 
		

				$lists = db("order_collage")->field("id,collage_state,five,ctime")->where(["goods_id" => $info["goods_id"], "collage_state" => 0])->order('five DESC,collage_number DESC,ctime ASC')->select();
				foreach ($lists as $v) {
					$arr = db("order_collage_item")->field("collage_win,collage_count")->where(["pid" => $v["id"], "collage_state" => 1])->order("pay_time asc")->select();
/* 					if ($arr) {
						$or = db("order_collage_item")->where(["pid" => $v["id"], "openid" => $info["openid"], "collage_state" => 0])->find();
						if ($or) {
							return $or['orderNo'];
						}
					} */
					$count = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
					if($count >= 3){
					}
					$number = 0;
					$success = 0;
					$dan=0;
					foreach ($arr as $r) {
						$number = $number + 1;
						if ($r["collage_win"] == 1) {
							$success = 1;
						}
						if ($r["collage_count"] == $collage_count) {
							$dan = $dan + 1;
						}
						
					}
					if ($number) {
						if ($success == 1) {
							//echo $v['id'];
							//echo '有成功人';
							// 有成功人，只有失败可以进团 
							if ($win == 0) {
								$id = $v["id"];
							}
						} else {
							//echo '无成功人';
							// 没有成功人，次团人数少于三人不限制人进团，大于等于三人只有成功人可以进团
								if ($number >= 2) {
									//echo '大于三人';
									$status = db("order_collage")->where(["id" => $v["id"]])->find();
									$nmuss = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
									if ($status['collage_state'] > 0 && $nmuss >= 2) {
										//echo 'vvvvv';
									} elseif ($win == 1) {
										$id = $v["id"];
										//echo 'zzzzz';
									}
								} else {
									// echo '小于三人';
									//if($dan < 1 &&$v['five'] == 0){
										$id = $v["id"];
									//}
								
								}
						}
					}
				
					if ($id) {
						break;
					}
				}
				if (!$id) {
					$id = $this->db->insertGetid([
						"openid" => $info["openid"],
						"goods_id" => $info["goods_id"],
						"five" => 0,
						"xin"=>0,
						"collageNo" => $this->collage_no(),
						"collage_win" => $win
					]);
				}
			
			
			$orderNo = $this->orderNo();
			$this->o_t_db->insert([
				"pid" => $id,
				"openid" => $info["openid"],
				"orderNo" => $orderNo,
				"out_trade_no" => $orderNo,
				"title" => $info["title"],
				"thumb" => $info["thumb"],
				"gg_id" => 0,
				"xin"=>0,
				"gg_title" => "",
				"gg_money" => $info["gg_money"],
				"number" => 1,
				"goodsid" => $info["goods_id"],
				"name" => $info["name"],
				"phone" => $info["phone"],
				"address" => $info["address"],
				"content" => $info["content"],
				"collage_win" => $win,
				"collage_error" => $collage_error,
				"collage_red_bag" => $info["collage_red_bag"],
				"collage_red_bag_jiu" => $goodsss["collage_red_bag_jiu"]
			]);
		
  
        return $orderNo;
    }


public function collage_addx()
    {
		//die;
        $info = input();
        $count = db("order_collage_item")->alias("a")
            ->join("yado_order_collage b", "a.pid = b.id")
            ->where(["b.collage_state" => 0, "b.goods_id" => $info["goods_id"], "a.openid" => $info["openid"], "a.collage_state" => 1])
            ->count("a.id");
        if ($count) {
            return "有进行中的拼团";
        }
		 $zxccc = db("order_collage_item")->where(["openid"=>$info["openid"],"collage_state"=>["in","1,2,3,"]])->find();
		if(time()< strtotime("+1minute", strtotime($zxccc['pay_time']))){
			 return "有进行中的拼团";
		} 
		//die;
		sleep(1);
		$aaaaa = date("H", time());
		
		
		
		if($aaaaa < 12){
			$start_time = strtotime(date("Y-m-d 09:30:00", time()));
			$end_time = strtotime(date("Y-m-d 11:00:00", time()));
		}else{
			$start_time = strtotime(date("Y-m-d 14:30:00", time()));
			$end_time = strtotime(date("Y-m-d 16:00:00", time()));
		}
		
		//echo $aaaaa;
		
		// if ($start_time > time() || $end_time < time()) return "error1";

		//die;

        $member = db("member")->field("collage_count, collage_success, collage_error,collage_count_xin")->where("openid", $info["openid"])->find();
        $collage_count = $member["collage_count"] ? string2array($member["collage_count"]) : [];
        $collage_count_xin = $member["collage_count_xin"] ? string2array($member["collage_count_xin"]) : [];
        $collage_success = $member["collage_success"] ? string2array($member["collage_success"]) : [];
        $collage_error = $member["collage_error"] ? string2array($member["collage_error"]) : [];
        $collage_count = $collage_count[$info["goods_id"]] ?? 0;
        $collage_count_xin = $collage_count_xin[$info["goods_id"]] ?? 0;
        $collage_success = $collage_success[$info["goods_id"]] ?? 0;
        $collage_error = $collage_error[$info["goods_id"]] ?? 0;

        //collage_count 0 1 2 3 4
				/*if 中没中过奖品 如果没中过并且为第四单则必须中奖 win=1
				如果不是第四单并且中过奖 必须不中
				如果不是第四单并且没中过奖 随机 中或者不中 
		*/
		$id = 0; 
				//判断 当前是第五单 并且 没中过
/* 		if($collage_count == 1 && $collage_success == 0){
		//	echo '当前是第五单 并且 没中过';
			//必中
			$win=1; 
			$bb=1;
		}elseif($collage_success >= 1){ //判断如果中过奖
		//	echo '中过奖';
			//必须不中
			$win=0;
			$bb=0;
		}
		if ($collage_success == 0){
			$win=rand(0,1);
			$bb=3;
		}
		 */
		
		
		if($collage_count == 0 && $collage_success == 0){
		//	echo '当前是第五单 并且 没中过';
			//必中
			$win=1; 
			$bb=1;
		}elseif($collage_success >= 1){ //判断如果中过奖
		//	echo '中过奖';
			//必须不中
			$win=0;
			$bb=0;
		}
		
		
		if($collage_error >0){
			$win=0;
			$bb=0;
		}
		
		
		if($collage_count == 1){
			
			$win = 0;
			$bb=0;
		}
		$goodsss = db("content")->where(["id" => $info["goods_id"]])->find();
		
		
		
		
		
		if($goodsss['is_xin'] == 1){
			
			if(($goodsss['sales']- 500) < $goodsss['xin_count'] && $collage_count == 0 ){
				
				
				if($collage_success == 0 && $collage_count == 0){
					$win=1;
				}
				
				if (!$id) {
					$id = $this->db->insertGetid([
						"openid" => $info["openid"],
						"goods_id" => $info["goods_id"],
						"five" => 0,
						"xin"=>0,
						"collage_number"=>3,
						"collageNo" => $this->collage_no(),
						"collage_win" => $win
					]);
				}
				$orderNo = $this->orderNo();
				$this->o_t_db->insert([
					"pid" => $id,
					"openid" => $info["openid"],
					"orderNo" => $orderNo,
					"out_trade_no" => $orderNo,
					"title" => $info["title"],
					"thumb" => $info["thumb"],
					"gg_id" => 0,
					"xin"=>0,
					"gg_title" => "",
					"gg_money" => $info["gg_money"],
					"number" => 1,
					"goodsid" => $info["goods_id"],
					"name" => $info["name"],
					"phone" => $info["phone"],
					"address" => $info["address"],
					"content" => $info["content"],
					"collage_win" => $win,
					"collage_error" => $collage_error,
					"collage_red_bag" => $info["collage_red_bag"],
					"collage_red_bag_jiu" => $goodsss["collage_red_bag_jiu"],
										"collage_red_bag_xin" => $goodsss["collage_red_bag_xin"]

				]);
				return $orderNo;
				
			}
			
		}
		//echo 2222;die;
		
		if($info['type'] == 1){
			
			if (!$id) {
				$id = $this->db->insertGetid([
					"openid" => $info["openid"],
					"goods_id" => $info["goods_id"],
					"five" => 0,
					"xin"=>0,
					"collageNo" => $this->collage_no(),
					"collage_win" => $win
				]);
			}
			$orderNo = $this->orderNo();
			$this->o_t_db->insert([
				"pid" => $id,
				"openid" => $info["openid"],
				"orderNo" => $orderNo,
				"out_trade_no" => $orderNo,
				"title" => $info["title"],
				"thumb" => $info["thumb"],
				"gg_id" => 0,
				"xin"=>0,
				"gg_title" => "",
				"gg_money" => $info["gg_money"],
				"number" => 1,
				"goodsid" => $info["goods_id"],
				"name" => $info["name"],
				"phone" => $info["phone"],
				"address" => $info["address"],
				"content" => $info["content"],
				"collage_win" => $win,
				"collage_error" => $collage_error,
				"collage_red_bag" => $info["collage_red_bag"],
					"collage_red_bag_jiu" => $goodsss["collage_red_bag_jiu"],
										"collage_red_bag_xin" => $goodsss["collage_red_bag_xin"]
			]);
			return $orderNo;
		}elseif($info['type'] == 2){
			
				/*
				先判断collage_count_xin 是否等于0 如果大于0那么此人是老用户 
				如果是老用户执行之前逻辑？直到 collage_count_xin 为零？？？
				*/
				/*
					判断是否第一单 并且collage_count_xin为0 是执行新逻辑 并且设置 collage_count_xin字段为 1
					
					如果不是第一单 并且 collage_count_xin字段为0 那么是老逻辑
					
					如果不是第一单 但是collage_count_xin为1 那么是新逻辑
					
					所以
					
					先判断 是否第一单 并且 collage_count_xin 等于0 符合 执行新逻辑 不符合 执行新逻辑 那么就是不管是不是第一单 collage_count_xin为1 就是新逻辑
					
					
					判断collage_count_xin是否为1 1执行新逻辑 0 执行老逻辑 
					那么
					 1 新逻辑继续走
					 
					 0 判断是否第五单？如果第五单 结束时 执行修改 collage_count_xin为1 
					 
					 回调分2种 新订单与老订单 新订单走新的
					 
					 老订单 判断第五单执行 即可
				
				循环列表
				判断有几个成功人
				2个成功人失败进入
				1个或者没有都能进入
				*/
				//if($collage_count > 0 && $collage_count_xin == 0 ){ //判断当前是否大于第一单 并且新单字段为0 此时 用户老逻辑没有走完 继续执行老逻辑 
		
		
				$tuan = db("order_collage")->where(["id" => $info["t_id"], "collage_state" => 0])->find();
				if(!$tuan){
					 return "此团已满1";
				}else{
					$arr = db("order_collage_item")->field("collage_win,collage_count")->where(["pid" => $info["t_id"], "collage_state" => 1])->order("pay_time asc")->select();
					$number = 0;
					$success = 0;
					$shibai = 0;
					$dan=0;
					foreach ($arr as $r) {
						$number = $number + 1;
						if ($r["collage_win"] == 1) {
							$success = $success +1;
						}
						if ($r["collage_win"] == 0) {
							$shibai = 1;
						}
						if ($r["collage_count"] == $collage_count) {
							$dan = $dan + 1;
						}
					}
					
					//判断是否有失败人！！！！！！
					if($shibai == 1){
						//有失败人
							//echo '有失败人';
							// 有失败人，只有成功可以进团 
							if ($win == 1) {
								$id = $info["t_id"];
							}else{
								return "此团已满2";
							}
						
					}else{
						//没有
						
						if ($number >= 2) {
							
							//echo '大于三人';
							$status = db("order_collage")->where(["id" => $v["id"]])->find();
							$nmuss = db("order_collage_item")->where(["pid" => $info["t_id"], "collage_state" => 1])->count();
							if ($status['collage_state'] > 0 && $nmuss >= 2) {
								//echo 'vvvvv';
							//	} elseif ($win == 1) {
							} elseif ($win == 0) {
								$id = $info["t_id"];
								//echo 'zzzzz';
							}else{
								return "此团已满3";
							}
						
						} else {
							// echo '小于三人';
							//if($dan < 1 &&$v['five'] == 0){
								$id = $info["t_id"];
							//}
						
						}
								
					}
					
					$orderNo = $this->orderNo();
					$this->o_t_db->insert([
						"pid" => $id,
						"openid" => $info["openid"],
						"orderNo" => $orderNo,
						"out_trade_no" => $orderNo,
						"title" => $info["title"],
						"thumb" => $info["thumb"],
						"gg_id" => 0,
						"xin"=>0,
						"gg_title" => "",
						"gg_money" => $info["gg_money"],
						"number" => 1,
						"goodsid" => $info["goods_id"],
						"name" => $info["name"],
						"phone" => $info["phone"],
						"address" => $info["address"],
						"content" => $info["content"],
						"collage_win" => $win,
						"collage_error" => $collage_error,
						"collage_red_bag" => $info["collage_red_bag"],
					"collage_red_bag_jiu" => $goodsss["collage_red_bag_jiu"],
										"collage_red_bag_xin" => $goodsss["collage_red_bag_xin"]
					]);
				
		  
					return $orderNo;

					
				}


/* 
				$lists = db("order_collage")->field("id,collage_state,five,ctime")->where(["goods_id" => $info["goods_id"], "collage_state" => 0])->order('five DESC,collage_number DESC,ctime ASC')->select();
				foreach ($lists as $v) {
					$arr = db("order_collage_item")->field("collage_win,collage_count")->where(["pid" => $v["id"], "collage_state" => 1])->order("pay_time asc")->select();
					$count = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
					if($count >= 3){
					}
					$number = 0;
					$success = 0;
					$dan=0;
					foreach ($arr as $r) {
						$number = $number + 1;
						if ($r["collage_win"] == 1) {
							$success = $success +1;
						}
							
						if ($r["collage_win"] == 0) {
							$shibai = 1;
						}
						
						if ($r["collage_count"] == $collage_count) {
							$dan = $dan + 1;
						}
						
					}
					if ($number) {
						
						//判断是否有失败人！！！！！！
						if ($shibai == 1) {
							//echo $v['id'];
							//echo '有失败人';
							// 有失败人，只有成功可以进团 
							if ($win == 1) {
								$id = $v["id"];
							}
						} else {
							//echo '无失败人';
							// 没有成功人，次团人数少于三人不限制人进团，大于等于三人只有成功人可以进团
							
							
							//成功的不足2人，或者大于2人？
							
							//判断团当前人数 大于等于2人的时候 只有失败能进入？
								if ($number >= 2) {
									
									//echo '大于三人';
									$status = db("order_collage")->where(["id" => $v["id"]])->find();
									$nmuss = db("order_collage_item")->where(["pid" => $v["id"], "collage_state" => 1])->count();
									if ($status['collage_state'] > 0 && $nmuss >= 2) {
										//echo 'vvvvv';
								//	} elseif ($win == 1) {
									} elseif ($win == 0) {
										$id = $v["id"];
										//echo 'zzzzz';
									}
								} else {
									// echo '小于三人';
									//if($dan < 1 &&$v['five'] == 0){
										$id = $v["id"];
									//}
								
								}
						}
					}
				
					if ($id) {
						break;
					}
				}
				if (!$id) {
					$id = $this->db->insertGetid([
						"openid" => $info["openid"],
						"goods_id" => $info["goods_id"],
						"five" => 0,
						"xin"=>0,
						"collageNo" => $this->collage_no(),
						"collage_win" => $win
					]);
				}
			
			
			$orderNo = $this->orderNo();
			$this->o_t_db->insert([
				"pid" => $id,
				"openid" => $info["openid"],
				"orderNo" => $orderNo,
				"out_trade_no" => $orderNo,
				"title" => $info["title"],
				"thumb" => $info["thumb"],
				"gg_id" => 0,
				"xin"=>0,
				"gg_title" => "",
				"gg_money" => $info["gg_money"],
				"number" => 1,
				"goodsid" => $info["goods_id"],
				"name" => $info["name"],
				"phone" => $info["phone"],
				"address" => $info["address"],
				"content" => $info["content"],
				"collage_win" => $win,
				"collage_error" => $collage_error,
				"collage_red_bag" => $info["collage_red_bag"],
					"collage_red_bag_jiu" => $goodsss["collage_red_bag_jiu"],
										"collage_red_bag_xin" => $goodsss["collage_red_bag_xin"]
			]);
		
  
			return $orderNo;
			 */
			
		}

	
    }
	
	
    public function pay ()
    {
        $openid = input("openid");
        $orderNo = input("orderNo");

        $data = $this->o_t_db->field("pid,gg_money")->where("out_trade_no",$orderNo)->find();
        $money = $data["gg_money"] * 100;
        // if($openid == "oXgpTt727lSF2pPbMFzWduYppfUg") $money = 1;
		
		$collage_state = $this->db->where("id",$data["pid"])->value("collage_state");
		if($collage_state != 0) return "次团已结束";
		
		$member = db("member")->where("openid",$openid)->find();
		
		//if($member['collage_money']>=$money){
			//$type='yue';
			//db("order_collage_item")->where("out_trade_no",$orderNo)->update(["paytype"=>$type]);
		//	$jsApiParameters = yuepay($openid, '购买商品', $orderNo, $money, "collage_a");
	//	}else{
			$type='weixin';
			db("order_collage_item")->where("out_trade_no",$orderNo)->update(["paytype"=>$type]);
			$jsApiParameters = wxpay($openid, '购买商品', $orderNo, $money, "collage_a");
		//}



        //$jsApiParameters = wxpay($openid, '购买商品', $orderNo, $money, "collage_a");

        $data = array();
        $data['money'] = $money;
        $data['orderNo'] = $orderNo;
        $data['data'] = $jsApiParameters;
		$data['type'] = $type;

        return json_encode($data);
    }

    private function orderNo (): string
    {
        $chars = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9" );
        $charsLen = count($chars) - 1;
        shuffle($chars);
        $str = "";
        for ($i=1; $i<=4; $i++){
            $str .= $chars[mt_rand(0, $charsLen)];
        }
        return date("ymdHis").trim($str);
    }

    private function collage_no (): string
    {
        $str = date("YmdHis").orderNo(6);

        $count = $this->db->where("collageNo",$str)->count("id");
        if ($count) $str = $this->collage_no();

        return $str;
    }
}