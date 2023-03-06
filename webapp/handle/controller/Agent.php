<?php

namespace app\handle\controller;
use think\Controller;
use think\Request;
use think\Loader;
use think\db\Query;
use index\Temp;

class Agent extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function pay ()
    {
        $openid = input("openid");

        $orderNo = orderNo();
        $money = db("agent_setup")->where("id",1)->value("money_a");

        $jsApiParameters = wxpay($openid, '成为黄金会员', $orderNo, $money*100, "agent");

        $data = array();
        $data['money'] = $money;
        $data['orderNo'] = $orderNo;
        $data['data'] = $jsApiParameters;

        return json_encode($data);
    }

    public function count ()
    {
        $openid = input("openid");

        $userid = db("member")->where("openid",$openid)->value("id");

        $count_1 = db("member")->where(["fx_top_a|fx_top_b"=>$userid, "agent_level"=>1])->count("id");
        $count_2 = db("member")->where(["fx_top_a|fx_top_b"=>$userid, "agent_level"=>2])->count("id");
        $count_3 = db("member")->where(["fx_top_a|fx_top_b"=>$userid, "agent_level"=>3])->count("id");

        return json_encode(["count_1"=>$count_1,"count_2"=>$count_2,"count_3"=>$count_3]);
    }
    public function lists ()
    {
        $state = input("state");
        $openid = input("openid");

        $userid = db("member")->where("openid",$openid)->value("id");

        $arr = db("member")->field("id,nickname,agent_time")->where(["fx_top_a|fx_top_b"=>$userid, "agent_level"=>$state])->select();

        return json_encode($arr);
    }

    public function record ()
    {
        $state = input("state");
        $openid = input("openid");

        $where = ["openid"=>$openid];
        switch ($state) {
            case 1:$where["state"] = ["in","7,8"];
            case 2:$where["state"] = ["in","9,10"];
        }

        $arr = db("record_pay")->field("id, orderId, money, intime")->where($where)->select();
        if($arr) {
            foreach ($arr as $k => $v) {
                if($state = 2 && $v["orderId"]) {
                    $data = db("order")->field("title, ggtitle, ggmoney, number")->where("id",$v["orderId"])->find();
                    if($data) $arr[$k]["data"] = $data;
                }

                $arr[$k]["money"] = xiaoshu($v["money"]);
                $arr[$k]["ctime"] = date("Y-m-d H:i:s",$v["intime"]);
            }
        }

        return json_encode($arr);
    }
}