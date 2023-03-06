<?php

namespace app\handle\controller;
use think\Controller;
use think\Request;
use think\Loader;
use think\db\Query;
use index\Temp;


class Pay extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 商品购买
     */
    public function index(){
        $openid = input("openid");
        $orderNo = input("orderNo");
        $status = input("status") ? input("status") : 0;


        if ($openid == "oXgpTt727lSF2pPbMFzWduYppfUg" || $openid == "oXgpTt4Y-t0HBocYfhxits0F8A74" || $openid == "oXgpTt3-vWcqVBvnBWvlmaaJ6yAA") {

            if($status == 4) {
                $arr = db("order")->field("ggmoney*number as money")->where("out_trade_no", $orderNo)->find();
                $money = $arr["money"];
            } else {
                $lists = db("order")->where("out_trade_no", $orderNo)->select();
                $money = 0;
                foreach ($lists as $k => $v) {
                    $kdmoney = $v['kdmoney'];
                    $money = $money + $v['ggmoney'] * $v['number'];
                }
                $money = $money + $kdmoney;
            }
    
    
    //        if ($openid == "oXgpTt727lSF2pPbMFzWduYppfUg") $money = 1;
            // $money = 1;
          
    
    	    $member = db("member")->where("openid",$openid)->find();
    		
    	//	if($member['collage_money']>$money){
    		//	$jsApiParameters = yuepay($openid, '购买商品', $orderNo, $money, $status == 4 ? "collage" : "");
    		//	$type='yue';
    		//}else{
    			$jsApiParameters = wxpay($openid, '购买商品', $orderNo, $money, $status == 4 ? "collage" : "");
    			//$type='weixin';
    		//}
    
    
    
            $this -> assign('money', $money);
            $this -> assign('orderNo', $orderNo);
            $this -> assign('data', $jsApiParameters);
    
            $data = array();
            $data['money'] = $money;
            $data['orderNo'] = $orderNo;
            $data['data'] = $jsApiParameters;
    		$data['type'] = $type;
    
            return json_encode($data);
        } else {
            return "";
        }
    }

    /**
     * 支付成功
     */
    public function succ()
    {
        $orderNo = input("orderNo");

        $lists = db("order")->where("out_trade_no",$orderNo)->select();
        $money = 0;
        foreach($lists as $k => $v){
            $name = $v['name'];
            $phone = $v['phone'];
            $address = $v['address'];
            $kdmoney = $v['kdmoney'];
            $money = $money + $v['ggmoney'] * $v['number'];
        }
        $money = $money + $kdmoney;

        $data = array();
        $data['money'] = xiaoshu($money);
        $data['name'] = $name;
        $data['phone'] = $phone;
        $data['address'] = $address;

        return json_encode($data);
    }
}