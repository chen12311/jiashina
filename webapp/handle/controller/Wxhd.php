<?php

namespace app\handle\controller;
use think\Controller;
use think\Request;

class Wxhd extends Controller
{
    public $agent_fen_yong_id;      // 代理商分佣 订单ID
    public $agent_fen_yong_openid;  // 代理商分佣 下单人openID
    public $agent_fen_yong_money;   // 代理商分佣 订单金额

    //自动获取模板
    public function __construct()
    { 
        parent::__construct();
    }
 
    /**微信回调**/
    public function index() {
				$modelsasasa = controller('Wxnofiy');

        $a = input("a");

        if ($a) {
            $xml = '<xml><appid><![CDATA[wxcd685d02ba1ce202]]></appid>
<attach><![CDATA[collage_a]]></attach>
<bank_type><![CDATA[OTHERS]]></bank_type>
<cash_fee><![CDATA[1]]></cash_fee>
<fee_type><![CDATA[CNY]]></fee_type>
<is_subscribe><![CDATA[Y]]></is_subscribe>
<mch_id><![CDATA[1550831061]]></mch_id>
<nonce_str><![CDATA[tu6sq44z7uirsqv20f94xpsm1fwms1md]]></nonce_str>
<openid><![CDATA[oXgpTtxP5guvFQkIC_VG-nR_naME]]></openid>
<out_trade_no><![CDATA[2207130008249813]]></out_trade_no>
<result_code><![CDATA[SUCCESS]]></result_code>
<return_code><![CDATA[SUCCESS]]></return_code>
<sign><![CDATA[AD80B39F8739FF18742DF2E31AAC453B398A00151C6D69A09163A8EA23635A39]]></sign>
<time_end><![CDATA[20220707193823]]></time_end>
<total_fee>1</total_fee>
<trade_type><![CDATA[JSAPI]]></trade_type>
<transaction_id><![CDATA[4200001464202207110244280844]]></transaction_id>
</xml>';
        } else {
            //微信返回的数据
            libxml_disable_entity_loader(true);
            $xml = file_get_contents('php://input');

            $dirname = "../logs/pay/" . date("Ymd");
            if (!file_exists($dirname)) {
                mkdir($dirname, 0755, true);
            }
            $myFile = fopen($dirname . "/logs-" . date("H") . " o'clock.txt", "a");
            fwrite($myFile, $xml);
            fwrite($myFile, "\n\r");
            fclose($myFile);
        }


        if(!$xml) return "error";

        $data = json_decode(json_encode(simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA)),true);

        if(($data['return_code']=='SUCCESS') && ($data['result_code']=='SUCCESS')){

            $attach = $data['attach'] ?? 0;
            $money = $data["total_fee"];
            $openid = $data['openid'];
            $out_trade_no = $data['out_trade_no'];
            $transaction_id = $data["transaction_id"];

            if ($attach === "agent_game") {
                // 2022-01-20 新增 start
                $member = db("member")->where("openid",$openid)->find();

                // 是否有上级
                if($member["fx_top_a"]) {
                    $agent_game_level = db("member")->where("id",$member["fx_top_a"])->value("agent_game_level");

                    // 等级为黄金
                    if($agent_game_level == 1) {
                        $number_a = db("agent_game_setup")->where("id", 1)->value("number_a");
                        $count = db("member")->where(["fx_top_a|fx_top_b"=>$member["fx_top_a"], "agent_game_level"=>1])->count();
                        if ($count >= $number_a && $number_a > 0) {
                            db("member")->where("id", $member["fx_top_a"])->update(["agent_game_level"=>2, "agent_game_time"=>date("Y-m-d H:i:s")]);
                        }

                        $number_b = db("agent_game_setup")->where("id", 1)->value("number_b");
                        $count = db("member")->where(["fx_top_a|fx_top_b"=>$member["fx_top_a"], "agent_game_level"=>2])->count();
                        if ($count >= $number_b && $number_b > 0) {
                            db("member")->where("id", $member["fx_top_a"])->update(["agent_game_level"=>3, "agent_game_time"=>date("Y-m-d H:i:s")]);
                        }
                    }

                    // 等级为白金
                    if($agent_game_level == 2) {
                        $number_b = db("agent_game_setup")->where("id", 1)->value("number_b");
                        $count = db("member")->where(["fx_top_a|fx_top_b"=>$member["fx_top_a"], "agent_game_level"=>2])->count();
                        if ($count >= $number_b && $number_b > 0) {
                            db("member")->where("id", $member["fx_top_a"])->update(["agent_game_level"=>3, "agent_game_time"=>date("Y-m-d H:i:s")]);
                        }
                    }

                    // 代理费返佣 给上级
                    $scale_a = db("agent_game_setup")->where("id", 1)->value("scale_a");

                    if($scale_a > 0) {
                        $openid_a = db("member")->where("id",$member["fx_top_a"])->value("openid");
                        $money_a = $scale_a * 100;

                        $info = array();
                        $info['openid'] = $openid_a;
                        $info['orderId'] = $out_trade_no;
                        $info['money'] = $money_a;
                        $info['state'] = 7;
                        $info['type'] = '+';
                        $info['msg'] = "游戏代理商支付会员费上级返佣";
                        $info['intime'] = time();
                        db("record_pay")->insert($info);

                        db("member")->where("openid", $openid_a)->setInc("agent_game_money", $money_a);
                    }
                }

                // 是否有上上级
                if($member["fx_top_b"]) {
                    $agent_game_level = db("member")->where("id",$member["fx_top_b"])->value("agent_game_level");

                    // 等级为黄金
                    if($agent_game_level == 1) {
                        $number_a = db("agent_game_setup")->where("id", 1)->value("number_a");
                        $count = db("member")->where(["fx_top_a|fx_top_b"=>$member["fx_top_b"], "agent_game_level"=>1])->count();
                        if ($count >= $number_a && $number_a > 0) {
                            db("member")->where("id", $member["fx_top_b"])->update(["agent_game_level"=>2, "agent_game_time"=>date("Y-m-d H:i:s")]);
                        }

                        $number_b = db("agent_game_setup")->where("id", 1)->value("number_b");
                        $count = db("member")->where(["fx_top_a|fx_top_b"=>$member["fx_top_b"], "agent_game_level"=>2])->count();
                        if ($count >= $number_b && $number_b > 0) {
                            db("member")->where("id", $member["fx_top_b"])->update(["agent_game_level"=>3, "agent_game_time"=>date("Y-m-d H:i:s")]);
                        }
                    }

                    // 等级为白金
                    if($agent_game_level == 2) {
                        $number_b = db("agent_game_setup")->where("id", 1)->value("number_b");
                        $count = db("member")->where(["fx_top_a|fx_top_b"=>$member["fx_top_b"], "agent_game_level"=>2])->count();
                        if ($count >= $number_b && $number_b > 0) {
                            db("member")->where("id", $member["fx_top_b"])->update(["agent_game_level"=>3, "agent_game_time"=>date("Y-m-d H:i:s")]);
                        }
                    }

                    // 会员费返佣 给上上级
                    $scale_b = db("agent_game_setup")->where("id", 1)->value("scale_b");
                    if($scale_b > 0) {
                        $openid_b = db("member")->where("id",$member["fx_top_b"])->value("openid");
                        $money_b = $scale_b * 100;

                        $info = array();
                        $info['openid'] = $openid_b;
                        $info['orderId'] = $out_trade_no;
                        $info['money'] = $money_b;
                        $info['state'] = 8;
                        $info['type'] = '+';
                        $info['msg'] = "游戏代理商支付会员费上上级返佣";
                        $info['intime'] = time();
                        db("record_pay")->insert($info);

                        db("member")->where("openid", $openid_b)->setInc("agent_game_money", $money_b);
                    }
                }

                if(!$member["agent_game_level"]) {
                    db("member")->where("openid",$openid)->update(["agent_game_level"=>1, "agent_game_time"=>date("Y-m-d H:i:s"), "agent_game_ctime"=>date("Y-m-d H:i:s")]);

                    $info = array();
                    $info['openid'] = $openid;
                    $info['orderId'] = $out_trade_no;
                    $info['money'] = $money;
                    $info['state'] = 6;
                    $info['type'] = '-';
                    $info['msg'] = "游戏代理商支付会员费成为黄金会员";
                    $info['intime'] = time();
                    db("record_pay")->insert($info);
                }
                // 2022-01-20 新增 end
            }

            if ($attach === "collage") {
                //2022-03-17 新增 start

                $order_info = db("order")->field("gg_money,state")->where("out_trade_no", $out_trade_no)->find();

                if ($order_info["state"] == 1) {
                    $this->collage_refund($order_info["gg_money"],$transaction_id,1);

//                    $str = db("member")->where("openid", $order_info["openid"])->value("collage_count");
//                    $arr = $str ? string2array($str) : [];
//                    $collage_count = $arr[$order_info["cid"]] ?? 0;
//
//                    if (!$a) {
//                        $order_info["collageNo"] = $this->collageNo($order_info["cid"],$order_info["openid"],$order_info["collageNo"]);
//                        db("order")->where("id", $order_info['id'])->update(['state' => 2, 'collage' => 1, 'paytime' => time(), 'transaction_id' => $data['transaction_id'], "collageNo"=>$order_info["collageNo"], "collage_count" => $collage_count]);
//
//                        $info = array();
//                        $info['openid'] = $openid;
//                        $info['orderId'] = $order_info["id"];
//                        $info['money'] = $money;
//                        $info['state'] = 12;
//                        $info['type'] = '-';
//                        $info['msg'] = "拼团商品支付";
//                        $info['intime'] = time();
//                        db("record_pay")->insert($info);
//                    }
//
//                    $content_info = db("content")->field("collage_number,collage_red_bag,collage_money_a,collage_money_b,collage_money_c,collage_money_d")->where("id", $order_info['cid'])->find();
//                    $collage_number = $content_info["collage_number"] == 5 ? $content_info["collage_number"] - 1 : $content_info["collage_number"] - 3;

//                    $where = ["collage_team"=>0, "status" => 4, "state" => 2, "collageNo" => $order_info["collageNo"], "openid"=>["neq",0], "out_refund_no"=>""];
//                    $lists = db("order")->field("id, cid, openid, out_trade_no, ggmoney, number, collage_red_bag, transaction_id")->where($where)->limit(4)->order("paytime asc")->select();
//
//                    if ($collage_number == count($lists)) {
//                        $id_str = implode(",",array_column($lists,"id"));
//                        db("order")->where("id","in",$id_str)->setField("collage_team",1);
//
//                        $id_one = 0;
//                        $prize_arr = [];
//                        foreach ($lists as $v) {
//                            $member_info = db("member")->field("collage_count,collage_success,collage_error")->where("openid", $v["openid"])->find();
//                            $arr_a = $member_info["collage_count"] ? string2array($member_info["collage_count"]) : [];
//                            $arr_b = $member_info["collage_success"] ? string2array($member_info["collage_success"]) : [];
//                            $arr_c = $member_info["collage_error"] ? string2array($member_info["collage_error"]) : [];
//
//                            $collage_count = $arr_a[$order_info["cid"]] ?? 0;
//                            $collage_success = $arr_b[$order_info["cid"]] ?? 0;
//                            $collage_error = $arr_c[$order_info["cid"]] ?? 0;
//
//                            if(!$collage_error) {
//                                if (!$collage_success) {
//                                    $prize_arr[] = ["id" => $v["id"], "v" => 1];
//                                    if (!$id_one && $collage_count >= 4) $id_one = $v["id"];
//                                }
//                            }
//                        }
//
//                        $arr = [];
//                        if (!$id_one) {
//                            foreach ($prize_arr as $val) {
//                                $arr[$val['id']] = $val['v'];
//                            }
//                            $rid[] = $this->get_rand($arr); //中奖ID
//                        } else {
//                            $rid[] = $id_one;
//                        }
//
//                        if ($collage_number == 7) {
//                            unset($arr[$rid[0]]);
//                            $rid[] = $this->get_rand($arr);
//                        }
//
//                        if (count($rid)) {
//                            $collage_site = db("collage")->where("id", 1)->find();
//                            foreach ($lists as $v) {
//                                $collage = db("order")->where("id", $v["id"])->value("collage");
//
//                                $member_info = db("member")->field("id, collage_count, collage_success, collage_error")->where("openid", $v["openid"])->find();
//                                $arr_a = $member_info["collage_count"] ? string2array($member_info["collage_count"]) : [];
//                                $arr_b = $member_info["collage_success"] ? string2array($member_info["collage_success"]) : [];
//                                $arr_c = $member_info["collage_error"] ? string2array($member_info["collage_error"]) : [];
//
//                                $arr_b[$order_info["cid"]] = $arr_b[$order_info["cid"]] ?? 0;
//                                $arr_c[$order_info["cid"]] = $arr_c[$order_info["cid"]] ?? 0;
//
//                                $number = $arr_a[$order_info["cid"]] ?? 0;
//                                $number = $number + 1;
//
//                                $stock = db("content")->where("id", $v['cid'])->value("stock");
//                                if (in_array($v['id'], $rid) && $stock > 0) {
//                                    // 中奖
//                                    if ($collage == 1) {
//                                        db("order")->where("id", $v["id"])->update(['state' => 3, 'collage' => 2, 'refund_id' => 2, 'fhtime' => time()]);
//
//                                        // 增加销量
//                                        db("content")->where("id", $v['cid'])->setInc("sales", 1);
//                                        // 减少库存
//                                        db("content")->where("id", $v['cid'])->setDec("stock", 1);
//
//                                        $arr_a[$order_info["cid"]] = $number == 5 ? 0 : $number;
//                                        $arr_b[$order_info["cid"]] = $number == 5 ? 0 : 1;
//
//                                        $str_a = array2string($arr_a);
//                                        $str_b = array2string($arr_b);
//
//                                        db("member")->where("id", $member_info["id"])->update(["collage_count" => $str_a, "collage_success" => $str_b]);
//
//                                        if ($collage_number == 4) {
//                                            // 拼团成功 返现开始
//                                            if ($collage_site["money_a"]) {
//                                                $member = db("member")->field("fx_top_a,fx_top_b")->where("openid", $v["openid"])->find();
//
//                                                // 有上级
//                                                if ($member["fx_top_a"]) {
//                                                    $money_a = $content_info["collage_money_a"] * 100;
//                                                    $openid_a = db("member")->where("id", $member["fx_top_a"])->value("openid");
//                                                    if ($openid_a && $money_a) {
//                                                        $info = array();
//                                                        $info['openid'] = $openid_a;
//                                                        $info['orderId'] = $out_trade_no;
//                                                        $info['money'] = $money_a;
//                                                        $info['state'] = 13;
//                                                        $info['type'] = '+';
//                                                        $info['msg'] = "拼团成功推广金";
//                                                        $info['intime'] = time();
//                                                        db("record_pay")->insert($info);
//
//                                                        db("member")->where("openid", $openid_a)->setInc("collage_money", $money_a);
//                                                    }
//                                                }
//                                            }
//                                            // 拼团成功 返现结束
//                                        }
//                                    }
//                                } else {
//                                    // 未中奖
//                                    $weChat = get_wechat();
//
//                                    $collage_money = $v["ggmoney"] * $v["number"];
//                                    $collage_red_bag = $v["collage_red_bag"];
//                                    $out_refund_no = orderNo();
//
//                                    // 退款 start
//                                    if ($collage == 1) {
//                                        $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
//                                        $data = [
//                                            'appid' => $weChat['appid'],
//                                            'mch_id' => '1550831061',
//                                            'nonce_str' => encrypt(32),
//                                            'transaction_id' => $v["transaction_id"],
//                                            'out_refund_no' => $out_refund_no,
//                                            'total_fee' => $collage_money,
//                                            'refund_fee' => $collage_money,
//                                            'refund_desc' => $stock > 0 ? "拼团失败" : '商家退款'
//                                        ];
//
//                                        $data = array_filter($data);
//                                        ksort($data);
//
//                                        $str = '';
//                                        foreach ($data as $k => $i) {
//                                            $str .= $k . '=' . $i . '&';
//                                        }
//                                        $str .= 'key=8s5r5cfqd453e775dq95795cy7b09x9d';
//
//                                        $data['sign'] = md5($str);
//
//                                        $xml = arraytoxml($data);
//                                        $res = curl($xml, $url);
//
//                                        $dirname = "./logs/refund/" . date("Ymd");
//                                        if (!file_exists($dirname)) {
//                                            mkdir($dirname, 0755, true);
//                                        }
//                                        $myFile = fopen($dirname . "/logs-" . date("H") . " o'clock.txt", "a");
//                                        fwrite($myFile, "transaction_id:{$v['transaction_id']}\n\r" . $res);
//                                        fwrite($myFile, "\n\r\n\r");
//                                        fclose($myFile);
//
//                                        $res_arr = xmltoarray($res);
//
//                                        if ($res_arr["return_code"] == "SUCCESS" && $res_arr["result_code"] == "SUCCESS") {
//                                            db("order")->where("id", $v["id"])->update(["state" => 7, 'collage' => $stock > 0 ? 3 : 5, "out_refund_no" => $out_refund_no]);
//
//                                            if ($stock > 0) {
//                                                $collage_error = $arr_c[$order_info["cid"]] ? 1 : 0;
//                                                if ($arr_c[$order_info["cid"]] > 0) {
//                                                    $arr_c[$order_info["cid"]] = $arr_c[$order_info["cid"]] - 1;
//                                                } else {
//                                                    $arr_a[$order_info["cid"]] = $number >= 5 && $arr_b[$order_info["cid"]] ? 0 : $number;
//                                                    $arr_b[$order_info["cid"]] = $number >= 5 && $arr_b[$order_info["cid"]] ? 0 : $arr_b[$order_info["cid"]];
//                                                }
//
//                                                $str_a = array2string($arr_a);
//                                                $str_b = array2string($arr_b);
//                                                $arr_c = array2string($arr_c);
//
//                                                db("order")->where("id", $v["id"])->update(["refund_id" => $res_arr["refund_id"], "collage_error" => $collage_error]);
//                                                db("member")->where("id", $member_info["id"])->setField(["collage_count" => $str_a, "collage_success" => $str_b, "collage_error" => $arr_c]);
//
//                                                if ($collage_number == 4) {
//                                                    // 拼团失败 返现开始
//                                                    if ($collage_site["money_c"]) {
//                                                        $member = db("member")->field("fx_top_a,fx_top_b")->where("openid", $v["openid"])->find();
//
//                                                        // 有上级
//                                                        if ($member["fx_top_a"]) {
//                                                            $money_a = $content_info["collage_money_c"] * 100;
//                                                            $openid_a = db("member")->where("id", $member["fx_top_a"])->value("openid");
//                                                            if ($openid_a && $money_a) {
//                                                                $info = array();
//                                                                $info['openid'] = $openid_a;
//                                                                $info['orderId'] = $out_trade_no;
//                                                                $info['money'] = $money_a;
//                                                                $info['state'] = 15;
//                                                                $info['type'] = '+';
//                                                                $info['msg'] = "拼团失败推广金";
//                                                                $info['intime'] = time();
//                                                                db("record_pay")->insert($info);
//
//                                                                db("member")->where("openid", $openid_a)->setInc("collage_money", $money_a);
//                                                            }
//                                                        }
//                                                    }
//                                                    // 拼团失败 返现结束
//                                                }
//
//                                                // 发红包 start 未开通
//                                                if ($collage_red_bag > 0) {
//                                                    $collage_red_bag = $collage_red_bag * 100;
//                                                    db("member")->where("openid", $v["openid"])->setInc("collage_money", $collage_red_bag);
//
//                                                    $info = array();
//                                                    $info['openid'] = $v["openid"];
//                                                    $info['orderId'] = $out_trade_no;
//                                                    $info['money'] = $collage_red_bag;
//                                                    $info['state'] = 18;
//                                                    $info['type'] = '+';
//                                                    $info['msg'] = "拼团失败鼓励金";
//                                                    $info['intime'] = time();
//                                                    db("record_pay")->insert($info);
//                                                }
//                                            }
//                                        } else {
//                                            if (isset($res_arr["err_code_des"])) db("order")->where("id", $v["id"])->update(["refund_err_code_des" => $res_arr["err_code_des"]]);
//                                        }
//                                    }
//                                    usleep(mt_rand(500000, 1500000));
//                                }
//                            }
//                        }
//                    }
                }
                // 2022-03-17 新增 end
            }

            if ($attach == "collage_a") {
                $info = db("order_collage_item")->field("id,pid,openid,gg_money,collage_state,collage_win,paytype,title")->where("out_trade_no",$out_trade_no)->find();
				 $member = db("member")->where("openid",$info["openid"])->find();

                // if($a){
                //     print_R($info);die();
                // }
                if ($info && isset($info["id"])) {
                    $info_collage = db("order_collage")->field("goods_id,collage_win,collage_state,collage_number")->where("id",$info["pid"])->find();
                    
                    if($a) {
                        $info["collage_state"] = 0;
                    }
                    
                    // 订单状态为未支付
                    if (!$info["collage_state"]) {
                        $str = db("member")->where("openid",$info["openid"])->value("collage_count");
                        $arr = $str ? string2array($str) : [];
                        $collage_count = $arr[$info_collage["goods_id"]] ?? 0;

                        if (!$a) {
                            
                            db("order_collage_item")->where("id",$info['id'])->update(["collage_state"=>1, "pay_time"=>date("Y-m-d H:i:s",time()), "collage_count"=>$collage_count, "transaction_id"=>$transaction_id]);
                           
                            // if ($info["collage_win"]) {
                            //     $collage_win = db("order_collage")->where("id",$info["pid"])->value("collage_win");
                            //     if($collage_win > 1) {
                            //         $res = $this->collage_refund($info["gg_money"],$transaction_id,1);
                                    
                            //         if ($res["return_code"] == "SUCCESS" && $res["result_code"] == "SUCCESS") {
                            //             db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>9, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
                            //         } else {
                            //             if (isset($res["err_code_des"])) db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_err_code_des"=>$res["err_code_des"]]);
                            //         }
                                    
                            //     } else {
                            //         db("order_collage")->where("id",$info["pid"])->setInc("collage_number");
                            //         if ($info["collage_win"] == 1) db("order_collage")->where("id",$info["pid"])->setField("collage_win",1);
                            //         $info_collage["collage_number"] = $info_collage["collage_number"] + 1;
                            //     }
                            // } else {
                                db("order_collage")->where("id",$info["pid"])->setInc("collage_number");
                                if ($info["collage_win"] == 1) db("order_collage")->where("id",$info["pid"])->setField("collage_win",1);
                                $info_collage["collage_number"] = $info_collage["collage_number"] + 1;
                            // }
    
                            $info_record = array();
                            $info_record['openid'] = $openid;
                            $info_record['orderNo'] = $out_trade_no;
                            $info_record['money'] = $money / 100;
                            $info_record['state'] = 1;
                            $info_record['type'] = '-';
                            $info_record['msg'] = "拼团商品支付";
                            db("record_collage")->insert($info_record);
                        }
                        
                        if($a) {
                            $info_collage["collage_state"] = 0;
                        }
                        
                        $info_collage = db("order_collage")->field("goods_id,collage_win,collage_state,collage_number")->where("id",$info["pid"])->find();
                        // 次团已开团，不可加入，自动退款
                        if ($info_collage["collage_state"] != 0) {
							if($info['paytype'] == 'weixin'){
								$res = $this->collage_refund($info["gg_money"],$transaction_id,1);
								if ($res["return_code"] == "SUCCESS" && $res["result_code"] == "SUCCESS") {
									db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
								} else {
									if (isset($res["err_code_des"])) db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_err_code_des"=>$res["err_code_des"]]);
								}
							}elseif($info['paytype'] == 'yue'){
								$refund_no = orderNo();
								$refund_id ='666680'.time();
								$res = $this->collage_refundyue($info['openid'],$info["gg_money"]);
								if ($res["code"] == "0000") {
									$info_record = [];
									$info_record['openid'] = $openid;
									$info_record['orderNo'] = $out_trade_no;
									$info_record['money'] = $info["gg_money"];
									$info_record['state'] = 6;
									$info_record['state_many'] = 0;
									$info_record['type'] = '+';
									$info_record['msg'] = "拼团已满订单取消退回鼓励金";
									db("record_collage")->insert($info_record);
									db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
								} else {
									db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_err_code_des"=>'拼团已满订单取消退回鼓励金失败']);
								}
							}
							
									$tuikuantongzhi =$modelsasasa->tuan_tk($openid,$out_trade_no,$member['nickname'],$info['title'],$info["gg_money"]);


                        } else {
                            
                            if (!$info_collage["collage_state"] && $info_collage["collage_win"] == 1 && $info_collage["collage_number"] >= 3) {
                                db("order_collage")->where("id",$info["pid"])->update(["collage_state"=>1,"open_time"=>date("Y-m-d H:i:s")]);
                                
                                $info_content = db("content")->field("stock,collage_money_a,collage_money_c,tuanzhang_money,shopuser_id,title,cbmoney")->where("id",$info_collage['goods_id'])->find();
                                
                                $lists = db("order_collage_item")->field("id,pid,openid,orderNo,gg_money,number,transaction_id,collage_win,collage_error,collage_state,collage_red_bag,paytype,collage_red_bag_jiu,collage_red_bag_xin,goodsid")->where(["pid"=>$info["pid"],"collage_state"=>1])->order("collage_win desc, pay_time asc")->select();
                                
                                $number = 0;
                                foreach ($lists as $v) {
                                    if ($info_content["stock"]) {
                                        $info_member = db("member")->field("fx_top_a,collage_count,collage_success,collage_error,collage_count_xin,collage_count_xin_xin")->where("openid",$v["openid"])->find();
    
                                        $arr_a = $info_member["collage_count"] ? string2array($info_member["collage_count"]) : [];
                                        $arr_b = $info_member["collage_success"] ? string2array($info_member["collage_success"]) : [];
    
                                        $count = $arr_a[$info_collage["goods_id"]] ?? 0;
                                        $success = $arr_b[$info_collage["goods_id"]] ?? 0;
    
                                        $count = $count + 1;
    
                                        // 订单状态为已支付
                                        if ($v["collage_state"] == 1) {
                                            // 订单为多余订单
                                            if ($number > 3) {
												if($v['paytype'] == 'weixin'){
													$res = $this->collage_refund($v["gg_money"],$v["transaction_id"],1);
													if ($res["return_code"] == "SUCCESS" && $res["result_code"] == "SUCCESS") {
														db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
													} else {
														if (isset($res["err_code_des"])) db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_err_code_des"=>$res["err_code_des"]]);
													}
												}elseif($v['paytype'] == 'yue'){
													$refund_no = orderNo();
													$refund_id ='666680'.time();
													$res = $this->collage_refundyue($v['openid'],$v["gg_money"]);
													if ($res["code"] == "0000") {
														$info_record = [];
														$info_record['openid'] = $v['openid'];
														$info_record['orderNo'] = $v['orderNo'];
														$info_record['money'] = $v["gg_money"];
														$info_record['state'] = 6;
														$info_record['state_many'] = 0;
														$info_record['type'] = '+';
														$info_record['msg'] = "拼团已满订单取消退回鼓励金多余";
														db("record_collage")->insert($info_record);
														db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
													} else {
														db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_err_code_des"=>'拼团已满订单取消退回鼓励金多余']);
													}
												}
												$tuikuantongzhi =$modelsasasa->tuan_tk($v['openid'],$v['orderNo'],$member['nickname'],$info['title'],$v["gg_money"]);

												
                                            } else {
                                                // 订单为成功订单
                                                if ($v["collage_win"]) {
                                                    db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>2, "finish_time"=>date("Y-m-d H:i:s")]);
        
                                                    // 增加销量、减少库存
                                                    db("content")->where("id",$info_collage['goods_id'])->setInc("sales", 1);
                                                    db("content")->where("id",$info_collage['goods_id'])->setDec("stock", 1);
        
													$arr_xin = $info_member["collage_count_xin"] ? string2array($info_member["collage_count_xin"]) : [];
													
													$arr_xin_xin = $info_member["collage_count_xin_xin"] ? string2array($info_member["collage_count_xin_xin"]) : [];
													$xin_xin = $arr_xin_xin[$info_collage["goods_id"]] ?? 0;


													$xin = $arr_xin[$info_collage["goods_id"]] ?? 0;

													/*

													判断 是不是最新？是的话走新的 不是走老的？
													
													新的 更新 字段 计数?
													
													旧的先继续走 
													往下

													判断当前是第几单  1 4 5
													如果是第一单 直接
													
													*/
													
													if($count == 1 || $count == 5 || $count == 4){
														
														
														/*
														xin 0 那么就是五单的数据？
														xin 1 那么就是四单的？
														xin_xin 1的话就是 新的 2单的？
														
														等于5的时候 直接更新为新？？
														
														等于4 的时候 判断xin是否为1 是的话 不变 不是更新 CCX
														
														等于1的时候 判断?
														
														*/
														
														if($count == 5 && $xin == 0){
															$arr_xinx[$info_collage["goods_id"]] = 1;
															$arr_xinx = array2string($arr_xinx);
															db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx,"collage_count_xin_xin"=>$arr_xinx]);
														}
														
														if($count == 4 && $xin == 1){
															$arr_xinxx[$info_collage["goods_id"]] = 1;
															$arr_xinxx = array2string($arr_xinxx);
															db("member")->where("openid",$v["openid"])->update(["collage_count_xin_xin"=>$arr_xinxx]);
														}
														
														if($count == 1 && $xin == 0 && $xin_xin == 0 ){
															$arr_xinx[$info_collage["goods_id"]] = 1;
															$arr_xinx = array2string($arr_xinx);
															db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx,"collage_count_xin_xin"=>$arr_xinx]);
														}
														if($count == 1 && $xin == 1 && $xin_xin == 0 ){
															$arr_xinx[$info_collage["goods_id"]] = 1;
															$arr_xinx = array2string($arr_xinx);
															db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx,"collage_count_xin_xin"=>$arr_xinx]);
														}	
													}
														
														
													
													if($xin_xin == 1){
															$arr_a[$info_collage["goods_id"]] = $count == 2 ? 0 : $count;
															$arr_b[$info_collage["goods_id"]] = $count == 2 ? 0 : 1;
															$str_a = array2string($arr_a);
															$str_b = array2string($arr_b);
															db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b]);
														
													}else{
														
														if($xin == 1){
															$arr_a[$info_collage["goods_id"]] = $count == 4 ? 0 : $count;
															//$arr_b[$info_collage["goods_id"]] = $count == 4 ? 0 : 1;
															$arr_b[$info_collage["goods_id"]] =0;

															$str_a = array2string($arr_a);
															$str_b = array2string($arr_b);
															db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b]);
															
														}else{
															$arr_a[$info_collage["goods_id"]] = $count == 5 ? 0 : $count;
															$arr_b[$info_collage["goods_id"]] = 0;
															$str_a = array2string($arr_a);
															$str_b = array2string($arr_b);
															db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b]);
														}
													}
			
        
                                                    // 拼团成功 返现开始 有上级
                                                    if ($info_content["collage_money_a"] && $info_member["fx_top_a"]) {
														$fx_openid = db("member")->where("id",$info_member["fx_top_a"])->find();
                                                        if ($fx_openid['openid']) {
                                                            $info_record = [];
                                                            $info_record['openid'] = $fx_openid['openid'];
                                                            $info_record['orderNo'] = $v["orderNo"];
                                                            $info_record['money'] = $info_content["collage_money_a"];
                                                            $info_record['state'] = 2;
                                                            $info_record['type'] = '+';
                                                            $info_record['msg'] = "拼团成功推广金";
                                                            db("record_collage")->insert($info_record);
        
                                                            db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["collage_money_a"] * 100);
                                                            db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["collage_money_a"]);
                                                        }
														
														//level == 1 此人为团长
														if($fx_openid['level'] == 1){
															$info_record = [];
                                                            $info_record['openid'] = $fx_openid['openid'];
                                                            $info_record['orderNo'] = $v["orderNo"];
                                                            $info_record['money'] = $info_content["tuanzhang_money"];
                                                            $info_record['state'] = 9;
                                                            $info_record['type'] = '+';
                                                            $info_record['msg'] = "团长收益";
                                                            db("record_collage")->insert($info_record);
															
                                                            db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["tuanzhang_money"] * 100);
                                                            db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["tuanzhang_money"]);
														}
														
                                                    }
														//给商家分佣 info_content增加字段,shopuser_id,title,cbmoney
														//先获取商家id
														if($info_content['shopuser_id'] != null){
															if($info_content['shopuser_id'] > 0){
																$shopuser=db("member")->field("openid")->where("id",$info_content["shopuser_id"])->find();

																
																$info_record = [];
																$info_record['openid'] = $shopuser['openid'];
																$info_record['orderNo'] = $v["orderNo"];
																$info_record['money'] = $info_content["cbmoney"]/100;
																$info_record['state'] = 1;
																$info_record['type'] = '+';
																$info_record['goods_id'] = $info_collage['goods_id'];
																$info_record['msg'] = "卖出商品".$info_content['title'];
																db("record_business")->insert($info_record);
																db("member")->where("openid",$shopuser['openid'])->setInc("money", $info_content["cbmoney"]);
																
																//record_business
															}
															
														}
														
														
														//增加健康金？？
														
														/*
														
															$info_record = [];
                                                            $info_record['openid'] = $v['openid'];
                                                            $info_record['orderNo'] = $v["orderNo"];
                                                            $info_record['money'] = $info_content["tuanzhang_money"];
                                                            $info_record['state'] = 9;
                                                            $info_record['type'] = '+';
                                                            $info_record['msg'] = "拼团成功增加健康金";
                                                            db("record_collage")->insert($info_record);
															
                                                            db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["tuanzhang_money"] * 100);
                                                            db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["tuanzhang_money"]);
														
														
														*/
														

														$chenggongtongzhi =$modelsasasa->tuan_success($v["openid"],$v["orderNo"],$member['nickname'],$info['title'],$v["gg_money"]);

                                                    // 拼团成功 返现结束
                                                }
        
                                                // 订单为失败订单
                                                if (!$v["collage_win"]) {
													
													if($v['paytype'] == 'weixin'){
														$res = $this->collage_refund($v["gg_money"],$v["transaction_id"]);
			
														if ($res["return_code"] == "SUCCESS" && $res["result_code"] == "SUCCESS") {
															
															$count_a = db("order_collage_item")->where(["pid"=>$v["pid"],"collage_state"=>3])->count("id");
															if ($count_a >= 2) {
																db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
															} else {
																db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>3, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);

																$arr_xin = $info_member["collage_count_xin"] ? string2array($info_member["collage_count_xin"]) : [];
																$arr_xin_xin = $info_member["collage_count_xin_xin"] ? string2array($info_member["collage_count_xin_xin"]) : [];
																$xin_xin = $arr_xin_xin[$info_collage["goods_id"]] ?? 0;
																$xin = $arr_xin[$info_collage["goods_id"]] ?? 0;
																
																
													
																if($count == 1 || $count == 5 || $count == 4){
														
																	
																	/*
																	xin 0 那么就是五单的数据？
																	xin 1 那么就是四单的？
																	xin_xin 1的话就是 新的 2单的？
																	
																	等于5的时候 直接更新为新？？
																	
																	等于4 的时候 判断xin是否为1 是的话 不变 不是更新 CCX
																	
																	等于1的时候 判断?
																	
																	*/
																	
																	if($count == 5 && $xin == 0){
																		$arr_xinx[$info_collage["goods_id"]] = 1;
																		$arr_xinx = array2string($arr_xinx);
																		db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx,"collage_count_xin_xin"=>$arr_xinx]);
																	}
																	
																	if($count == 4 && $xin == 1){
																		$arr_xinxx[$info_collage["goods_id"]] = 1;
																		$arr_xinxx = array2string($arr_xinxx);
																		db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinxx,"collage_count_xin_xin"=>$arr_xinxx]);
																	}
																	
																	if($count == 1 && $xin == 0 && $xin_xin == 0 ){
																		$arr_xinx[$info_collage["goods_id"]] = 1;
																		$arr_xinx = array2string($arr_xinx);
																		db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx,"collage_count_xin_xin"=>$arr_xinx]);
																		
																		
																	}
																	
																															if($count == 1 && $xin == 1 && $xin_xin == 0 ){
															$arr_xinx[$info_collage["goods_id"]] = 1;
															$arr_xinx = array2string($arr_xinx);
															db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx,"collage_count_xin_xin"=>$arr_xinx]);
														}	

																}
																
																
																
															if($xin_xin == 1){
																	$arr_a[$info_collage["goods_id"]] = $count == 2 ? 0 : $count;
																	$arr_b[$info_collage["goods_id"]] = $count == 2 ? 0 : 1;
																
															}else{
																
																if($xin == 1){
																	$arr_a[$info_collage["goods_id"]] = $count == 4 ? 0 : $count;
																	$arr_b[$info_collage["goods_id"]] = 0;
																	
																}else{
																	$arr_a[$info_collage["goods_id"]] = $count == 5 ? 0 : $count;
																	$arr_b[$info_collage["goods_id"]] = 0;
																}
															}

																$arr_c = $info_member["collage_error"] ? string2array($info_member["collage_error"]) : [];
																if (isset($arr_c[$info_collage["goods_id"]]) && $arr_c[$info_collage["goods_id"]] > 0) {
																	$arr_a[$info_collage["goods_id"]] = $arr_a[$info_collage["goods_id"]] - 1;
																	$arr_c[$info_collage["goods_id"]] = $arr_c[$info_collage["goods_id"]] - 1;
																}
					
																$str_a = array2string($arr_a);
																$str_b = array2string($arr_b);
																$str_c = array2string($arr_c);
					
																db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b, "collage_error"=>$str_c]);

				
																// 拼团失败 返现开始 有上级
																if ($info_content["collage_money_c"] && $info_member["fx_top_a"]) {
																	$fx_openid = db("member")->where("id",$info_member["fx_top_a"])->find();
																	if ($fx_openid['openid']) {
																		$info_record = [];
																		$info_record['openid'] = $fx_openid['openid'];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $info_content["collage_money_c"];
																		$info_record['state'] = 3;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "拼团失败推广金";
																		db("record_collage")->insert($info_record);
				
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["collage_money_c"] * 100);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["collage_money_c"]);
																	}
																	
																	
																	//level == 1 此人为团长
																	if($fx_openid['level'] == 1){
																		$info_record = [];
																		$info_record['openid'] = $fx_openid['openid'];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $info_content["tuanzhang_money"];
																		$info_record['state'] = 9;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "团长收益";
																		db("record_collage")->insert($info_record);
																		
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["tuanzhang_money"] * 100);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["tuanzhang_money"]);
																	}
														
														
																}
																// 拼团失败 返现结束
				
																// 发红包 start
																if ($v["collage_red_bag"]) {
																	
																	
																	$info_members = db("member")->field("collage_count_xin,collage_count_xin_xin")->where("openid",$v["openid"])->find();
																	$arr_xins = $info_members["collage_count_xin"] ? string2array($info_members["collage_count_xin"]) : [];
																	$xins = $arr_xins[$info_collage["goods_id"]] ?? 0;
																	$arr_xins_xin = $info_members["collage_count_xin_xin"] ? string2array($info_members["collage_count_xin_xin"]) : [];
																	$xins_xin = $arr_xins_xin[$info_collage["goods_id"]] ?? 0;
																	
																	
																	if($xins_xin == 1){
																		$collage_red_bag = $v["collage_red_bag_xin"];
																	}else{
																		
																		if($xins == 1){
																			$cishu =  5 - $count;
																			$collage_red_bag = $v["collage_red_bag"] * $cishu;
																			
																			$arr_as[$info_collage["goods_id"]] = 0;
																			$str_as = array2string($arr_as);
																			db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_as]);
																		}else{
																			//$collage_red_bag = $v["collage_red_bag_jiu"];
																			$cishu =  6 - $count;
																			$collage_red_bag = $v["collage_red_bag_jiu"] * $cishu;
																			$arr_as[$info_collage["goods_id"]] = 0;
																			$str_as = array2string($arr_as);
																			db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_as]);
																		}
																		
																	}
																	
																	

																	$money = db("collage_many_money")->where("openid",$v["openid"])->value("money");
				
																	$many = 0;
																	$reduce = 0;
																	// if ($money) {
																	//     $many = 1;
																	//     $reduce = min($money, $collage_red_bag / 4);
																	//     $collage_red_bag = $v["collage_red_bag"] - $reduce;
																	// }
				
																	if($xins_xin == 1){
																		
																		$chenggsss = db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"goodsid"=>$v['goodsid']])->order("id desc")->find();
																		$tuikuanaaaa = $this->collage_refund($chenggsss["gg_money"],$chenggsss["transaction_id"]);
																		
																		db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"id"=>$chenggsss['id']])->update(["is_cg_tui"=>1]);

																		
																		
																			$info_recordsssc = [];
																			$info_recordsssc['openid'] = $v["openid"];
																			$info_recordsssc['orderNo'] = $chenggsss["orderNo"];
																			$info_recordsssc['money'] = $chenggsss["gg_money"];
																			$info_recordsssc['state'] = 1;
																			$info_recordsssc['state_many'] = $many;
																			$info_recordsssc['type'] = '+';
																			$info_recordsssc['msg'] = "拼团失败，成功订单直接退款";
																			db("record_collage_yltui")->insert($info_recordsssc);
																			

																	}else{
																		
																		$info_record = [];
																		$info_record['openid'] = $v["openid"];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $collage_red_bag;
																		$info_record['state'] = 4;
																		$info_record['state_many'] = $many;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "拼团失败鼓励金";
																		db("record_collage")->insert($info_record);
					
																		db("member")->where("openid",$v["openid"])->setInc("collage_money",$collage_red_bag * 100);
																		db("member")->where("openid",$v["openid"])->setInc("collage_money_a",$collage_red_bag);
																		db("order_collage_item")->where("id",$v["id"])->setField("collage_many",$many);
																		db("collage_many_money")->where("openid",$v["openid"])->setDec("money",$reduce);
																		db("collage_many_money")->where("openid",$v["openid"])->setInc("money_reduce",$reduce);
																			
																		
																	}
																}
																// 发红包 end
															}
														} else {
															if (isset($res["err_code_des"])) {
																db("order_collage_item")->where("id",$v["id"])->update(["refund_no"=>$res["refund_no"], "refund_err_code_des"=>$res["err_code_des"]]);
															}
														}
													}elseif($v['paytype'] == 'yue'){
														
														
														$refund_no = orderNo();
														$refund_id ='666680'.time();
														$res = $this->collage_refundyue($v['openid'],$v["gg_money"]);
														if ($res["code"] == "0000") {
															
																$info_record = [];
																$info_record['openid'] = $v['openid'];
																$info_record['orderNo'] = $v['orderNo'];
																$info_record['money'] = $v["gg_money"];
																$info_record['state'] = 7;
																$info_record['state_many'] = 0;
																$info_record['type'] = '+';
																$info_record['msg'] = "拼团失败退还支付鼓励金";
																db("record_collage")->insert($info_record);
															
															$count_a = db("order_collage_item")->where(["pid"=>$v["pid"],"collage_state"=>3])->count("id");
															if ($count_a >= 2) {
																db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
															} else {
																db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>3, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
				
/* 																$arr_xin = $info_member["collage_count_xin"] ? string2array($info_member["collage_count_xin"]) : [];
																$xin = $arr_xin[$info_collage["goods_id"]] ?? 0;
																if($count == 1 || $count == 5){
																	if($xin == 0){ 
																		$arr_xinx[$info_collage["goods_id"]] = 1;
																		$arr_xinx = array2string($arr_xinx);
																		db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx]);
																	}
																}
																if($xin == 1){
																	$arr_a[$info_collage["goods_id"]] = $count >= 4 ? 0 : $count;
																	$arr_b[$info_collage["goods_id"]] = $count >= 4 ? 0 : $success;
																	
																}else{
													
																	$arr_a[$info_collage["goods_id"]] = $count >= 5 ? 0 : $count;
																	$arr_b[$info_collage["goods_id"]] = $count >= 5 ? 0 : $success;
																}

																$arr_c = $info_member["collage_error"] ? string2array($info_member["collage_error"]) : [];
																if (isset($arr_c[$info_collage["goods_id"]]) && $arr_c[$info_collage["goods_id"]] > 0) {
																	$arr_a[$info_collage["goods_id"]] = $arr_a[$info_collage["goods_id"]] - 1;
																	$arr_c[$info_collage["goods_id"]] = $arr_c[$info_collage["goods_id"]] - 1;
																}
					
																$str_a = array2string($arr_a);
																$str_b = array2string($arr_b);
																$str_c = array2string($arr_c);
					
																db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b, "collage_error"=>$str_c]); */
				
																$arr_xin = $info_member["collage_count_xin"] ? string2array($info_member["collage_count_xin"]) : [];
																$arr_xin_xin = $info_member["collage_count_xin_xin"] ? string2array($info_member["collage_count_xin_xin"]) : [];
																$xin_xin = $arr_xin_xin[$info_collage["goods_id"]] ?? 0;
																$xin = $arr_xin[$info_collage["goods_id"]] ?? 0;
																
																
													
																if($count == 1 || $count == 5 || $count == 4){
														
																	
																	/*
																	xin 0 那么就是五单的数据？
																	xin 1 那么就是四单的？
																	xin_xin 1的话就是 新的 2单的？
																	
																	等于5的时候 直接更新为新？？
																	
																	等于4 的时候 判断xin是否为1 是的话 不变 不是更新 CCX
																	
																	等于1的时候 判断?
																	
																	*/
																	
																	if($count == 5 && $xin == 0){
																		$arr_xinx[$info_collage["goods_id"]] = 1;
																		$arr_xinx = array2string($arr_xinx);
																		db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx,"collage_count_xin_xin"=>$arr_xinx]);
																	}
																	
																	if($count == 4 && $xin == 1){
																		$arr_xinxx[$info_collage["goods_id"]] = 1;
																		$arr_xinxx = array2string($arr_xinxx);
																		db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinxx,"collage_count_xin_xin"=>$arr_xinxx]);
																	}
																	
																	if($count == 1 && $xin == 0 && $xin_xin == 0 ){
																		$arr_xinx[$info_collage["goods_id"]] = 1;
																		$arr_xinx = array2string($arr_xinx);
																		db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx,"collage_count_xin_xin"=>$arr_xinx]);
																		
																		
																	}
																	
																															if($count == 1 && $xin == 1 && $xin_xin == 0 ){
															$arr_xinx[$info_collage["goods_id"]] = 1;
															$arr_xinx = array2string($arr_xinx);
															db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx,"collage_count_xin_xin"=>$arr_xinx]);
														}	
																}
																
																
																
															if($xin_xin == 1){
																	$arr_a[$info_collage["goods_id"]] = $count == 2 ? 0 : $count;
																	$arr_b[$info_collage["goods_id"]] = $count == 2 ? 0 : 1;
																
															}else{
																
																if($xin == 1){
																	$arr_a[$info_collage["goods_id"]] = $count == 4 ? 0 : $count;
																	//$arr_b[$info_collage["goods_id"]] = $count == 4 ? 0 : 1;
																																		$arr_b[$info_collage["goods_id"]] = 0;

																	
																}else{
																	$arr_a[$info_collage["goods_id"]] = $count == 5 ? 0 : $count;
																	$arr_b[$info_collage["goods_id"]] = 0;
																}
															}

																$arr_c = $info_member["collage_error"] ? string2array($info_member["collage_error"]) : [];
																if (isset($arr_c[$info_collage["goods_id"]]) && $arr_c[$info_collage["goods_id"]] > 0) {
																	$arr_a[$info_collage["goods_id"]] = $arr_a[$info_collage["goods_id"]] - 1;
																	$arr_c[$info_collage["goods_id"]] = $arr_c[$info_collage["goods_id"]] - 1;
																}
					
																$str_a = array2string($arr_a);
																$str_b = array2string($arr_b);
																$str_c = array2string($arr_c);
					
																db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b, "collage_error"=>$str_c]);


																// 拼团失败 返现开始 有上级
																if ($info_content["collage_money_c"] && $info_member["fx_top_a"]) {
																	$fx_openid = db("member")->where("id",$info_member["fx_top_a"])->find();
																	if ($fx_openid['openid']) {
																		$info_record = [];
																		$info_record['openid'] = $fx_openid['openid'];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $info_content["collage_money_c"];
																		$info_record['state'] = 3;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "拼团失败推广金";
																		db("record_collage")->insert($info_record);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["collage_money_c"] * 100);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["collage_money_c"]);
																	}
																	
																	//level == 1 此人为团长
																	if($fx_openid['level'] == 1){
																		$info_record = [];
																		$info_record['openid'] = $fx_openid['openid'];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $info_content["tuanzhang_money"];
																		$info_record['state'] = 9;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "团长收益";
																		db("record_collage")->insert($info_record);
																		
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["tuanzhang_money"] * 100);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["tuanzhang_money"]);
																	}
																	
																	
																}
																// 拼团失败 返现结束
				
																// 发红包 start
																if ($v["collage_red_bag"]) {
																	$info_members = db("member")->field("collage_count_xin,collage_count_xin_xin")->where("openid",$v["openid"])->find();
																	$arr_xins = $info_members["collage_count_xin"] ? string2array($info_members["collage_count_xin"]) : [];
																	$xins = $arr_xins[$info_collage["goods_id"]] ?? 0;
																	$arr_xins_xin = $info_members["collage_count_xin_xin"] ? string2array($info_members["collage_count_xin_xin"]) : [];
																	$xins_xin = $arr_xins_xin[$info_collage["goods_id"]] ?? 0;
																	
																	
																	if($xins_xin == 1){
																		$collage_red_bag = $v["collage_red_bag_xin"];
																	}else{
																		
																		if($xins == 1){
																			$cishu =  5 - $count;
																			$collage_red_bag = $v["collage_red_bag"] * $cishu;
																			
																			$arr_as[$info_collage["goods_id"]] = 0;
																			$str_as = array2string($arr_as);
																			db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_as]);
																		}else{
																			//$collage_red_bag = $v["collage_red_bag_jiu"];
																			$cishu =  6 - $count;
																			$collage_red_bag = $v["collage_red_bag_jiu"] * $cishu;
																			$arr_as[$info_collage["goods_id"]] = 0;
																			$str_as = array2string($arr_as);
																			db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_as]);
																		}
																		
																	}
																	$money = db("collage_many_money")->where("openid",$v["openid"])->value("money");
				
																	$many = 0;
																	$reduce = 0;
																	// if ($money) {
																	//     $many = 1;
																	//     $reduce = min($money, $collage_red_bag / 4);
																	//     $collage_red_bag = $v["collage_red_bag"] - $reduce;
																	// }
				
																	if($xins_xin == 1){
																		
																		$chenggsss = db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"goodsid"=>$v['goodsid']])->order("id desc")->find();
																		$tuikuanaaaa = $this->collage_refund($chenggsss["gg_money"],$chenggsss["transaction_id"]);
																																				db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"id"=>$chenggsss['id']])->update(["is_cg_tui"=>1]);

																		
																		
																			$info_recordsssc = [];
																			$info_recordsssc['openid'] = $v["openid"];
																			$info_recordsssc['orderNo'] = $chenggsss["orderNo"];
																			$info_recordsssc['money'] = $chenggsss["gg_money"];
																			$info_recordsssc['state'] = 1;
																			$info_recordsssc['state_many'] = $many;
																			$info_recordsssc['type'] = '+';
																			$info_recordsssc['msg'] = "拼团失败，成功订单直接退款";
																			db("record_collage_yltui")->insert($info_recordsssc);
																			

																	}else{
																		
																		$info_record = [];
																		$info_record['openid'] = $v["openid"];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $collage_red_bag;
																		$info_record['state'] = 4;
																		$info_record['state_many'] = $many;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "拼团失败鼓励金";
																		db("record_collage")->insert($info_record);
					
																		db("member")->where("openid",$v["openid"])->setInc("collage_money",$collage_red_bag * 100);
																		db("member")->where("openid",$v["openid"])->setInc("collage_money_a",$collage_red_bag);
																		db("order_collage_item")->where("id",$v["id"])->setField("collage_many",$many);
																		db("collage_many_money")->where("openid",$v["openid"])->setDec("money",$reduce);
																		db("collage_many_money")->where("openid",$v["openid"])->setInc("money_reduce",$reduce);
																			
																		
																	}
																}
																// 发红包 end
															}
														} else {
															db("order_collage_item")->where("id",$v["id"])->update(["refund_no"=>$refund_no, "refund_err_code_des"=>'拼团失败逻辑处理失败']);
														}
													}	
														//if($v['collage_state'] == 3){
															$shibaitongzhi =$modelsasasa->tuan_fail($v["openid"],$v["orderNo"],$member['nickname'],$info['title'],$v["gg_money"]);

														//}
													
                                                }
                                            }
    
                                            $number++;
                                        }
                                    } else {
										
										if($v['paytype'] == 'weixin'){
											$res = $this->collage_refund($v["gg_money"],$v["transaction_id"],1);
											if ($res["return_code"] == "SUCCESS" && $res["result_code"] == "SUCCESS") {
												db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
											} else {
												if (isset($res["err_code_des"])) db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_err_code_des"=>$res["err_code_des"]]);
											}
										}elseif($v['paytype'] == 'yue'){
											$refund_no = orderNo();
											$refund_id ='666680'.time();
											$res = $this->collage_refundyue($v['openid'],$v["gg_money"]);
											if ($res["code"] == "0000") {
												$info_record = [];
												$info_record['openid'] = $v['openid'];
												$info_record['orderNo'] = $v['orderNo'];
												$info_record['money'] = $v["gg_money"];
												$info_record['state'] = 8;
												$info_record['state_many'] = 0;
												$info_record['type'] = '+';
												$info_record['msg'] = "库存不足订单取消退回鼓励金";
												db("record_collage")->insert($info_record);
												db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
											} else {
												db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_no"=>$refund_no, "refund_err_code_des"=>'库存不足订单取消退回鼓励金失败']);
											}
										}	
										
											$tuikuantongzhi =$modelsasasa->tuan_tk($v['openid'],$v['orderNo'],$member['nickname'],$info['title'],$v["gg_money"]);

										
                                    }
    
                                    usleep(mt_rand(500000, 1500000));
                                }
                                 
                            }
                        }
                    }
                }
            }

            if ($attach != "agent_game" && $attach != "collage" && $attach != "collage_a") {

                $website = db("website")->where("id",1)->find();

                if ($attach == 1) {
                    $data = db("order")->where("out_trade_no", $out_trade_no)->find();
                    if ($data['state'] == 0) {
                        if (!$data) {
                            $str = sprintf("<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>");
                        } else {
                            $money = $data['ggmoney'];
                            $orderId = $data['id'];

                            $info = array();
                            $info['openid'] = $openid;
                            $info['orderId'] = $orderId;
                            $info['money'] = $money;
                            $info['state'] = 2;
                            $info['type'] = '-';
                            $info['msg'] = "线下订单支付";
                            $info['intime'] = time();
                            db("record_pay")->insert($info);

                            $member = db("member")->where("id", $data['cid'])->find();
                            $website['scale'] = $member['scale'] || $member['scale'] == 0 ? $member['scale'] : $website['scale'];
                            $website['fwf'] = $member['fwf'] || $member['fwf'] == 0 ? $member['fwf'] : $website['fwf'];

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

                            $money = floor($money * (1 - ($website['scale'] + $website['fwf'])));
                            $money = $money < 1 ? 1 : $money;

                            db("order")->where("id", $data['id'])->update(['state' => 7, 'paytime' => time(), 'scale' => $website['scale'], 'fwf' => $website['fwf'], 'transaction_id' => $data['transaction_id']]);
                            db("member")->where("openid", $member['openid'])->setInc("money", $money);
                            orderFx($data['cid'], $data['status']);
                        }
                    }
                } else {
                    $lists = db("order")->where("out_trade_no", $out_trade_no)->select();

                    if (!$lists) {
                        $str = sprintf("<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>");
                    } else {
                        $orderId = '';
                        $money = $kdmoney = 0;
                        foreach ($lists as $k => $v) {
                            $orderId = $v['id'] . ',';
                            $kdmoney = $v['kdmoney'];
                            $money = $money + $v['ggmoney'] * $v['number'];

                            db("content")->where("id", $v['cid'])->setInc("sales", $v['number']);
                            db("content_list")->where("id", $v['ggid'])->setInc("sales", $v['number']);
                            db("order")->where("id", $v['id'])->update(['state' => 2, 'paytime' => time(), 'scale' => $website['scale'], 'fwf' => $website['fwf'], 'transaction_id' => $data['transaction_id']]);

                            if ($v['status'] == 1 || $v['status'] == 3) {
                                orderFxa($v['cid'], $v['status']);
                                $contentData = db("content")->where("id", $v['goods_id'])->find();
                                db("order")->where("id", $v['id'])->update(['scale' => $contentData['order_money'], 'fwf' => $contentData['fwf_money']]);

                                db("member")->where("openid", $v['openid'])->setInc("money", $contentData['fwf_money']);
                            } else if ($v['status'] == 2) {
                                $money = $v['ggmoney'] * $v['number'];
                                $insure_health_info = db("insure_health")->where("openid", $v['openid'])->find();
                                if (!$insure_health_info) {
                                    $data = ['openid' => $v['openid'], 'money' => $money, 'intime' => date("Y-m-d H:i:s"), 'duetime' => date('Y-m-d H:i:s', strtotime('+1 year'))];
                                    $insure_health_info['id'] = db("insure_health")->insertGetId($data);
                                } else {
                                    $data = [];
                                    if (strtotime($insure_health_info['duetime']) < time()) {
                                        $data['openid'] = $v['openid'];
                                        $data['money'] = $money;
                                        $data['intime'] = date("Y-m-d H:i:s");
                                        $data['duetime'] = date('Y-m-d H:i:s', strtotime('+1 year'));
                                        $insure_health_info['id'] = db("insure_health")->insertGetId($data);
                                    } else {
                                        $data['money'] = $insure_health_info['money'] + $money;
                                        $data['duetime'] = date("Y-m-d H:i:s", strtotime("+1 years", strtotime($insure_health_info['duetime'])));
                                        db("insure_health")->where("id", $insure_health_info['id'])->update($data);
                                    }
                                }
                                $data = ['insure_health_id' => $insure_health_info['id'], 'money' => $money, 'intime' => date("Y-m-d H:i:s")];
                                db("insure_health_lists")->insert($data);
                                db("insure")->where("openid", $v['openid'])->update(['insure_health_id' => $insure_health_info['id']]);
                                orderFxb(5, $v['id']);
                            } else {
                                orderFxb(5, $v['id']);
                            }

                            // 分销start
                            $openid = $v['openid'];
                            $cid = $v['status'] == 1 ? $v['goods_id'] : $v['cid'];
                            $content = db("content")->field("`level_a`,`level_b`")->where("id", $cid)->find();
                            $member = db("member")->field("`fx_top_a`,`fx_top_b`")->where("openid", $openid)->find();
                            if ($member['fx_top_a']) {
                                $infoFx = array();
                                $infoFx['order_id'] = $v['id'];
                                $infoFx['openid'] = $openid;
                                $infoFx['userid_a'] = $member['fx_top_a'];
                                $infoFx['money_a'] = $content['level_a'] * $v['number'];
                                db("member")->where("id", $member['fx_top_a'])->setInc("money", $content['level_a']);
                                db("record_pay")->insert(['openid' => $member['fx_top_a'], 'money' => $content['level_a'], 'state' => 4, 'type' => '+', 'msg' => '给上一级分销增加（openid为会员ID不是openid）', 'intime' => time()]);
                                if ($member['fx_top_b']) {
                                    $infoFx['userid_b'] = $member['fx_top_b'];
                                    $infoFx['money_b'] = $content['level_b'] * $v['number'];
                                    db("member")->where("id", $member['fx_top_b'])->setInc("money", $content['level_b']);
                                    db("record_pay")->insert(['openid' => $member['fx_top_b'], 'money' => $content['level_b'], 'state' => 4, 'type' => '+', 'msg' => '给上二级分销增加（openid为会员ID不是openid）', 'intime' => time()]);
                                }
                                $infoFx['intime'] = date("Y-m-d H:i:s", time());
                                db("record_fenxiao")->insert($infoFx);
                            }
                            // 分销end
                        }

                        $money = $money + $kdmoney;
                        $orderId = substr($orderId, 0, strlen($orderId) - 1);

                        $info = array();
                        $info['openid'] = $openid;
                        $info['orderId'] = $orderId;
                        $info['money'] = $money;
                        $info['state'] = 1;
                        $info['type'] = '-';
                        $info['msg'] = "线上订单支付";
                        $info['intime'] = time();
                        db("record_pay")->insert($info);

                        foreach ($lists as $k => $v){
                            // 2021-02-05 新增 start
                            /**
                             * 用户分享商品
                             * 分享用户已购买并且没有被返现
                             * 被分享用户有三人从分享页面购买商品
                             * 全额返现分享人购买商品的金额
                             * 返现
                             */
                            db("z_order_three_fanxian")->where(['id_order' => $v['id'], 'state' => 0])->update(['state' => 1]);
                            $order_three_fanxian_fenxiang_info = db("z_order_three_fanxian_fenxiang")->where(['id_order' => $v['id']])->find();
                            if ($order_three_fanxian_fenxiang_info) {
                                db("z_order_three_fanxian_fenxiang")->where("id", $order_three_fanxian_fenxiang_info['id'])->update(['state' => 1]);
                                if ($id_fanxian = $order_three_fanxian_fenxiang_info['id_fanxian']) {
                                    $z_order_three_fanxian = db("z_order_three_fanxian")->where(['id' => $id_fanxian, 'state' => 1, 'number' => ['<', 3]])->find();
                                    if ($z_order_three_fanxian) {
                                        $number = $z_order_three_fanxian['number'] + 1;
                                        $up_arr = ['number' => $number];
                                        if ($number == 3) {
                                            $up_arr = ['state' => 2];
                                        }

                                        $info = array();
                                        $info['openid'] = $openid;
                                        $info['orderId'] = $v['id'];
                                        $info['money'] = $z_order_three_fanxian['money'];
                                        $info['state'] = 5;
                                        $info['type'] = '+';
                                        $info['msg'] = "分享满三人返现";
                                        $info['intime'] = time();
                                        db("record_pay")->insert($info);

                                        db("z_order_three_fanxian")->where("id", $z_order_three_fanxian['id'])->update($up_arr);
                                        db("z_order_three_fanxian_fenxiang")->where("id", $order_three_fanxian_fenxiang_info['id'])->update(['state' => 2]);
                                    }
                                }
                            }
                            // 2021-02-05 新增 end

                            // 2022-01-20 新增 start
                            /**
                             * 新增游戏代理商
                             * 游戏代理商购买商品返佣
                             * 游戏代理商等级不同给上级分佣不同
                             */
                            if($v["status"] == 3) {
                                $arr = db("member")->field("agent_game, agent_game_level")->where("openid", $v["openid"])->find();
                                if ($arr["agent"]) {
                                    $this->agent_fen_yong_id = $v["id"];
                                    $this->agent_fen_yong_openid = $v["openid"];
                                    $this->agent_fen_yong_money = $v['ggmoney'] * $v['number'];
                                    switch ($arr["agent_game_level"]) {
                                        case 0:
                                            $this->agent_fen_yong(['scale_c', 'scale_d'], "普通");
                                            break;
                                        case 1:
                                            $this->agent_fen_yong(['scale_e', 'scale_f'], "黄金");
                                            break;
                                        case 2:
                                            $this->agent_fen_yong(['scale_g', 'scale_h'], "白金");
                                            break;
                                        case 3:
                                            $this->agent_fen_yong(['scale_i', 'scale_j'], "钻石");
                                            break;
                                    }
                                }
                            }
                            // 2022-01-20 新增 end
                        }
                    }
                }
            }

            $str = sprintf("<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>");
        }else{
            $str = 'fail';
        }

        return $str;
    }

    // 2022-01-20 新增 start
    public function agent_fen_yong($arr = [], $str = "")
    {
        $order_id = $this->agent_fen_yong_id;
        $openid = $this->agent_fen_yong_openid;
        $money = $this->agent_fen_yong_money;

        $scale_a = db("agent_game_setup")->where("id",1)->value($arr[0]);
        $scale_b = db("agent_game_setup")->where("id",1)->value($arr[1]);

        $fx_top_a = db("member")->where("openid",$openid)->value("fx_top_a");
        if($fx_top_a && $scale_a > 0) {
            $openid_a = db("member")->where("id",$fx_top_a)->value("openid");
            $money_a = floor($money * $scale_a);
            $money_a = $money_a < 1 ? 1 : $money_a;

            $info = array();
            $info['openid'] = $openid_a;
            $info['orderId'] = $order_id;
            $info['money'] = $money_a;
            $info['state'] = 9;
            $info['type'] = '+';
            $info['msg'] = "{$str}游戏代理商购买商品给上级返佣";
            $info['intime'] = time();
            db("record_pay")->insert($info);

            db("member")->where("openid", $openid_a)->setInc("money", $money_a);
        }

        $fx_top_b = db("member")->where("openid",$openid)->value("fx_top_b");
        if($fx_top_a && $scale_b > 0) {
            $openid_b = db("member")->where("id",$fx_top_b)->value("openid");
            $money_b = floor($money * $scale_b);
            $money_b = $money_b < 1 ? 1 : $money_b;

            $info = array();
            $info['openid'] = $openid_b;
            $info['orderId'] = $order_id;
            $info['money'] = $money_b;
            $info['state'] = 10;
            $info['type'] = '+';
            $info['msg'] = "{$str}游戏代理商购买商品给上上级返佣";
            $info['intime'] = time();
            db("record_pay")->insert($info);

            db("member")->where("openid", $openid_b)->setInc("money", $money_b);
        }
    }
    // 2022-01-20 新增 end

    //2022-03-17 新增 start
//    function get_rand(array $proArr = [])
//    {
//        $result = '';
//
//        //概率数组的总概率精度
//        $proSum = array_sum($proArr);
//
//        //概率数组循环
//        foreach ($proArr as $key => $proCur) {
//            if($proCur == 7){
//                $result = $key;
//                break;
//            } else {
//                $randNum = mt_rand(1, $proSum);
//                if ($randNum <= $proCur) {
//                    $result = $key;
//                    break;
//                } else {
//                    $proSum -= $proCur;
//                }
//            }
//
//        }
//        unset ($proArr);
//
//        return $result;
//    }
    //2022-03-17 新增 end

//    private function collageNo (int $cid = 0, string $openid = "", string $collageNo = "")
//    {
//        $str = "";
//        $lists_a = db("order")->field("collageNo")->where(["id"=>[">",4445],"openid"=>["neq", 0],"cid"=>$cid,"state"=>2,"status"=>4,"out_refund_no"=>"","collage_team"=>0])->group("collageNo")->select();
//        if(!count($lists_a)) $str = $collageNo;
//
//        if (!$str && $str != $collageNo) {
//            $member = db("member")->field("collage_count, collage_success")->where("openid", $openid)->find();
//            $arr_a = $member["collage_count"] ? string2array($member["collage_count"]) : [];
//            $arr_b = $member["collage_success"] ? string2array($member["collage_success"]) : [];
//
//            $a = $arr_a[$cid] ?? 0;
//            $b = $arr_b[$cid] ?? 0;
//            $state = $a ? ($a == 4 ? 1 : $b) : 0;
//
//            foreach ($lists_a as $v) {
//                $lists_b = db("order")->where(["id" => [">", 4445], "cid" => $cid, "state" => 2, "collageNo" => $v["collageNo"], "openid" => ["neq", 0],"collage_team"=>0])->select();
//                $number = 0;
//                foreach ($lists_b as $r) {
//                    $member = db("member")->field("collage_count, collage_success")->where("openid", $r["openid"])->find();
//                    $arr_a = $member["collage_count"] ? string2array($member["collage_count"]) : [];
//                    $arr_b = $member["collage_success"] ? string2array($member["collage_success"]) : [];
//
//                    $a = $arr_a[$cid] ?? 0;
//                    $b = $arr_b[$cid] ?? 0;
//                    $number = $number + ($a ? ($a == 4 ? 1 : $b) : 0);
//                }
//
//                if (!$state) {
//                    if ($number < 4) $str = $v["collageNo"];
//                } else {
//                    if ($number < 3) $str = $v["collageNo"];
//                }
//
//                if ($str) break;
//            }
//        }
//
//        if(!$str) $str = $collageNo;
//
//        $count = db("order")->where(["id"=>[">",4445],"cid"=>$cid,"collageNo"=>$str,"collage_team"=>1,"openid"=>["neq",0]])->count("id");
//        if ($count) {
//            $str_a = collageNo();
//            db("order")->where(["id"=>[">",4445],"cid"=>$cid,"collageNo"=>$str,"collage_team"=>0,"openid"=>["neq",0]])->setField("collageNo",$str_a);
//            $str = $str_a;
//        }
//
//        return $str;
//    }

    private function collage_refund ($money = 0.00, $transaction_id = 0, $state = 0)
    {
        $weChat = get_wechat();

        $out_refund_no = orderNo();

        $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
        $data = [
            'appid' => $weChat['appid'],
            'mch_id' => '1550831061',
            'nonce_str' => encrypt(32),
            'transaction_id' => $transaction_id,
            'out_refund_no' => $out_refund_no,
            'total_fee' => $money*100,
            'refund_fee' => $money*100,
            'refund_desc' => $state ? "商家退款" : "拼团失败"
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

        $dirname = "../logs/refund/" . date("Ymd");
        if (!file_exists($dirname)) {
            mkdir($dirname, 0755, true);
        }
        $myFile = fopen($dirname . "/logs-" . date("H") . " o'clock.txt", "a");
        fwrite($myFile, "transaction_id:$transaction_id\n\r" . $res);
        fwrite($myFile, "\n\r\n\r");
        fclose($myFile);

        $data = xmltoarray($res);
        $data["refund_no"] = $out_refund_no;

        return $data;
    }
	
	
	
	
	private function collage_refundyue ($openid,$money)
    {
		$res= db("member")->where("openid",$openid)->setInc("collage_money", $money*100);
		if($res){
			$data['code']='0000';
			$data['msg']='退款成功';
		}else{
			$data['code']='1111';
			$data['msg']='退款失败';
		}

        return $data;
    }
	
	//////////////////////////////
	
	    /**余额支付回调**/
   // public function yuepayok() {
    public function yuepayok($openid,$money,$out_trade_no) {
        $orderinfo = db("order_collage_item")->where("out_trade_no",$out_trade_no)->find();
        if($orderinfo){
				$transaction_id ='888800'.time();
                $info = db("order_collage_item")->field("id,pid,openid,collage_state")->where("out_trade_no",$out_trade_no)->find();

                if ($info && isset($info["id"])) {
                    $info_collage = db("order_collage")->field("goods_id,collage_state,collage_number")->where("id",$info["pid"])->find();
                    // 订单状态为未支付
                    if (!$info["collage_state"]) {
                        
                        $str = db("member")->where("openid",$info["openid"])->value("collage_count");
                        $arr = $str ? string2array($str) : [];
                        $collage_count = $arr[$info_collage["goods_id"]] ?? 0;
							db("order_collage_item")->where("id",$info['id'])->update(["collage_state"=>1, "pay_time"=>date("Y-m-d H:i:s",time()), "collage_count"=>$collage_count, "transaction_id"=>$transaction_id]);
							db("order_collage")->where("id",$info["pid"])->setInc("collage_number");

							$info_collage["collage_number"] = $info_collage["collage_number"] + 1;

							$info_record = array();
							$info_record['openid'] = $openid;
							$info_record['orderNo'] = $out_trade_no;
							$info_record['money'] = $money;
							$info_record['state'] = 6;
							$info_record['type'] = '-';
							$info_record['msg'] = "拼团商品鼓励金支付";
							db("record_collage")->insert($info_record);
							
                        if (!$info_collage["collage_state"] && $info_collage["collage_number"] == 4) {
                            db("order_collage")->where("id",$info["pid"])->update(["collage_state"=>1, "open_time"=>date("Y-m-d H:i:s")]);

                            $info_content = db("content")->field("stock,collage_money_a,collage_money_c")->where("id",$info_collage['goods_id'])->find();

                            $lists = db("order_collage_item")->field("id,openid,orderNo,gg_money,number,transaction_id,collage_win,collage_error,collage_state,collage_red_bag")->where(["pid"=>$info["pid"]])->select();

                            $number = 0;
                            foreach ($lists as $v) {
                                if ($info_content["stock"]) {
                                    $info_member = db("member")->field("fx_top_a,collage_count,collage_success,collage_error")->where("openid",$v["openid"])->find();

                                    $arr_a = $info_member["collage_count"] ? string2array($info_member["collage_count"]) : [];
                                    $arr_b = $info_member["collage_success"] ? string2array($info_member["collage_success"]) : [];

                                    $count = $arr_a[$info_collage["goods_id"]] ?? 0;
                                    $success = $arr_b[$info_collage["goods_id"]] ?? 0;

                                    $count = $count + 1;

                                    // 订单状态为已支付
                                    if ($v["collage_state"] == 1) {

                                        // 订单为多余订单
                                        if ($number > 4) {
											$refund_no = orderNo();
											$refund_id ='666680'.time();
											$res= db("member")->where("openid",$openid)->setInc("collage_money", $v["gg_money"]*100);
                                            if ($res) {
												
													$info_record = [];
													$info_record['openid'] = $openid;
													$info_record['orderNo'] = $v["orderNo"];
													$info_record['money'] = $v["gg_money"];
													$info_record['state'] = 5;
													$info_record['state_many'] = 0;
													$info_record['type'] = '+';
													$info_record['msg'] = "多余订单退还鼓励金";
													db("record_collage")->insert($info_record);
													
                                                db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
                                            } else {
                                                db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_err_code_des"=>'退还鼓励金失败']);
                                            }
                                        }

                                        // 订单为成功订单
                                        if ($v["collage_win"]) {
                                            db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>2, "finish_time"=>date("Y-m-d H:i:s")]);

                                            // 增加销量、减少库存
                                            db("content")->where("id",$info_collage['goods_id'])->setInc("sales", 1);
                                            db("content")->where("id",$info_collage['goods_id'])->setDec("stock", 1);

                                            $arr_a[$info_collage["goods_id"]] = $count == 5 ? 0 : $count;
                                            $arr_b[$info_collage["goods_id"]] = $count == 5 ? 0 : 1;

                                            $str_a = array2string($arr_a);
                                            $str_b = array2string($arr_b);

                                            db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b]);

                                            // 拼团成功 返现开始 有上级
                                            if ($info_content["collage_money_a"] && $info_member["fx_top_a"]) {
                                                $fx_openid = db("member")->where("id",$info_member["fx_top_a"])->value("openid");
                                                if ($fx_openid) {
                                                    $info_record = [];
                                                    $info_record['openid'] = $fx_openid;
                                                    $info_record['orderNo'] = $v["orderNo"];
                                                    $info_record['money'] = $info_content["collage_money_a"];
                                                    $info_record['state'] = 2;
                                                    $info_record['type'] = '+';
                                                    $info_record['msg'] = "拼团成功推广金";
                                                    db("record_collage")->insert($info_record);

                                                    db("member")->where("openid",$fx_openid)->setInc("collage_money", $info_content["collage_money_a"] * 100);
                                                    db("member")->where("openid",$fx_openid)->setInc("collage_money_a", $info_content["collage_money_a"]);
                                                }
                                            }
                                            // 拼团成功 返现结束
                                        }

                                        // 订单为失败订单
                                        if (!$v["collage_win"]) {
											
											
											$refund_no = orderNo();
											$refund_id ='666680'.time();
											$res= db("member")->where("openid",$openid)->setInc("collage_money", $v["gg_money"]*100);
                                            if ($res) {
													$info_record = [];
													$info_record['openid'] = $openid;
													$info_record['orderNo'] = $v["orderNo"];
													$info_record['money'] = $v["gg_money"];
													$info_record['state'] = 5;
													$info_record['state_many'] = 0;
													$info_record['type'] = '+';
													$info_record['msg'] = "拼团失败退还鼓励金本金";
													db("record_collage")->insert($info_record);
													
													
                                                db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>3, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);

                                                $arr_a[$info_collage["goods_id"]] = $count >= 5 ? 0 : $count;
                                                $arr_b[$info_collage["goods_id"]] = $count >= 5 ? 0 : $success;

                                                $arr_c = $info_member["collage_error"] ? string2array($info_member["collage_error"]) : [];
                                                if ($v["collage_error"]) {
                                                    $arr_a[$info_collage["goods_id"]] = $arr_a[$info_collage["goods_id"]] - 1;
                                                    $arr_c[$info_collage["goods_id"]] = $arr_c[$info_collage["goods_id"]] - 1;
                                                }

                                                $str_a = array2string($arr_a);
                                                $str_b = array2string($arr_b);
                                                $str_c = array2string($arr_c);

                                                db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b, "collage_error"=>$str_c]);

                                                // 拼团失败 返现开始 有上级
                                                if ($info_content["collage_money_c"] && $info_member["fx_top_a"]) {
                                                    $fx_openid = db("member")->where("id",$info_member["fx_top_a"])->value("openid");
                                                    if ($fx_openid) {
                                                        $info_record = [];
                                                        $info_record['openid'] = $fx_openid;
                                                        $info_record['orderNo'] = $v["orderNo"];
                                                        $info_record['money'] = $info_content["collage_money_c"];
                                                        $info_record['state'] = 3;
                                                        $info_record['type'] = '+';
                                                        $info_record['msg'] = "拼团失败推广金";
                                                        db("record_collage")->insert($info_record);

                                                        db("member")->where("openid",$fx_openid)->setInc("collage_money", $info_content["collage_money_c"] * 100);
                                                        db("member")->where("openid",$fx_openid)->setInc("collage_money_a", $info_content["collage_money_c"]);
                                                    }
                                                }
                                                // 拼团失败 返现结束

                                                // 发红包 start
                                                if ($v["collage_red_bag"]) {
                                                    $collage_red_bag = $v["collage_red_bag"];
                                                    $money = db("collage_many_money")->where("openid",$v["openid"])->value("money");

                                                    $many = 0;
                                                    $reduce = 0;
                                                    // if ($money) {
                                                    //     $many = 1;
                                                    //     $reduce = min($money, $collage_red_bag / 4);
                                                    //     $collage_red_bag = $v["collage_red_bag"] - $reduce;
                                                    // }

																	if($xins_xin == 1){
																		
																		$chenggsss = db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"goodsid"=>$v['goodsid']])->order("id desc")->find();
																		$tuikuanaaaa = $this->collage_refund($chenggsss["gg_money"],$chenggsss["transaction_id"]);
																																				db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"id"=>$chenggsss['id']])->update(["is_cg_tui"=>1]);

																		
																		
																			$info_recordsssc = [];
																			$info_recordsssc['openid'] = $v["openid"];
																			$info_recordsssc['orderNo'] = $chenggsss["orderNo"];
																			$info_recordsssc['money'] = $chenggsss["gg_money"];
																			$info_recordsssc['state'] = 1;
																			$info_recordsssc['state_many'] = $many;
																			$info_recordsssc['type'] = '+';
																			$info_recordsssc['msg'] = "拼团失败，成功订单直接退款";
																			db("record_collage_yltui")->insert($info_recordsssc);
																			

																	}else{
																		
																		$info_record = [];
																		$info_record['openid'] = $v["openid"];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $collage_red_bag;
																		$info_record['state'] = 4;
																		$info_record['state_many'] = $many;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "拼团失败鼓励金";
																		db("record_collage")->insert($info_record);
					
																		db("member")->where("openid",$v["openid"])->setInc("collage_money",$collage_red_bag * 100);
																		db("member")->where("openid",$v["openid"])->setInc("collage_money_a",$collage_red_bag);
																		db("order_collage_item")->where("id",$v["id"])->setField("collage_many",$many);
																		db("collage_many_money")->where("openid",$v["openid"])->setDec("money",$reduce);
																		db("collage_many_money")->where("openid",$v["openid"])->setInc("money_reduce",$reduce);
																			
																		
																	}
                                                }
                                                // 发红包 end
                                            } else {
                                                db("order_collage_item")->where("id",$v["id"])->update(["refund_no"=>$refund_no, "refund_err_code_des"=>'退还鼓励金失败']);
                                            }
                                        }

                                        $number++;
                                    }
                                } else {
										$refund_no = orderNo();
											$refund_id ='666680'.time();
											$res= db("member")->where("openid",$openid)->setInc("collage_money", $v["gg_money"]*100);
										if ($res) {
													$info_record = [];
														$info_record['openid'] = $openid;
														$info_record['orderNo'] = $v["orderNo"];
														$info_record['money'] = $v["gg_money"];
														$info_record['state'] = 5;
														$info_record['state_many'] = 0;
														$info_record['type'] = '+';
														$info_record['msg'] = "拼团失败退还鼓励金";
														db("record_collage")->insert($info_record);
														
											db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
										} else {
											db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_err_code_des"=>'退还鼓励金失败']);
										}
                                }

                                usleep(mt_rand(500000, 1500000));
                            }
                        }
                    }
                }
            $str = 'success';
        }else{
            $str = 'fail';
        }

        return $str;
    }
	
	
	
	public function yuepayoks220907($openid,$money,$out_trade_no){
	
		///collage_refundyue ($openid,$money)
				$info = db("order_collage_item")->field("id,pid,openid,gg_money,collage_state,collage_win,paytype")->where("out_trade_no",$out_trade_no)->find();
                // if($a){
                //     print_R($info);die();
                // }
                if ($info && isset($info["id"])) {
					$transaction_id ='888800'.time();
                    $info_collage = db("order_collage")->field("goods_id,collage_win,collage_state,collage_number")->where("id",$info["pid"])->find();
                    // 订单状态为未支付
                    if (!$info["collage_state"]) {
                        $str = db("member")->where("openid",$info["openid"])->value("collage_count");
                        $arr = $str ? string2array($str) : [];
                        $collage_count = $arr[$info_collage["goods_id"]] ?? 0;
						
								db("order_collage_item")->where("id",$info['id'])->update(["collage_state"=>1, "pay_time"=>date("Y-m-d H:i:s",time()), "collage_count"=>$collage_count, "transaction_id"=>$transaction_id]);
                                db("order_collage")->where("id",$info["pid"])->setInc("collage_number");
                                if ($info["collage_win"] == 1) {
									db("order_collage")->where("id",$info["pid"])->setField("collage_win",1);
								}
                                $info_collage["collage_number"] = $info_collage["collage_number"] + 1;
    
                            $info_record = array();
                            $info_record['openid'] = $openid;
                            $info_record['orderNo'] = $out_trade_no;
                            $info_record['money'] = $money / 100;
                            $info_record['state'] = 5;
                            $info_record['type'] = '-';
                            $info_record['msg'] = "拼团商品使用鼓励金支付";
                            db("record_collage")->insert($info_record);
                        
                        
                        $info_collage = db("order_collage")->field("goods_id,collage_win,collage_state,collage_number")->where("id",$info["pid"])->find();
                        // 次团已开团，不可加入，自动退款
                        if ($info_collage["collage_state"] != 0) {
							
							if($info['paytype'] == 'weixin'){
								$res = $this->collage_refund($info["gg_money"],$transaction_id,1);
								if ($res["return_code"] == "SUCCESS" && $res["result_code"] == "SUCCESS") {
									db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
								} else {
									if (isset($res["err_code_des"])) db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_err_code_des"=>$res["err_code_des"]]);
								}
							}elseif($info['paytype'] == 'yue'){
								$refund_no = orderNo();
								$refund_id ='666680'.time();
								$res = $this->collage_refundyue($info['openid'],$info["gg_money"]);
								if ($res["code"] == "0000") {
									$info_record = [];
									$info_record['openid'] = $openid;
									$info_record['orderNo'] = $out_trade_no;
									$info_record['money'] = $info["gg_money"];
									$info_record['state'] = 6;
									$info_record['state_many'] = 0;
									$info_record['type'] = '+';
									$info_record['msg'] = "拼团已满订单取消退回鼓励金";
									db("record_collage")->insert($info_record);
									db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
								} else {
									db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_err_code_des"=>'拼团已满订单取消退回鼓励金失败']);
								}
							}
                        } else {
                            //此团未开团正常走流程
                            if (!$info_collage["collage_state"] && $info_collage["collage_win"] == 1 && $info_collage["collage_number"] >= 4) {
                                db("order_collage")->where("id",$info["pid"])->update(["collage_state"=>1,"open_time"=>date("Y-m-d H:i:s")]);
                                $info_content = db("content")->field("stock,collage_money_a,collage_money_c,tuanzhang_money,shopuser_id,title,cbmoney")->where("id",$info_collage['goods_id'])->find();
                                $lists = db("order_collage_item")->field("id,pid,openid,orderNo,gg_money,number,transaction_id,collage_win,collage_error,collage_state,collage_red_bag,paytype")->where(["pid"=>$info["pid"],"collage_state"=>1])->order("collage_win desc, pay_time asc")->select();
                                $number = 0;
                                foreach ($lists as $v) {
                                    if ($info_content["stock"]) {
                                        $info_member = db("member")->field("fx_top_a,collage_count,collage_success,collage_error")->where("openid",$v["openid"])->find();
                                        $arr_a = $info_member["collage_count"] ? string2array($info_member["collage_count"]) : [];
                                        $arr_b = $info_member["collage_success"] ? string2array($info_member["collage_success"]) : [];
                                        $count = $arr_a[$info_collage["goods_id"]] ?? 0;
                                        $success = $arr_b[$info_collage["goods_id"]] ?? 0;
                                        $count = $count + 1;
                                        // 订单状态为已支付
                                        if ($v["collage_state"] == 1) {
                                            // 订单为多余订单
                                            if ($number > 4) {
												if($v['paytype'] == 'weixin'){
													$res = $this->collage_refund($v["gg_money"],$v["transaction_id"],1);
													if ($res["return_code"] == "SUCCESS" && $res["result_code"] == "SUCCESS") {
														db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
													} else {
														if (isset($res["err_code_des"])) db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_err_code_des"=>$res["err_code_des"]]);
													}
												}elseif($v['paytype'] == 'yue'){
													$refund_no = orderNo();
													$refund_id ='666680'.time();
													$res = $this->collage_refundyue($v['openid'],$v["gg_money"]);
													if ($res["code"] == "0000") {
														$info_record = [];
														$info_record['openid'] = $v['openid'];
														$info_record['orderNo'] = $v['orderNo'];
														$info_record['money'] = $v["gg_money"];
														$info_record['state'] = 6;
														$info_record['state_many'] = 0;
														$info_record['type'] = '+';
														$info_record['msg'] = "拼团已满订单取消退回鼓励金";
														db("record_collage")->insert($info_record);
														db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
													} else {
														db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_err_code_des"=>'拼团已满订单取消退回鼓励金失败']);
													}
												}
                                            } else {
                                                // 订单为成功订单
                                                if ($v["collage_win"]) {
                                                    db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>2, "finish_time"=>date("Y-m-d H:i:s")]);
        
                                                    // 增加销量、减少库存
                                                    db("content")->where("id",$info_collage['goods_id'])->setInc("sales", 1);
                                                    db("content")->where("id",$info_collage['goods_id'])->setDec("stock", 1);
        
                                                    $arr_a[$info_collage["goods_id"]] = $count == 5 ? 0 : $count;
                                                    $arr_b[$info_collage["goods_id"]] = $count == 5 ? 0 : 1;
        
                                                    $str_a = array2string($arr_a);
                                                    $str_b = array2string($arr_b);
        
                                                    db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b]);
        
                                                    // 拼团成功 返现开始 有上级
                                                    if ($info_content["collage_money_a"] && $info_member["fx_top_a"]) {
                                                        $fx_openid = db("member")->where("id",$info_member["fx_top_a"])->find();
                                                        if ($fx_openid['openid']) {
                                                            $info_record = [];
                                                            $info_record['openid'] = $fx_openid['openid'];
                                                            $info_record['orderNo'] = $v["orderNo"];
                                                            $info_record['money'] = $info_content["collage_money_a"];
                                                            $info_record['state'] = 2;
                                                            $info_record['type'] = '+';
                                                            $info_record['msg'] = "拼团成功推广金";
                                                            db("record_collage")->insert($info_record);
                                                            db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["collage_money_a"] * 100);
                                                            db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["collage_money_a"]);
                                                        }
													//level == 1 此人为团长
														if($fx_openid['level'] == 1){
															$info_record = [];
															$info_record['openid'] = $fx_openid['openid'];
															$info_record['orderNo'] = $v["orderNo"];
															$info_record['money'] = $info_content["tuanzhang_money"];
															$info_record['state'] = 9;
															$info_record['type'] = '+';
															$info_record['msg'] = "团长收益";
															db("record_collage")->insert($info_record);
															
															db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["tuanzhang_money"] * 100);
															db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["tuanzhang_money"]);
														}	
														
                                                    }
														//给商家分佣 info_content增加字段,shopuser_id,title,cbmoney
														//先获取商家id
														if($info_content['shopuser_id'] != null){
															if($info_content['shopuser_id'] > 0){
																$shopuser=db("member")->field("openid")->where("id",$info_content["shopuser_id"])->find();

																
																$info_record = [];
																$info_record['openid'] = $shopuser['openid'];
																$info_record['orderNo'] = $v["orderNo"];
																$info_record['money'] = $info_content["cbmoney"]/100;
																$info_record['state'] = 1;
																$info_record['type'] = '+';
																$info_record['goods_id'] = $info_collage['goods_id'];
																$info_record['msg'] = "卖出商品".$info_content['title'];
																db("record_business")->insert($info_record);
																db("member")->where("openid",$shopuser['openid'])->setInc("money", $info_content["cbmoney"]);
																
																//record_business
															}
															
														}

                                                    // 拼团成功 返现结束
                                                }
        
                                                // 订单为失败订单
                                                if (!$v["collage_win"]) {
													
													
													if($v['paytype'] == 'weixin'){
														$res = $this->collage_refund($v["gg_money"],$v["transaction_id"]);
														if ($res["return_code"] == "SUCCESS" && $res["result_code"] == "SUCCESS") {
															$count_a = db("order_collage_item")->where(["pid"=>$v["pid"],"collage_state"=>3])->count("id");
															if ($count_a >= 2) {
																db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
															} else {
																db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>3, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
				
																$arr_a[$info_collage["goods_id"]] = $count >= 5 ? 0 : $count;
																$arr_b[$info_collage["goods_id"]] = $count >= 5 ? 0 : $success;
				
																$arr_c = $info_member["collage_error"] ? string2array($info_member["collage_error"]) : [];
																if (isset($arr_c[$info_collage["goods_id"]]) && $arr_c[$info_collage["goods_id"]] > 0) {
																	$arr_a[$info_collage["goods_id"]] = $arr_a[$info_collage["goods_id"]] - 1;
																	$arr_c[$info_collage["goods_id"]] = $arr_c[$info_collage["goods_id"]] - 1;
																}
				
																$str_a = array2string($arr_a);
																$str_b = array2string($arr_b);
																$str_c = array2string($arr_c);
				
																db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b, "collage_error"=>$str_c]);
				
																// 拼团失败 返现开始 有上级
																if ($info_content["collage_money_c"] && $info_member["fx_top_a"]) {
																	$fx_openid = db("member")->where("id",$info_member["fx_top_a"])->find();
																	if ($fx_openid['openid']) {
																		$info_record = [];
																		$info_record['openid'] = $fx_openid['openid'];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $info_content["collage_money_c"];
																		$info_record['state'] = 3;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "拼团失败推广金";
																		db("record_collage")->insert($info_record);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["collage_money_c"] * 100);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["collage_money_c"]);
																	}
																	
																	//level == 1 此人为团长
																	if($fx_openid['level'] == 1){
																		$info_record = [];
																		$info_record['openid'] = $fx_openid['openid'];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $info_content["tuanzhang_money"];
																		$info_record['state'] = 9;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "团长收益";
																		db("record_collage")->insert($info_record);
																		
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["tuanzhang_money"] * 100);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["tuanzhang_money"]);
																	}	
														
																}
																// 拼团失败 返现结束
				
																// 发红包 start
																if ($v["collage_red_bag"]) {
																	$collage_red_bag = $v["collage_red_bag"];
																	$money = db("collage_many_money")->where("openid",$v["openid"])->value("money");
				
																	$many = 0;
																	$reduce = 0;
																	// if ($money) {
																	//     $many = 1;
																	//     $reduce = min($money, $collage_red_bag / 4);
																	//     $collage_red_bag = $v["collage_red_bag"] - $reduce;
																	// }
				
																	if($xins_xin == 1){
																		
																		$chenggsss = db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"goodsid"=>$v['goodsid']])->order("id desc")->find();
																		$tuikuanaaaa = $this->collage_refund($chenggsss["gg_money"],$chenggsss["transaction_id"]);
																																				db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"id"=>$chenggsss['id']])->update(["is_cg_tui"=>1]);

																		
																		
																			$info_recordsssc = [];
																			$info_recordsssc['openid'] = $v["openid"];
																			$info_recordsssc['orderNo'] = $chenggsss["orderNo"];
																			$info_recordsssc['money'] = $chenggsss["gg_money"];
																			$info_recordsssc['state'] = 1;
																			$info_recordsssc['state_many'] = $many;
																			$info_recordsssc['type'] = '+';
																			$info_recordsssc['msg'] = "拼团失败，成功订单直接退款";
																			db("record_collage_yltui")->insert($info_recordsssc);
																			

																	}else{
																		
																		$info_record = [];
																		$info_record['openid'] = $v["openid"];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $collage_red_bag;
																		$info_record['state'] = 4;
																		$info_record['state_many'] = $many;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "拼团失败鼓励金";
																		db("record_collage")->insert($info_record);
					
																		db("member")->where("openid",$v["openid"])->setInc("collage_money",$collage_red_bag * 100);
																		db("member")->where("openid",$v["openid"])->setInc("collage_money_a",$collage_red_bag);
																		db("order_collage_item")->where("id",$v["id"])->setField("collage_many",$many);
																		db("collage_many_money")->where("openid",$v["openid"])->setDec("money",$reduce);
																		db("collage_many_money")->where("openid",$v["openid"])->setInc("money_reduce",$reduce);
																			
																		
																	}
																}
																// 发红包 end
															}
														} else {
															if (isset($res["err_code_des"])) {
																db("order_collage_item")->where("id",$v["id"])->update(["refund_no"=>$res["refund_no"], "refund_err_code_des"=>$res["err_code_des"]]);
															}
														}
													}elseif($v['paytype'] == 'yue'){
														
														
														$refund_no = orderNo();
														$refund_id ='666680'.time();
														$res = $this->collage_refundyue($v['openid'],$v["gg_money"]);
														if ($res["code"] == "0000") {
															
																$info_record = [];
																$info_record['openid'] = $v['openid'];
																$info_record['orderNo'] = $v['orderNo'];
																$info_record['money'] = $v["gg_money"];
																$info_record['state'] = 7;
																$info_record['state_many'] = 0;
																$info_record['type'] = '+';
																$info_record['msg'] = "拼团失败退还支付鼓励金";
																db("record_collage")->insert($info_record);
															
															$count_a = db("order_collage_item")->where(["pid"=>$v["pid"],"collage_state"=>3])->count("id");
															if ($count_a >= 2) {
																db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
															} else {
																db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>3, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
				
																$arr_a[$info_collage["goods_id"]] = $count >= 5 ? 0 : $count;
																$arr_b[$info_collage["goods_id"]] = $count >= 5 ? 0 : $success;
				
																$arr_c = $info_member["collage_error"] ? string2array($info_member["collage_error"]) : [];
																if (isset($arr_c[$info_collage["goods_id"]]) && $arr_c[$info_collage["goods_id"]] > 0) {
																	$arr_a[$info_collage["goods_id"]] = $arr_a[$info_collage["goods_id"]] - 1;
																	$arr_c[$info_collage["goods_id"]] = $arr_c[$info_collage["goods_id"]] - 1;
																}
				
																$str_a = array2string($arr_a);
																$str_b = array2string($arr_b);
																$str_c = array2string($arr_c);
				
																db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b, "collage_error"=>$str_c]);
				
																// 拼团失败 返现开始 有上级
																if ($info_content["collage_money_c"] && $info_member["fx_top_a"]) {
																	$fx_openid = db("member")->where("id",$info_member["fx_top_a"])->find();
																	if ($fx_openid['openid']) {
																		$info_record = [];
																		$info_record['openid'] = $fx_openid['openid'];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $info_content["collage_money_c"];
																		$info_record['state'] = 3;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "拼团失败推广金";
																		db("record_collage")->insert($info_record);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["collage_money_c"] * 100);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["collage_money_c"]);
																	}
																	
																	//level == 1 此人为团长
																	if($fx_openid['level'] == 1){
																		$info_record = [];
																		$info_record['openid'] = $fx_openid['openid'];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $info_content["tuanzhang_money"];
																		$info_record['state'] = 9;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "团长收益";
																		db("record_collage")->insert($info_record);
																		
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["tuanzhang_money"] * 100);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["tuanzhang_money"]);
																	}	
																}
																// 拼团失败 返现结束
				
																// 发红包 start
																if ($v["collage_red_bag"]) {
																	$collage_red_bag = $v["collage_red_bag"];
																	$money = db("collage_many_money")->where("openid",$v["openid"])->value("money");
				
																	$many = 0;
																	$reduce = 0;
																	// if ($money) {
																	//     $many = 1;
																	//     $reduce = min($money, $collage_red_bag / 4);
																	//     $collage_red_bag = $v["collage_red_bag"] - $reduce;
																	// }
				
																	if($xins_xin == 1){
																		
																		$chenggsss = db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"goodsid"=>$v['goodsid']])->order("id desc")->find();
																		$tuikuanaaaa = $this->collage_refund($chenggsss["gg_money"],$chenggsss["transaction_id"]);
																																				db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"id"=>$chenggsss['id']])->update(["is_cg_tui"=>1]);

																		
																		
																			$info_recordsssc = [];
																			$info_recordsssc['openid'] = $v["openid"];
																			$info_recordsssc['orderNo'] = $chenggsss["orderNo"];
																			$info_recordsssc['money'] = $chenggsss["gg_money"];
																			$info_recordsssc['state'] = 1;
																			$info_recordsssc['state_many'] = $many;
																			$info_recordsssc['type'] = '+';
																			$info_recordsssc['msg'] = "拼团失败，成功订单直接退款";
																			db("record_collage_yltui")->insert($info_recordsssc);
																			

																	}else{
																		
																		$info_record = [];
																		$info_record['openid'] = $v["openid"];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $collage_red_bag;
																		$info_record['state'] = 4;
																		$info_record['state_many'] = $many;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "拼团失败鼓励金";
																		db("record_collage")->insert($info_record);
					
																		db("member")->where("openid",$v["openid"])->setInc("collage_money",$collage_red_bag * 100);
																		db("member")->where("openid",$v["openid"])->setInc("collage_money_a",$collage_red_bag);
																		db("order_collage_item")->where("id",$v["id"])->setField("collage_many",$many);
																		db("collage_many_money")->where("openid",$v["openid"])->setDec("money",$reduce);
																		db("collage_many_money")->where("openid",$v["openid"])->setInc("money_reduce",$reduce);
																			
																		
																	}
																}
																// 发红包 end
															}
														} else {
															db("order_collage_item")->where("id",$v["id"])->update(["refund_no"=>$refund_no, "refund_err_code_des"=>'拼团失败逻辑处理失败']);
														}
													}			
										
                                                }
                                            }
    
                                            $number++;
                                        }
                                    } else {
										if($v['paytype'] == 'weixin'){
											$res = $this->collage_refund($v["gg_money"],$v["transaction_id"],1);
											if ($res["return_code"] == "SUCCESS" && $res["result_code"] == "SUCCESS") {
												db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
											} else {
												if (isset($res["err_code_des"])) db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_err_code_des"=>$res["err_code_des"]]);
											}
										}elseif($v['paytype'] == 'yue'){
											$refund_no = orderNo();
											$refund_id ='666680'.time();
											$res = $this->collage_refundyue($v['openid'],$v["gg_money"]);
											if ($res["code"] == "0000") {
												$info_record = [];
												$info_record['openid'] = $v['openid'];
												$info_record['orderNo'] = $v['orderNo'];
												$info_record['money'] = $v["gg_money"];
												$info_record['state'] = 8;
												$info_record['state_many'] = 0;
												$info_record['type'] = '+';
												$info_record['msg'] = "库存不足订单取消退回鼓励金";
												db("record_collage")->insert($info_record);
												db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
											} else {
												db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_no"=>$refund_no, "refund_err_code_des"=>'库存不足订单取消退回鼓励金失败']);
											}
										}										
                                    }
    
                                    usleep(mt_rand(500000, 1500000));
                                }
                                
                            }
                        }
                    }
					$str = 'success';
                }else{
					$str = 'fail';

				}	
		return $str;
	}
	
	
	public function yuepayoks($openid,$money,$out_trade_no){
	
		///collage_refundyue ($openid,$money)
				$info = db("order_collage_item")->field("id,pid,openid,gg_money,collage_state,collage_win,paytype")->where("out_trade_no",$out_trade_no)->find();
                // if($a){
                //     print_R($info);die();
                // }
                if ($info && isset($info["id"])) {
					$transaction_id ='888800'.time();
                    $info_collage = db("order_collage")->field("goods_id,collage_win,collage_state,collage_number")->where("id",$info["pid"])->find();
                    // 订单状态为未支付
                    if (!$info["collage_state"]) {
                        $str = db("member")->where("openid",$info["openid"])->value("collage_count");
                        $arr = $str ? string2array($str) : [];
                        $collage_count = $arr[$info_collage["goods_id"]] ?? 0;
						
								db("order_collage_item")->where("id",$info['id'])->update(["collage_state"=>1, "pay_time"=>date("Y-m-d H:i:s",time()), "collage_count"=>$collage_count, "transaction_id"=>$transaction_id]);
                                db("order_collage")->where("id",$info["pid"])->setInc("collage_number");
                                if ($info["collage_win"] == 1) {
									db("order_collage")->where("id",$info["pid"])->setField("collage_win",1);
								}
                                $info_collage["collage_number"] = $info_collage["collage_number"] + 1;
    
                            $info_record = array();
                            $info_record['openid'] = $openid;
                            $info_record['orderNo'] = $out_trade_no;
                            $info_record['money'] = $money / 100;
                            $info_record['state'] = 5;
                            $info_record['type'] = '-';
                            $info_record['msg'] = "拼团商品使用鼓励金支付";
                            db("record_collage")->insert($info_record);
                        
                        
                        $info_collage = db("order_collage")->field("goods_id,collage_win,collage_state,collage_number")->where("id",$info["pid"])->find();
                        // 次团已开团，不可加入，自动退款
                        if ($info_collage["collage_state"] != 0) {
							
							if($info['paytype'] == 'weixin'){
								$res = $this->collage_refund($info["gg_money"],$transaction_id,1);
								if ($res["return_code"] == "SUCCESS" && $res["result_code"] == "SUCCESS") {
									db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
								} else {
									if (isset($res["err_code_des"])) db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_err_code_des"=>$res["err_code_des"]]);
								}
							}elseif($info['paytype'] == 'yue'){
								$refund_no = orderNo();
								$refund_id ='666680'.time();
								$res = $this->collage_refundyue($info['openid'],$info["gg_money"]);
								if ($res["code"] == "0000") {
									$info_record = [];
									$info_record['openid'] = $openid;
									$info_record['orderNo'] = $out_trade_no;
									$info_record['money'] = $info["gg_money"];
									$info_record['state'] = 6;
									$info_record['state_many'] = 0;
									$info_record['type'] = '+';
									$info_record['msg'] = "拼团已满订单取消退回鼓励金";
									db("record_collage")->insert($info_record);
									db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
								} else {
									db("order_collage_item")->where("id",$info["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_err_code_des"=>'拼团已满订单取消退回鼓励金失败']);
								}
							}
                        } else {
                            //此团未开团正常走流程
                            if (!$info_collage["collage_state"] && $info_collage["collage_win"] == 1 && $info_collage["collage_number"] >= 3) {
                                db("order_collage")->where("id",$info["pid"])->update(["collage_state"=>1,"open_time"=>date("Y-m-d H:i:s")]);
                                $info_content = db("content")->field("stock,collage_money_a,collage_money_c,tuanzhang_money,shopuser_id,title,cbmoney")->where("id",$info_collage['goods_id'])->find();
                                $lists = db("order_collage_item")->field("id,pid,openid,orderNo,gg_money,number,transaction_id,collage_win,collage_error,collage_state,collage_red_bag,paytype,collage_red_bag_jiu,collage_red_bag_xin,goodsid")->where(["pid"=>$info["pid"],"collage_state"=>1])->order("collage_win desc, pay_time asc")->select();
                                $number = 0;
                                foreach ($lists as $v) {
                                    if ($info_content["stock"]) {
                                        $info_member = db("member")->field("fx_top_a,collage_count,collage_success,collage_error,collage_count_xin,collage_count_xin_xin")->where("openid",$v["openid"])->find();
                                        $arr_a = $info_member["collage_count"] ? string2array($info_member["collage_count"]) : [];
                                        $arr_b = $info_member["collage_success"] ? string2array($info_member["collage_success"]) : [];
                                        $count = $arr_a[$info_collage["goods_id"]] ?? 0;
                                        $success = $arr_b[$info_collage["goods_id"]] ?? 0;
                                        $count = $count + 1;
                                        // 订单状态为已支付
                                        if ($v["collage_state"] == 1) {
                                            // 订单为多余订单
                                            if ($number > 3) {
												if($v['paytype'] == 'weixin'){
													$res = $this->collage_refund($v["gg_money"],$v["transaction_id"],1);
													if ($res["return_code"] == "SUCCESS" && $res["result_code"] == "SUCCESS") {
														db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
													} else {
														if (isset($res["err_code_des"])) db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_err_code_des"=>$res["err_code_des"]]);
													}
												}elseif($v['paytype'] == 'yue'){
													$refund_no = orderNo();
													$refund_id ='666680'.time();
													$res = $this->collage_refundyue($v['openid'],$v["gg_money"]);
													if ($res["code"] == "0000") {
														$info_record = [];
														$info_record['openid'] = $v['openid'];
														$info_record['orderNo'] = $v['orderNo'];
														$info_record['money'] = $v["gg_money"];
														$info_record['state'] = 6;
														$info_record['state_many'] = 0;
														$info_record['type'] = '+';
														$info_record['msg'] = "拼团已满订单取消退回鼓励金";
														db("record_collage")->insert($info_record);
														db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
													} else {
														db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_err_code_des"=>'拼团已满订单取消退回鼓励金失败']);
													}
												}
                                            } else {
                                                // 订单为成功订单
                                                if ($v["collage_win"]) {
                                                    db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>2, "finish_time"=>date("Y-m-d H:i:s")]);
        
                                                    // 增加销量、减少库存
                                                    db("content")->where("id",$info_collage['goods_id'])->setInc("sales", 1);
                                                    db("content")->where("id",$info_collage['goods_id'])->setDec("stock", 1);
/*         
													$arr_xin = $info_member["collage_count_xin"] ? string2array($info_member["collage_count_xin"]) : [];
													$xin = $arr_xin[$info_collage["goods_id"]] ?? 0;
													if($count == 1 || $count == 5){
														if($xin == 0){ 
															$arr_xinx[$info_collage["goods_id"]] = 1;
															$arr_xinx = array2string($arr_xinx);
															db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx]);
														}
													}
													
													if($xin == 1){
														$arr_a[$info_collage["goods_id"]] = $count == 4 ? 0 : $count;
														$arr_b[$info_collage["goods_id"]] = $count == 4 ? 0 : 1;
														$str_a = array2string($arr_a);
														$str_b = array2string($arr_b);
														db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b]);
														
													}else{
														$arr_a[$info_collage["goods_id"]] = $count == 5 ? 0 : $count;
														$arr_b[$info_collage["goods_id"]] = $count == 5 ? 0 : 1;
														$str_a = array2string($arr_a);
														$str_b = array2string($arr_b);
														db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b]);
													}
													 */
													
													$arr_xin = $info_member["collage_count_xin"] ? string2array($info_member["collage_count_xin"]) : [];
													$arr_xin_xin = $info_member["collage_count_xin_xin"] ? string2array($info_member["collage_count_xin_xin"]) : [];
													$xin_xin = $arr_xin_xin[$info_collage["goods_id"]] ?? 0;
													$xin = $arr_xin[$info_collage["goods_id"]] ?? 0;

													/*

													判断 是不是最新？是的话走新的 不是走老的？
													
													新的 更新 字段 计数?
													
													旧的先继续走 
													往下

													判断当前是第几单  1 4 5
													如果是第一单 直接
													
													*/
													
													if($count == 1 || $count == 5 || $count == 4){
														
														
														/*
														xin 0 那么就是五单的数据？
														xin 1 那么就是四单的？
														xin_xin 1的话就是 新的 2单的？
														
														等于5的时候 直接更新为新？？
														
														等于4 的时候 判断xin是否为1 是的话 不变 不是更新 CCX
														
														等于1的时候 判断?
														
														*/
														
														if($count == 5 && $xin == 0){
															$arr_xinx[$info_collage["goods_id"]] = 1;
															$arr_xinx = array2string($arr_xinx);
															db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx,"collage_count_xin_xin"=>$arr_xinx]);
														}
														
														if($count == 4 && $xin == 1){
															$arr_xinxx[$info_collage["goods_id"]] = 1;
															$arr_xinxx = array2string($arr_xinxx);
															db("member")->where("openid",$v["openid"])->update(["collage_count_xin_xin"=>$arr_xinxx]);
														}
														
														if($count == 1 && $xin == 0 && $xin_xin == 0 ){
															$arr_xinx[$info_collage["goods_id"]] = 1;
															$arr_xinx = array2string($arr_xinx);
															db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx,"collage_count_xin_xin"=>$arr_xinx]);
															
															
														}
																												if($count == 1 && $xin == 1 && $xin_xin == 0 ){
															$arr_xinx[$info_collage["goods_id"]] = 1;
															$arr_xinx = array2string($arr_xinx);
															db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx,"collage_count_xin_xin"=>$arr_xinx]);
														}	
														
													}
														
														
													
													if($xin_xin == 1){
															$arr_a[$info_collage["goods_id"]] = $count == 2 ? 0 : $count;
															$arr_b[$info_collage["goods_id"]] = $count == 2 ? 0 : 1;
															$str_a = array2string($arr_a);
															$str_b = array2string($arr_b);
															db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b]);
														
													}else{
														
														if($xin == 1){
															$arr_a[$info_collage["goods_id"]] = $count == 4 ? 0 : $count;
															$arr_b[$info_collage["goods_id"]] = $count == 0;
															$str_a = array2string($arr_a);
															$str_b = array2string($arr_b);
															db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b]);
															
														}else{
															$arr_a[$info_collage["goods_id"]] = $count == 5 ? 0 : $count;
															$arr_b[$info_collage["goods_id"]] = 0;
															$str_a = array2string($arr_a);
															$str_b = array2string($arr_b);
															db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b]);
														}
													}
													////////////////
													
													
        
                                                    // 拼团成功 返现开始 有上级
                                                    if ($info_content["collage_money_a"] && $info_member["fx_top_a"]) {
                                                        $fx_openid = db("member")->where("id",$info_member["fx_top_a"])->find();
                                                        if ($fx_openid['openid']) {
                                                            $info_record = [];
                                                            $info_record['openid'] = $fx_openid['openid'];
                                                            $info_record['orderNo'] = $v["orderNo"];
                                                            $info_record['money'] = $info_content["collage_money_a"];
                                                            $info_record['state'] = 2;
                                                            $info_record['type'] = '+';
                                                            $info_record['msg'] = "拼团成功推广金";
                                                            db("record_collage")->insert($info_record);
                                                            db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["collage_money_a"] * 100);
                                                            db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["collage_money_a"]);
                                                        }
													//level == 1 此人为团长
														if($fx_openid['level'] == 1){
															$info_record = [];
															$info_record['openid'] = $fx_openid['openid'];
															$info_record['orderNo'] = $v["orderNo"];
															$info_record['money'] = $info_content["tuanzhang_money"];
															$info_record['state'] = 9;
															$info_record['type'] = '+';
															$info_record['msg'] = "团长收益";
															db("record_collage")->insert($info_record);
															
															db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["tuanzhang_money"] * 100);
															db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["tuanzhang_money"]);
														}	
														
                                                    }
													
													//给商家分佣 info_content增加字段,shopuser_id,title,cbmoney
													//先获取商家id
													if($info_content['shopuser_id'] != null){
														if($info_content['shopuser_id'] > 0){
															$shopuser=db("member")->field("openid")->where("id",$info_content["shopuser_id"])->find();

															
															$info_record = [];
															$info_record['openid'] = $shopuser['openid'];
															$info_record['orderNo'] = $v["orderNo"];
															$info_record['money'] = $info_content["cbmoney"];
															$info_record['state'] = 1;
															$info_record['type'] = '+';
															$info_record['goods_id'] = $info_collage['goods_id'];
															$info_record['msg'] = "卖出商品".$info_content['title'];
															db("record_business")->insert($info_record);
															db("member")->where("openid",$shopuser['openid'])->setInc("money", $info_content["cbmoney"]);
															
															//record_business
														}
														
													}
													
													
                                                    // 拼团成功 返现结束
                                                }
        
                                                // 订单为失败订单
                                                if (!$v["collage_win"]) {
													
													
													if($v['paytype'] == 'weixin'){
														$res = $this->collage_refund($v["gg_money"],$v["transaction_id"]);
														if ($res["return_code"] == "SUCCESS" && $res["result_code"] == "SUCCESS") {
															$count_a = db("order_collage_item")->where(["pid"=>$v["pid"],"collage_state"=>3])->count("id");
															if ($count_a >= 2) {
																db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
															} else {
																db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>3, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
				
															$arr_xin = $info_member["collage_count_xin"] ? string2array($info_member["collage_count_xin"]) : [];
																$xin = $arr_xin[$info_collage["goods_id"]] ?? 0;
																if($count == 1 || $count == 5){
																	if($xin == 0){ 
																		$arr_xinx[$info_collage["goods_id"]] = 1;
																		$arr_xinx = array2string($arr_xinx);
																		db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx]);
																	}
																}
																if($xin == 1){
																	$arr_a[$info_collage["goods_id"]] = $count >= 4 ? 0 : $count;
																	$arr_b[$info_collage["goods_id"]] = $count >= 4 ? 0 : $success;
																	
																}else{
													
																	$arr_a[$info_collage["goods_id"]] = $count >= 5 ? 0 : $count;
																	$arr_b[$info_collage["goods_id"]] = $count >= 5 ? 0 : $success;
																}

																$arr_c = $info_member["collage_error"] ? string2array($info_member["collage_error"]) : [];
																if (isset($arr_c[$info_collage["goods_id"]]) && $arr_c[$info_collage["goods_id"]] > 0) {
																	$arr_a[$info_collage["goods_id"]] = $arr_a[$info_collage["goods_id"]] - 1;
																	$arr_c[$info_collage["goods_id"]] = $arr_c[$info_collage["goods_id"]] - 1;
																}
					
																$str_a = array2string($arr_a);
																$str_b = array2string($arr_b);
																$str_c = array2string($arr_c);
					
																db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b, "collage_error"=>$str_c]);
															
				
																// 拼团失败 返现开始 有上级
																if ($info_content["collage_money_c"] && $info_member["fx_top_a"]) {
																	$fx_openid = db("member")->where("id",$info_member["fx_top_a"])->find();
																	if ($fx_openid['openid']) {
																		$info_record = [];
																		$info_record['openid'] = $fx_openid['openid'];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $info_content["collage_money_c"];
																		$info_record['state'] = 3;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "拼团失败推广金";
																		db("record_collage")->insert($info_record);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["collage_money_c"] * 100);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["collage_money_c"]);
																	}
																	
																	//level == 1 此人为团长
																	if($fx_openid['level'] == 1){
																		$info_record = [];
																		$info_record['openid'] = $fx_openid['openid'];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $info_content["tuanzhang_money"];
																		$info_record['state'] = 9;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "团长收益";
																		db("record_collage")->insert($info_record);
																		
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["tuanzhang_money"] * 100);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["tuanzhang_money"]);
																	}	
														
																}
																// 拼团失败 返现结束
				
																// 发红包 start
																if ($v["collage_red_bag"]) {
																																
																	$info_members = db("member")->field("collage_count_xin,collage_count_xin_xin")->where("openid",$v["openid"])->find();
																	$arr_xins = $info_members["collage_count_xin"] ? string2array($info_members["collage_count_xin"]) : [];
																	$xins = $arr_xins[$info_collage["goods_id"]] ?? 0;
																	$arr_xins_xin = $info_members["collage_count_xin_xin"] ? string2array($info_members["collage_count_xin_xin"]) : [];
																	$xins_xin = $arr_xins_xin[$info_collage["goods_id"]] ?? 0;
																	
																	
																	if($xins_xin == 1){
																		$collage_red_bag = $v["collage_red_bag_xin"];
																	}else{
																		
																		if($xins == 1){
																			$cishu =  5 - $count;
																			$collage_red_bag = $v["collage_red_bag"] * $cishu;
																			
																			$arr_as[$info_collage["goods_id"]] = 0;
																			$str_as = array2string($arr_as);
																			db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_as]);
																		}else{
																			//$collage_red_bag = $v["collage_red_bag_jiu"];
																			$cishu =  6 - $count;
																			$collage_red_bag = $v["collage_red_bag_jiu"] * $cishu;
																			$arr_as[$info_collage["goods_id"]] = 0;
																			$str_as = array2string($arr_as);
																			db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_as]);
																		}
																		
																	}
																	$money = db("collage_many_money")->where("openid",$v["openid"])->value("money");
				
																	$many = 0;
																	$reduce = 0;
																	// if ($money) {
																	//     $many = 1;
																	//     $reduce = min($money, $collage_red_bag / 4);
																	//     $collage_red_bag = $v["collage_red_bag"] - $reduce;
																	// }
				
																	if($xins_xin == 1){
																		
																		$chenggsss = db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"goodsid"=>$v['goodsid']])->order("id desc")->find();
																		$tuikuanaaaa = $this->collage_refund($chenggsss["gg_money"],$chenggsss["transaction_id"]);
																		
																																				db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"id"=>$chenggsss['id']])->update(["is_cg_tui"=>1]);

																		
																			$info_recordsssc = [];
																			$info_recordsssc['openid'] = $v["openid"];
																			$info_recordsssc['orderNo'] = $chenggsss["orderNo"];
																			$info_recordsssc['money'] = $chenggsss["gg_money"];
																			$info_recordsssc['state'] = 1;
																			$info_recordsssc['state_many'] = $many;
																			$info_recordsssc['type'] = '+';
																			$info_recordsssc['msg'] = "拼团失败，成功订单直接退款";
																			db("record_collage_yltui")->insert($info_recordsssc);
																			

																	}else{
																		
																		$info_record = [];
																		$info_record['openid'] = $v["openid"];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $collage_red_bag;
																		$info_record['state'] = 4;
																		$info_record['state_many'] = $many;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "拼团失败鼓励金";
																		db("record_collage")->insert($info_record);
					
																		db("member")->where("openid",$v["openid"])->setInc("collage_money",$collage_red_bag * 100);
																		db("member")->where("openid",$v["openid"])->setInc("collage_money_a",$collage_red_bag);
																		db("order_collage_item")->where("id",$v["id"])->setField("collage_many",$many);
																		db("collage_many_money")->where("openid",$v["openid"])->setDec("money",$reduce);
																		db("collage_many_money")->where("openid",$v["openid"])->setInc("money_reduce",$reduce);
																			
																		
																	}
																}
																// 发红包 end
															}
														} else {
															if (isset($res["err_code_des"])) {
																db("order_collage_item")->where("id",$v["id"])->update(["refund_no"=>$res["refund_no"], "refund_err_code_des"=>$res["err_code_des"]]);
															}
														}
													}elseif($v['paytype'] == 'yue'){
														
														
														$refund_no = orderNo();
														$refund_id ='666680'.time();
														$res = $this->collage_refundyue($v['openid'],$v["gg_money"]);
														if ($res["code"] == "0000") {
															
																$info_record = [];
																$info_record['openid'] = $v['openid'];
																$info_record['orderNo'] = $v['orderNo'];
																$info_record['money'] = $v["gg_money"];
																$info_record['state'] = 7;
																$info_record['state_many'] = 0;
																$info_record['type'] = '+';
																$info_record['msg'] = "拼团失败退还支付鼓励金";
																db("record_collage")->insert($info_record);
															
															$count_a = db("order_collage_item")->where(["pid"=>$v["pid"],"collage_state"=>3])->count("id");
															if ($count_a >= 2) {
																db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>6, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
															} else {
																db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>3, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
				
															$arr_xin = $info_member["collage_count_xin"] ? string2array($info_member["collage_count_xin"]) : [];
																$xin = $arr_xin[$info_collage["goods_id"]] ?? 0;
																if($count == 1 || $count == 5){
																	if($xin == 0){ 
																		$arr_xinx[$info_collage["goods_id"]] = 1;
																		$arr_xinx = array2string($arr_xinx);
																		db("member")->where("openid",$v["openid"])->update(["collage_count_xin"=>$arr_xinx]);
																	}
																}
																if($xin == 1){
																	$arr_a[$info_collage["goods_id"]] = $count >= 4 ? 0 : $count;
																	$arr_b[$info_collage["goods_id"]] = $count >= 4 ? 0 : $success;
																	
																}else{
													
																	$arr_a[$info_collage["goods_id"]] = $count >= 5 ? 0 : $count;
																	$arr_b[$info_collage["goods_id"]] = $count >= 5 ? 0 : $success;
																}

																$arr_c = $info_member["collage_error"] ? string2array($info_member["collage_error"]) : [];
																if (isset($arr_c[$info_collage["goods_id"]]) && $arr_c[$info_collage["goods_id"]] > 0) {
																	$arr_a[$info_collage["goods_id"]] = $arr_a[$info_collage["goods_id"]] - 1;
																	$arr_c[$info_collage["goods_id"]] = $arr_c[$info_collage["goods_id"]] - 1;
																}
					
																$str_a = array2string($arr_a);
																$str_b = array2string($arr_b);
																$str_c = array2string($arr_c);
					
																db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_a, "collage_success"=>$str_b, "collage_error"=>$str_c]);
															
				
																// 拼团失败 返现开始 有上级
																if ($info_content["collage_money_c"] && $info_member["fx_top_a"]) {
																	$fx_openid = db("member")->where("id",$info_member["fx_top_a"])->find();
																	if ($fx_openid['openid']) {
																		$info_record = [];
																		$info_record['openid'] = $fx_openid['openid'];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $info_content["collage_money_c"];
																		$info_record['state'] = 3;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "拼团失败推广金";
																		db("record_collage")->insert($info_record);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["collage_money_c"] * 100);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["collage_money_c"]);
																	}
																	
																	//level == 1 此人为团长
																	if($fx_openid['level'] == 1){
																		$info_record = [];
																		$info_record['openid'] = $fx_openid['openid'];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $info_content["tuanzhang_money"];
																		$info_record['state'] = 9;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "团长收益";
																		db("record_collage")->insert($info_record);
																		
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money", $info_content["tuanzhang_money"] * 100);
																		db("member")->where("openid",$fx_openid['openid'])->setInc("collage_money_a", $info_content["tuanzhang_money"]);
																	}	
																}
																// 拼团失败 返现结束
				
																// 发红包 start
																if ($v["collage_red_bag"]) {
																	$info_members = db("member")->field("collage_count_xin,collage_count_xin_xin")->where("openid",$v["openid"])->find();
																	$arr_xins = $info_members["collage_count_xin"] ? string2array($info_members["collage_count_xin"]) : [];
																	$xins = $arr_xins[$info_collage["goods_id"]] ?? 0;
																	$arr_xins_xin = $info_members["collage_count_xin_xin"] ? string2array($info_members["collage_count_xin_xin"]) : [];
																	$xins_xin = $arr_xins_xin[$info_collage["goods_id"]] ?? 0;
																	
																	
																	if($xins_xin == 1){
																		$collage_red_bag = $v["collage_red_bag_xin"];
																	}else{
																		
																		if($xins == 1){
																			$cishu =  5 - $count;
																			$collage_red_bag = $v["collage_red_bag"] * $cishu;
																			
																			$arr_as[$info_collage["goods_id"]] = 0;
																			$str_as = array2string($arr_as);
																			db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_as]);
																		}else{
																			//$collage_red_bag = $v["collage_red_bag_jiu"];
																			$cishu =  6 - $count;
																			$collage_red_bag = $v["collage_red_bag_jiu"] * $cishu;
																			$arr_as[$info_collage["goods_id"]] = 0;
																			$str_as = array2string($arr_as);
																			db("member")->where("openid",$v["openid"])->update(["collage_count"=>$str_as]);
																		}
																		
																	}
																	$money = db("collage_many_money")->where("openid",$v["openid"])->value("money");
				
																	$many = 0;
																	$reduce = 0;
																	// if ($money) {
																	//     $many = 1;
																	//     $reduce = min($money, $collage_red_bag / 4);
																	//     $collage_red_bag = $v["collage_red_bag"] - $reduce;
																	// }
				
																	if($xins_xin == 1){
																		
																		$chenggsss = db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"goodsid"=>$v['goodsid']])->order("id desc")->find();
																		$tuikuanaaaa = $this->collage_refund($chenggsss["gg_money"],$chenggsss["transaction_id"]);
																		
																																				db("order_collage_item")->where(["openid"=>$v["openid"],"collage_state"=>2,"id"=>$chenggsss['id']])->update(["is_cg_tui"=>1]);

																		
																			$info_recordsssc = [];
																			$info_recordsssc['openid'] = $v["openid"];
																			$info_recordsssc['orderNo'] = $chenggsss["orderNo"];
																			$info_recordsssc['money'] = $chenggsss["gg_money"];
																			$info_recordsssc['state'] = 1;
																			$info_recordsssc['state_many'] = $many;
																			$info_recordsssc['type'] = '+';
																			$info_recordsssc['msg'] = "拼团失败，成功订单直接退款";
																			db("record_collage_yltui")->insert($info_recordsssc);
																			

																	}else{
																		
																		$info_record = [];
																		$info_record['openid'] = $v["openid"];
																		$info_record['orderNo'] = $v["orderNo"];
																		$info_record['money'] = $collage_red_bag;
																		$info_record['state'] = 4;
																		$info_record['state_many'] = $many;
																		$info_record['type'] = '+';
																		$info_record['msg'] = "拼团失败鼓励金";
																		db("record_collage")->insert($info_record);
					
																		db("member")->where("openid",$v["openid"])->setInc("collage_money",$collage_red_bag * 100);
																		db("member")->where("openid",$v["openid"])->setInc("collage_money_a",$collage_red_bag);
																		db("order_collage_item")->where("id",$v["id"])->setField("collage_many",$many);
																		db("collage_many_money")->where("openid",$v["openid"])->setDec("money",$reduce);
																		db("collage_many_money")->where("openid",$v["openid"])->setInc("money_reduce",$reduce);
																			
																		
																	}
																}
																// 发红包 end
															}
														} else {
															db("order_collage_item")->where("id",$v["id"])->update(["refund_no"=>$refund_no, "refund_err_code_des"=>'拼团失败逻辑处理失败']);
														}
													}			
										
                                                }
                                            }
    
                                            $number++;
                                        }
                                    } else {
										if($v['paytype'] == 'weixin'){
											$res = $this->collage_refund($v["gg_money"],$v["transaction_id"],1);
											if ($res["return_code"] == "SUCCESS" && $res["result_code"] == "SUCCESS") {
												db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_no"=>$res["refund_no"], "refund_id"=>$res["refund_id"]]);
											} else {
												if (isset($res["err_code_des"])) db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_err_code_des"=>$res["err_code_des"]]);
											}
										}elseif($v['paytype'] == 'yue'){
											$refund_no = orderNo();
											$refund_id ='666680'.time();
											$res = $this->collage_refundyue($v['openid'],$v["gg_money"]);
											if ($res["code"] == "0000") {
												$info_record = [];
												$info_record['openid'] = $v['openid'];
												$info_record['orderNo'] = $v['orderNo'];
												$info_record['money'] = $v["gg_money"];
												$info_record['state'] = 8;
												$info_record['state_many'] = 0;
												$info_record['type'] = '+';
												$info_record['msg'] = "库存不足订单取消退回鼓励金";
												db("record_collage")->insert($info_record);
												db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_no"=>$refund_no, "refund_id"=>$refund_id]);
											} else {
												db("order_collage_item")->where("id",$v["id"])->update(["collage_state"=>5, "refund_no"=>$refund_no, "refund_err_code_des"=>'库存不足订单取消退回鼓励金失败']);
											}
										}										
                                    }
    
                                    usleep(mt_rand(500000, 1500000));
                                }
                                
                            }
                        }
                    }
					$str = 'success';
                }else{
					$str = 'fail';

				}	
		return $str;
	}
	
} 