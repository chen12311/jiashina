<?php

namespace app\handle\controller;
use think\Controller;

use think\Request;

use think\Loader;

use think\db\Query;

class Index extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function jsConfig()
    {
        $url = input("url");
        $config = wx_js_config($url);

        return json_encode($config);
    }

    /**
     * 获取会员信息
     */
    public function register()
    {
        $url = input("url");
        $code = input('code');

        if(!$code) {
            $url = get_url();

            return $url;
        }else{
            $res = wechat_member($code);

            $urlArr = explode("?",$url);
            if (count($urlArr) > 1) {
                $url = $url."&openid=$res";
            } else {
                $url = $url."?openid=$res";
            }

            return '<script>location.href = "'.$url.'"</script>';
        }
    }

    /**
     * 上级会员
     */
    public function fenxiao($user_id = '', $openid = '')
    {
    	if($openid && $user_id){
	        $member = db("member")->field("id, fx_top_a")->where("openid",$openid)->find();
            $arr = db("member")->field("fx_top_a,fx_top_b")->where("id",$user_id)->find();

	        if(!$member['fx_top_a'] && $member['id'] != $user_id && !in_array($member["id"],$arr)){
	            $info = array();
	            $info['fx_top_a'] = $user_id;
	            $info['fx_top_b'] = $arr["fx_top_a"];
	            db("member")->where("openid",$openid)->update($info);
	        }
	    }
    }

    /**
     * 首页
     */
    public function index()
    {
        $id = input("id") ? input("id") : 0;
        $user_id = input("user_id");
        $openid = input("openid");

        if($user_id){
        	$this->fenxiao($user_id,$openid);
        }

        $navListA = array(['id'=>0,'id'=>0,'level'=>1,'name'=>'推荐']);
        $navListB = db("category")->where("pid",0)->order("id asc")->select();
        $navList = array_merge($navListA,$navListB);

        $bannerList = db("fragment")->where(['catid'=>$id,'state'=>1])->order("id desc")->select();

        $imgA = db("fragment")->where(['catid'=>$id,'state'=>2])->order("id desc")->find();
        $imgB = db("fragment")->where(['catid'=>$id,'state'=>3])->order("id desc")->find();
        $imgC = db("fragment")->where(['catid'=>$id,'state'=>4])->order("id desc")->find();
        $imgD = db("fragment")->where(['catid'=>$id,'state'=>5])->order("id desc")->find();

        if($id){
            $where = "`zcatid` = $id && (`order_type` = 0 || `order_type` = 2)";
            $where = ["zcatid"=>$id, "order_type"=>["in","0,2"], "collage"=>1];
            $lists = db("content")->where($where)->where("status",1)->order("id desc")->limit(10)->select();

            foreach ($lists as $k => $v) {
                $lists[$k]['money'] = xiaoshu($v['money']);
                $lists[$k]['ymoney'] = xiaoshu($v['ymoney']);
            }
            $arra = $lists;
        }else {
            $lists = db("position")->field("`cid`")->where("state",1)->order("id desc")->limit(10)->select();
            $arr = [];$i = 0;
            foreach ($lists as $k => $v) {
                $contentData = getData("content", ['id' => $v["cid"]]);
//                if ($contentData["stock"] > 0) {
                    $contentData['money'] = xiaoshu($contentData['money']);
                    $contentData['ymoney'] = xiaoshu($contentData['ymoney']);

                    if ($contentData['order_type'] == 0 || $contentData['order_type'] = 2) {
                        $arr[$i] = $contentData;
                        $i++;
//                    }
                }
            }

            $where = '`order_type` = 0 || `order_type` = 2';
            $where = ["order_type"=>["in","0,2"], "collage"=>1];
          //  $lists = db("content")->where($where)->where('status',1)->select();
            $lists = db("content")->where($where)->where("status",1)->select();
            foreach ($lists as $k => $v) {
                $lists[$k]['money'] = xiaoshu($v['money']);
                $lists[$k]['ymoney'] = xiaoshu($v['ymoney']);
            }

            $arra = array_merge($arr,$lists);
        }

        $category_banner = db("category_new")->limit("10")->order("id asc")->select();

        $lists = db("position")->field("`cid`")->where(["state"=>3])->order("id desc")->limit(4)->select();
        $shop_position_lists = [];$i = 0;
        foreach ($lists as $k => $v) {
            $contentData = getData("content", ['id' => $v["cid"]]);
//            if ($contentData["stock"] > 0) {
                $contentData['money'] = xiaoshu($contentData['money']);
                $contentData['ymoney'] = xiaoshu($contentData['ymoney']);

                $shop_position_lists[$i] = $contentData;
                $i++;
//            }
        }

        $data = array();
        $data['id'] = $id;
        $data['navList'] = $navList;
        $data['bannerList'] = $bannerList;
        $data['imgA'] = $imgA;
        $data['imgB'] = $imgB;
        $data['imgC'] = $imgC;
        $data['imgD'] = $imgD;
        $data['list'] = $arra;

        $data['category_banner'] = $category_banner;
        $data['shop_position_lists'] = $shop_position_lists;

        return json_encode($data);
    }

    /**
     * 分类
     */
    public function category()
    {
        $id = input("id") ? input("id") : 0;

        $navList = db("category")->where("pid",0)->order("id asc")->select();
        if($id == 0){
            $id = $navList[0]['id'];
        }

        $lists = db("category")->where("pid",$id)->order("id asc")->select();

        foreach($lists as $k => $v){
            $array = db("category")->where("pid",$v['id'])->order("id asc")->select();
            $lists[$k]['lists'] = $array;
        }

        $data = array();
        $data['id'] = $id;
        $data['left'] = $navList;
        $data['right'] = $lists;

        return json_encode($data);
    }

    /**
     * 列表
     */
    public function lists()
    {
        $id = input("id");
        $type = input("type") ? input("type") : 0;
        $state = input("state") ? input("state") : 0;
        $limit = input("limit") ? input("limit") : 0;
        $shop_id = input("shop_id") ? input("shop_id") : '';
        $collage = input("collage") ? input("collage") : 0;
        $p = input("p");

        $order = '';
        switch ($state){
            case 0:$order = 'id asc';break;
            case 1:$order = 'sales desc';break;
            case 2:$order = 'id desc';default:break;
        }

        $where = array();
//        $where["stock"] = [">", 0];
        $where['order_type'] = $type;
        $where['collage'] = $collage;

        if($p){
            $p = urldecode($p);
            $where['title'] = ['LIKE','%'.$p.'%'];
        }else{
            $where['catid'] = $id;
        }

        if($collage) {
            $id_arr = db("category")->field("id")->where('pid',$id)->select();
            $id_str = implode(",",array_column($id_arr,"id"));

            $id_arr = db("category")->field("id")->where('pid','in',$id_str)->select();
            $id_str = implode(",",array_column($id_arr,"id"));

            $where['catid'] = ['in',$id_str];
        }
        if($shop_id) $where['catid'] = $shop_id;

		//$where['status']=1;
        if($collage) $lists = db("content")->where($where)->order($order)->select();
        else $lists = db("content")->where($where)->order($order)->limit($limit,8)->select();

        if($lists) {
            foreach ($lists as $k => $v) {
                $lists[$k]['money'] = xiaoshu($v['money']);
                $lists[$k]['ymoney'] = xiaoshu($v['ymoney']);
            }
        }

        $data = array();
        $data['state'] = $state;
        $data['lists'] = $lists;

        return json_encode($data);
    }

    /**
     * 详情
     */
    public function show()
    {
        $id = input("id");
        $openid = input("openid");

        $data = db("content")->where("id",$id)->find();
        $data['images'] = $data['images'] ? string2array($data['images']) : '';

        $data['money'] = xiaoshu($data['money']);
        $data['ymoney'] = xiaoshu($data['ymoney']);
        $data['like'] = db("like")->where(['cid'=>$id,'openid'=>$openid])->count();

        $array = db("order")->field("`id`,`openid`,`ggtitle`,`starA`,`starB`,`contentPj`,`imgarr`,`pjtime`")->where(["cid"=>$id,"state"=>7,"ggid"=>['>',0]])->order("pjtime desc")->limit(2)->select();
        foreach ($array as $k => $v){
            $member = db("member")->where("openid",$v['openid'])->find();
            $array[$k]['pjtime'] = date("Y-m-d",$v['pjtime']);
            $array[$k]['imgarr'] = string2array($v['imgarr']);
            $array[$k]['usertx'] = $member['usertx'];
            $array[$k]['nickname'] = $member['nickname'];
        }

        $data['pjList'] = $array;
        $data['pjCount'] = db("order")->where(["cid"=>$id,"state"=>7,"ggid"=>['>',0]])->count();

        //2021-02-05 新增 start
        /**
         * 用户分享商品
         * 分享用户已购买并且没有被返现
         * 被分享用户有三人从分享页面购买商品
         * 全额返现分享人购买商品的金额
         * 获取未返现返程购买记录的ID
         */
        $three_fanxian_id = db("z_order_three_fanxian")->where(['id_shop'=>$id,'openid'=>$openid,'state'=>0])->value("id");
        $data['three_fanxian_id'] = $three_fanxian_id ? $three_fanxian_id : '';
        //2021-02-05 新增 end

        //2022-03-17 新增 start
        /**
         * 拼团商品
         * 五人团自动补贴一人，一人中奖，三人退款发红包
         * 十人团自动补贴三人，两人中奖，五人退款发红包
         */
        $array = db("order")->field("openid,collageNo")->where(["status"=>4, "collage"=>1, "cid"=>$id])->select();
        $count = $data["collage_number"] == 5 ? $data["collage_number"] - 1 : $data["collage_number"] - 3;
        $count = $count - count($array);
        $collageNo = $array ? $array[0]["collageNo"] : collageNo();

        $arr = [];
        $arr["my"] = in_array($openid,array_column($array,"openid"));
        $arr["count"] = $count;
        $arr["collageNo"] = $collageNo;
        
        $data['collage_arr'] = $arr;
        //2022-03-17 新增 end
		
		
		if($data['status']==0){
			$data['stock']=0;
		}
        
        return json_encode($data);
    }

    public function pj_lists()
    {
        $id = input("id");

        $lists = db("order")->field("`id`,`openid`,`ggtitle`,`starA`,`starB`,`contentPj`,`imgarr`,`pjtime`")->where(["cid"=>$id,"state"=>7,"ggid"=>['>',0]])->order("pjtime desc")->select();
        foreach ($lists as $k => $v){
            $member = db("member")->where("openid",$v['openid'])->find();

            $lists[$k]['pjtime'] = date("Y-m-d",$v['pjtime']);
            $lists[$k]['imgarr'] = string2array($v['imgarr']);
            $lists[$k]['usertx'] = $member['usertx'];
            $lists[$k]['nickname'] = $member['nickname'];
        }

        return json_encode($lists);
    }

    /**
     * 收藏
     */
    public function like()
    {
        $arr = array();
        $arr['cid'] = input("id");
        $arr['openid'] = input("openid");
        $count = db("like")->where($arr)->count();

        if($count == 0){
            $arr['intime'] = time();
            db("like")->insert($arr);
            $str = 1;
        }else{
            db("like")->where($arr)->delete();
            $str = 0;
        }

        return $str;
    }

    /**
     * 选择规格
     */
    public function show_lists()
    {
        $id = input("id") ? input("id") : 0;
        $lists = db("content_list")->where("zid",$id)->select();
        foreach($lists as $k => $v){
            $lists[$k]['money'] = xiaoshu($v['money']);
        }

        $data = array();
        $data['lists'] = $lists;

        return json_encode($lists);
    }

    /**
     * 拼团
     */
    public function show_collate ()
    {
        $cid = input("cid");
        $openid = input("openid");

        $arr = db("content")->field("collage_number")->where("id",$cid)->find();
        $collage_number = $arr["collage_number"] == 5 ? $arr["collage_number"] - 1 : $arr["collage_number"] - 3;

        $lists = db("order")->field("id, cid, openid, paytime, collageNo")->where(["id"=>[">",4445], "cid"=>$cid, "state"=>2, "status"=>4, "collage_team"=>0, "openid"=>["neq",0]])->group("collageNo")->order("paytime asc")->select();

        $pay = 0;
        $my_status = 0;
        if ($lists) {
//            $str = implode(",", array_column($lists, "collageNo"));
            $count = db("order")->where(["openid"=>$openid, "state"=>2, "cid"=>$cid])->count();
            if ($count) $my_status = 1;

            foreach ($lists as $k => $v) {
                if (!$pay && $v["openid"] == $openid) $pay = 1;

                $time = strtotime(date("Y-m-d 23:00:00",time()));
//                if ($time < $v["paytime"]) $time = strtotime(date("Y-m-d 24:00:00",strtotime("+1 days")));

                $seconds = $time - time();
                if ($seconds <= 0) {
                    unset($lists[$k]);
                    continue;
                }

                $count = db("order")->where(["state" => 2, "collage_team"=>0, "collageNo" => $v["collageNo"], "openid"=>["neq",0]])->count();
                $member = db("member")->field("usertx,nickname")->where("openid", $v["openid"])->find();

                $lists[$k]["time"] = "";
                $lists[$k]["member"] = $member;
                $lists[$k]["count"] = $collage_number - $count;
                $lists[$k]["seconds"] = $seconds;
            }
        }

        $data = [];
        $data["pay"] = $pay;
        $data["lists"] = $lists;
        $data["my_status"] = $my_status;

        return json_encode($data);
    }

    /**
     * 拼团判断
     * @return void
     */
    public function collage_change ()
    {
        $id = input("id");
        $cid = input("cid");
        $openid = input("openid");

        $member = db("member")->field("collage_count, collage_success")->where("openid",$openid)->find();
        $arr_a = $member["collage_count"] ? string2array($member["collage_count"]) : [];
        $arr_b = $member["collage_success"] ? string2array($member["collage_success"]) : [];

        $a = $arr_a[$cid] ?? 0;
        $b = $arr_b[$cid] ?? 0;

        $number = $a ? ($a == 4 ? 1 : $b) : 0;

        if ($number) {
            $collageNo = db("order")->where("id", $id)->value("collageNo");

            $lists = db("order")->field("openid")->where(["collageNo"=>$collageNo, "cid"=>$cid, "state"=>2, "status" => 4, "openid"=>["neq",0]])->select();

            $number = count($lists);
            if ($number < 4) {
                $number = 0;
                foreach ($lists as $v) {
                    $member = db("member")->field("collage_count, collage_success")->where("openid", $v["openid"])->find();
                    $arr_a = $member["collage_count"] ? string2array($member["collage_count"]) : [];
                    $arr_b = $member["collage_success"] ? string2array($member["collage_success"]) : [];

                    $a = $arr_a[$cid] ?? 0;
                    $b = $arr_b[$cid] ?? 0;
                    $number = $number + ($a ? ($a == 4 ? 1 : $b) : 0);
                }
            }
        }

        return $number;
    }

    /**
     * 加入购物车
     */
    public function cart_add()
    {
        $id = input("id");
        $number = input("number");
        $openid = input("openid");
        $shop_id = input("shop_id");

        $data = db("content_list")->where("id",$id)->find();
        $contentData = db("content")->where("id",$data['zid'])->find();

        $where = array();
        $where['cid'] = $contentData['id'];
        $where['shop_id'] = $shop_id;
        $where['openid'] = $openid;
        $where['ggid'] = $id;
        $where['status'] = $contentData['order_type'];
        $cartData = db("order_cart")->where($where)->find();

        if($cartData) {
            $info = array();
            $info['title'] = $contentData['title'];
            $info['thumb'] = $contentData['thumb'];
            $info['ggtitle'] = $data['title'];
            $info['ggmoney'] = $data['money'];
            $info['number'] = $cartData['number'] + $number;
            db("order_cart")->where("id",$data['id'])->update($info);
        }else{
            $info = $where;
            $info['title'] = $contentData['title'];
            $info['thumb'] = $contentData['thumb'];
            $info['ggtitle'] = $data['title'];
            $info['ggmoney'] = $data['money'];
            $info['number'] = $number;
            $info['intime'] = time();
            db("order_cart")->insert($info);
        }

        return 'succ';
    }

    /**
     * 立即购买
     */
    public function order_add()
    {
        $id = input("id");
        $number = input("number");
        $openid = input("openid");
        $shop_id = input("shop_id");
        $three_fanxian_id = input("three_fanxian_id");

        $data = db("content_list")->where("id",$id)->find();
        $contentData = db("content")->where("id",$data['zid'])->find();

        $orderNo = orderNo();

        $info = array();
        $info['cid'] = $contentData['id'];
        $info['goods_id'] = 0;
        $info['openid'] = $openid;
        $info['orderNo'] = orderNo();
        $info['out_trade_no'] = $orderNo;
        $info['title'] = $contentData['title'];
        $info['thumb'] = $contentData['thumb'];
        $info['ggid'] = $data['id'];
        $info['ggtitle'] = $data['title'];
        $info['ggmoney'] = $data['money'];
        $info['number'] = $number;
        $info['state'] = 0;
        $info['intime'] = time();
        $info['status'] = $contentData['order_type'];

        if($contentData['order_type'] == 1){
            $info['cid'] = $shop_id ? $shop_id : 5;
            $info['goods_id'] = $contentData['id'];
        }

        $id = db("order")->insertGetId($info);

        //2021-02-05 新增 start
        /**
         * 用户分享商品
         * 分享用户已购买并且没有被返现
         * 被分享用户有三人从分享页面购买商品
         * 全额返现分享人购买商品的金额
         * 添加购买记录，有$three_fanxian_id添加分享记录
         */
        $arr = [];
        $arr['id_shop'] = $data['zid'];
        $arr['id_order'] = $id;
        $arr['openid'] = $openid;
        $arr['money'] = $data['money']*$number;
        $arr['number'] = 0;
        $arr['state'] = 0;
        $arr['ctime'] = time();
        db("z_order_three_fanxian")->insert($arr);
        
        if($three_fanxian_id){
            $three_fanxian_info = db("z_order_three_fanxian")->where("id",$three_fanxian_id)->find();
            if($three_fanxian_info && $three_fanxian_info['state'] == 0) {
                $arr = [];
                $arr['openid'] = $three_fanxian_info['openid'];
                $arr['openid'] = $openid;
                $arr['id_shop'] = $data['zid'];
                $arr['id_order'] = $id;
                $arr['id_fanxian'] = $three_fanxian_id;
                $arr['state'] = 0;
                $arr['ctime'] = time();
                db("z_order_three_fanxian_fenxiang")->insert($arr);
            }
        }
        //2021-02-05 新增 end

        return $orderNo;
    }

    //2022-03-17 新增 start
    /**
     * 拼团商品
     * 五人团自动补贴一人，一人中奖，三人退款发红包
     * 十人团自动补贴三人，两人中奖，五人退款发红包
     */
    public function collage_add ()
    {
        $info = input();
        $orderNo = orderNo();

        $info["ggmoney"] = $info["ggmoney"] * 100;
        $info["orderNo"] = $orderNo;
        $info["out_trade_no"] = $orderNo;
        $info["collageNo"] = collageNo();
        $info["intime"] = time();
        db("order")->insertGetId($info);

        return $orderNo;
    }

    public function collage_item_add ()
    {
        $info = input();
        $orderNo = orderNo();

        $collageNo = db("order")->where("id",$info["id"])->value("collageNo");

        if ($collageNo) {
            $count = db("order")->where(["state" => [">", 1], "collage" => $collageNo, "openid"=>["neq",0]])->count();
            if ($count >= 4) return "error";

            $info["ggmoney"] = $info["ggmoney"] * 100;
            $info["orderNo"] = $orderNo;
            $info["out_trade_no"] = $orderNo;
            $info["collageNo"] = $collageNo;
            $info["intime"] = time();
            unset($info["id"]);

            db("order")->insertGetId($info);

            return $orderNo;
        } else {
            return "error";
        }
    }

    public function collage_tips ()
    {
        $openid = input("openid");

        return db("order_collage_item")->where(["openid"=>$openid, "collage_state"=>2, "collage_tips"=>0])->count("id");
    }
	
	public function gg(){
		$data = db("basic")->find();

		$data['ggtp']=$data['ggtp'] ? string2array($data['ggtp']) : [];
		foreach($data['ggtp'] as $k=>$v){
			
			// foreach($data['ggtp'][$k] as $kk=>$vv){
				// $data['ggtp'][$kk]['s']=array($v);
				
			// }
			
			$data['ggtp'][$k]=$v;
		}
		
		//var_dump($data['ggtp']);die;
        return json_encode($data);

	}
    //2022-03-17 新增 end
	
	
	
	    public function gmgd ()
    {
		
		
		$start_time = strtotime(date("Y-m-d 09:00:00", time()));
        $end_time = strtotime(date("Y-m-d 23:00:00", time()));
        if ($start_time > time() || $end_time < time()){
			$data = [];
			$data["lists"] =[];
		}else{
			
			$lists = db('order_collage_item')->field("id,openid,title")->where(["collage_state"=>1])->order("id asc")->select();

			foreach ($lists as $k => $v) {
				$arr =db('member')->where(["openid"=>$v["openid"]])->find();

				$lists[$k]["nickname"] = $arr['nickname'];
				$lists[$k]["usertx"] =$arr['usertx'];


			}

			$data = [];
			$data["lists"] = array_values($lists);
			
		}
		
		


        return json_encode($data);
    }
	
}