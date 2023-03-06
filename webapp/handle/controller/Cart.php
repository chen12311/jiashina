<?php

namespace app\handle\controller;
use think\Controller;
use think\Request;
use think\Loader;
use think\db\Query;

class Cart extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 购物车信息
     */
    public function index()
    {
        $openid = input("openid");

        $money = 0;
        $lists = db("order_cart")->where("openid",$openid)->order("id desc")->select();
        foreach($lists as $k => $v){
            $lists[$k]['ggmoney'] = xiaoshu($v['ggmoney']);
            $money = $money + $v['ggmoney'] * $v['number'];
        }

        $shopLists = db("position")->field("`cid`")->where(["state"=>2])->order("id desc")->limit(4)->select();
        foreach($shopLists as $k => $v){
            $contentData = getData("content",['id'=>$v["cid"]]);
            $contentData['money'] = xiaoshu($contentData['money']);
            $contentData['ymoney'] = xiaoshu($contentData['ymoney']);
            $shopLists[$k] = $contentData;
        }

        $data = array();
        $data['lists'] = $lists;
        $data['money'] = xiaoshu($money);
        $data['shopLists'] = $shopLists;

        return json_encode($data);
    }

    /**
     * 购买数量修改
     */
    public function edit()
    {
        $id = input("id");
        $number = input("number");

        db("order_cart")->where("id",$id)->update(['number'=>$number]);

        return 'succ';
    }

    /**
     * 购物车删除
     */
    public function cart_del()
    {
        $id = input("id");

        db("order_cart")->where("id","in",$id)->delete();

        return 'succ';
    }

    //商城购物车-结算生成订单
    public function cart2order()
    {
        $id = input("id");
        $three_fanxian_id = input("three_fanxian_id");
        $orderNo = orderNo();

        $lists = db("order_cart")->where("id","in",$id)->select();
        foreach($lists as $v){
            $info = $v;
            $info['orderNo'] = orderNo();
            $info['out_trade_no'] = $orderNo;
            $info['state'] = 0;
            $info['intime'] = time();
            if($v['status'] == 1){
                $info['cid'] = 5;
                $info['goods_id'] = $v['cid'];
            }
            unset($info['id']);
            $id = db("order")->insertGetId($info);
            db("order_cart")->delete($v);

            //2021-02-05 新增 start
            /**
             * 用户分享商品
             * 分享用户已购买并且没有被返现
             * 被分享用户有三人从分享页面购买商品
             * 全额返现分享人购买商品的金额
             * 添加购买记录，有$three_fanxian_id添加分享记录
             */
            $arr = [];
            $arr['id_shop'] = $v['cid'];
            $arr['id_order'] = $id;
            $arr['openid'] = $v['openid'];
            $arr['money'] = $v['ggmoney']*$v['number'];
            $arr['number'] = 0;
            $arr['state'] = 0;
            $arr['ctime'] = time();
            db("z_order_three_fanxian")->insert($arr);

            if($three_fanxian_id){
                $three_fanxian_info = db("z_order_three_fanxian")->where("id",$three_fanxian_id)->find();
                if($three_fanxian_info && $three_fanxian_info['state'] == 0) {
                    $arr = [];
                    $arr['openid'] = $three_fanxian_info['openid'];
                    $arr['openid'] = $v['openid'];
                    $arr['id_shop'] = $v['cid'];
                    $arr['id_order'] = $id;
                    $arr['id_fanxian'] = $three_fanxian_id;
                    $arr['state'] = 0;
                    $arr['ctime'] = time();
                    db("z_order_three_fanxian_fenxiang")->insert($arr);
                }
            }
            //2021-02-05 新增 end
        }

        return $orderNo;
    }
}