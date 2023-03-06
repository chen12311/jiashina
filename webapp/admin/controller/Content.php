<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Content extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $cur = array();
        $cur['action'] = $action = Request::instance()->action();
        $cur['controller'] = $controller = Request::instance()->controller();

        $catid = $cur['id'] = input("catid");
        $topArr = getData("category",['id'=>$catid]);
        $cur['tid'] = $topArr["pid"];
        $topArr = getData("category",['id'=>$topArr['pid']]);
        $this->oid = $cur['oid'] = $topArr["pid"];

        $this->assign('cur', $cur);
        $this->assign('catid', $catid);
    }

    /**
     * 商品列表
     */
    public function lists()
    {
        $catid = input("catid");
        $order_type = input("order_type") ? input("order_type") : 0;

        $where = "`catid` = $catid";
        if($order_type !== '' && $catid != 0){
            $where .= " and (`order_type` = 0 or `order_type` = 2)";
        }

        $lists = db("content")->where($where)->order("id asc")->paginate(30,false,['query'=>[]]);
        $pages = $lists->render();

        if(Request::instance()->isPost()){
            if (input('position/a') && input("ids/a")) {
                $catNav = cateNav($catid);
                foreach (input('position/a') as $v) {
                    $ids = input("ids/a");
                    krsort($ids);
                    foreach ($ids as $r) {
                        $info = array();
                        $info['cid'] = $r;
                        $info['catid'] = $catNav['topid'];
                        $info['state'] = $v;
                        $count = db("position")->where($info)->count();
                        if (!$count) {
                            $info['intime'] = time();
                            db("position")->insert($info);
                        }
                    }
                }
            }

            header("location:" . url("lists", 'catid='.$catid));
            die();
        }

        $this->assign('catid',$catid);
        $this->assign('order_type',$order_type);
        $this->assign('lists',$lists);
        $this->assign('pages',$pages);

        return view();
    }

    /**
     * 添加商品
     */
    public function add()
    {
        $catid = input("catid");

        if(Request::instance()->isPost()){
            $thumb = '';
            $file = request()->file('thumb');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $thumb = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input("post.");
            $info['zcatid'] = $catid == 0 ? $catid : $this->oid;
            $info['catid'] = $catid;
            $info['money'] = (input("money") ? input("money") : 0) * 100;
            $info['ymoney'] = (input("ymoney") ? input("ymoney") : 0) * 100;
			            $info['cbmoney'] = (input("cbmoney") ? input("cbmoney") : 0) * 100;

            $info['thumb'] = $thumb;
            $info['images'] = isset($info["images"]) ? array2string($info["images"]) : "";
            $info['order_type'] = $catid == 0 ? 2 : 0;
            $info['intime'] = time();
            if(!$info['collage']){
                unset($info['collage_number']);
                unset($info['collage_red_bag']);
            }
            db("content")->insert($info);

            header("location:".url("lists","catid=".$catid));
            die();
        }

        $this->assign('catid',$catid);

        return view();
    }

    /**
     * 修改商品
     */
    public function edit()
    {
        $id = input("id");
        $catid = input("catid");

        $data = db("content")->where("id",$id)->find();
		
        $shopsname = db("member")->where("id",$data['shopuser_id'])->value("nickname");

        $images = $data['images'] ? string2array($data['images']) : [];

        if(Request::instance()->isPost()){
            $thumb = $data['thumb'];
            $file = request()->file('thumb');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $thumb = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input("post.");
            $info['money'] = (input("money") ? input("money") : 0) * 100;
            $info['ymoney'] = (input("ymoney") ? input("ymoney") : 0) * 100;
            $info['cbmoney'] = (input("cbmoney") ? input("cbmoney") : 0) * 100;
            $info['thumb'] = $thumb;
            $info['images'] = isset($info["images"]) ? array2string($info["images"]) : "";
            db("content")->where("id",$id)->update($info);

            header("location:".url("lists","catid=".$catid));
            die();
        }

        $this->assign('catid',$catid);
        $this->assign('shopsname',$shopsname);
        $this->assign('data',$data);
        $this->assign('images',$images);

        return view();
    }

    public function set_order() {
        $id = input("id");
        $catid = input("catid");

        $data = db("content")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $info = input("post.");
            $info['order_type'] = 1;
            $info['order_money'] = ($info['order_money'] ? $info['order_money'] : 0) * 100;
            $info['fwf_money'] = ($info['fwf_money'] ? $info['fwf_money'] : 0) * 100;

            db("content")->where("id",$id)->update($info);

            header("location:".url("lists","catid=".$catid));
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    /**
     * 分享设置
     */
    public function fxSet()
    {
        $id = input("id");
        $catid = input("catid");

        $data = db("content")->field("`level_a`,`level_b`,`order_money`")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $arr = array();
            $arr['level_a'] = (input("level_a") ? input("level_a") : '') * 100;
            $arr['level_b'] = (input("level_b") ? input("level_b") : '') * 100;
            $arr['order_money'] = (input("order_money") ? input("order_money") : 0) * 100;
            db("content")->where("id",$id)->update($arr);

            header("location:".url("lists","catid=".$catid));
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    /**
     * 删除商品
     */
    public function del()
    {
        $id = input("id");

        db("content")->delete($id);
        db("position")->where("cid",$id)->delete();
        db("content_list")->where("zid",$id)->delete();

        return "succ";
    }

    /**
     * 规格列表
     */
    public function sub_lists()
    {
        $id = input("id");

        $data = db("content")->where("id",$id)->find();
        $lists = db("content_list")->where("zid",$id)->order("id desc")->select();

        $this->assign('id',$id);
        $this->assign('data',$data);
        $this->assign('lists',$lists);

        return view();
    }

    /**
     * 添加规格
     */
    public function sub_add()
    {
        $id = input("id");
        $catid = input("catid");

        $data = db("content")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $thumb = '';
            $file = request()->file('thumb');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $thumb = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input("post.");
            $info['zid'] = $id;
            $info['money'] = (input("money") ? input("money") : 0) * 100;
            $info['thumb'] = $thumb;
            $info['intime'] = time();
            unset($info['pid']);
            unset($info['catid']);
            db("content_list")->insert($info);

            header("location:".url("sub_lists","catid=".$catid.'&id='.$id));
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    /**
     * 修改规格
     */
    public function sub_edit()
    {
        $id = input("id");
        $catid = input("catid");

        $info = db("content_list")->where("id",$id)->find();
        $data = db("content")->where("id",$info['zid'])->find();

        if(Request::instance()->isPost()){
            $thumb = $data['thumb'];
            $file = request()->file('thumb');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $thumb = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input("post.");
            $info['money'] = (input("money") ? input("money") : 0) * 100;
            $info['thumb'] = $thumb;
            db("content_list")->where("id",$id)->update($info);

            header("location:".url("sub_lists","catid=".$catid.'&id='.$id));
            die();
        }

        $this->assign('data',$data);
        $this->assign('info',$info);

        return view();
    }

    /**
     * 规格删除
     */
    public function sub_del()
    {
        $id = input("id");

        db("content_list")->delete($id);

        return true;
    }

    public function ajax_img()
    {
        $images = '';
        $files = request()->file('images');
        if($files){
            foreach($files as $fileArr){
                // 移动到框架应用根目录/public/uploads/ 目录下
                $infoArr = $fileArr->move('./uploads');
                if($infoArr){
                    $url = $infoArr->getFilename();
                    $images .= "<input name='images[]' value='/uploads/".date("Ymd",time())."/".$url."'>";
                }else{
                    $images .= "";
                }
            }
        }

        return $images;
    }

    public function child_add()
    {
        $pid = input("pid");
        $catid = input("catid");
        $scid = input("scid");

        $data = db("content_sc")->where("id",$scid)->find();

        if(Request::instance()->isPost()){
            $thumb = '';
            $file = request()->file('thumb');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $thumb = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input();
            $info['money'] = (input("money") ? input("money") : 0) * 100;
            $info['thumb'] = $thumb;
            $info['inserttime'] = time();
            unset($info['pid']);
            unset($info['catid']);
            db("content_sc_list")->insert($info);

            header("location:".url("child_lists","pid=".$pid."&catid=".$catid.'&scid='.$scid));
            die();
        }

        $this->assign('pid',$pid);
        $this->assign('catid',$catid);
        $this->assign('scid',$scid);
        $this->assign('data',$data);

        return view();
    }

    public function child_edit()
    {
        $pid = input("pid");
        $catid = input("catid");
        $scid = input("scid");
        $id = input("id");

        $data = db("content_sc")->where("id",$scid)->find();
        $info = db("content_sc_list")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $thumb = input("thumbDel") == 1 ? '' : $data['thumb'];
            $file = request()->file('thumb');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $thumb = "/uploads/" . date("Ymd", time()) . '/' . $url;
                }
            }

            $info = input();
            $info['money'] = (input("money") ? input("money") : 0) * 100;
            $info['thumb'] = $thumb;
            unset($info['id']);
            unset($info['pid']);
            unset($info['scid']);
            unset($info['catid']);
            unset($info['thumbDel']);
            db("content_sc_list")->where("id",$id)->update($info);

            header("location:".url("child_lists","pid=".$pid."&catid=".$catid.'&scid='.$scid));
            die();
        }

        $this->assign('pid',$pid);
        $this->assign('catid',$catid);
        $this->assign('scid',$scid);
        $this->assign('data',$data);
        $this->assign('info',$info);

        return view();
    }

    public function child_del()
    {
        $id = input("id");

        db("content_sc_list")->delete($id);

        return 'succ';
    }

    public function collage_record ()
    {
        $id = input("id");
        $time = input("time");

        $lists = [];
        $money = 0;
        if ($time) {
            $where = ["cid" => $id, "collage"=>2, "status" => 4, "paytime"=>["between", [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')]]];
//            $array = db("order")->field("orderNo,collageNo")->where($where)->group("collageNo")->order("paytime desc")->select();
//            $array_a =[];
//            foreach ($array as $b) {
//                $array_a[$b["collageNo"]] = $b["orderNo"];
//            }

            $lists = db("order")->field("orderNo,openid,collageNo,paytime")->where($where)->order("paytime asc")->select();

            $id_arr = [];
            foreach ($lists as $k => $r) {
                $arr_a = db("order")->field("id,orderNo,openid,collageNo,collage_red_bag,paytime")->where(["collage"=>3,"fhtime"=>0,"collageNo"=>$r["collageNo"], "paytime"=>["between", [strtotime($time . '00:00:00'), strtotime($time . '23:59:59')]]])->order("paytime asc")->select();

                $i = 0;
                $arr = [];
                foreach ($arr_a as $v) {
                    if(!isset($id_arr[$r["collageNo"]])) $id_arr[$r["collageNo"]] = [];
//                    if ($i > 3) {
//                        if($array_a[$v["collageNo"]] == $v["orderNo"]) {
//                            if(!in_array($v["id"],$id_arr[$r["collageNo"]])) {
//                                $id_arr[$r["collageNo"]][] = $v["id"];
//                                $arr[] = $v;
//                            }
//                        }
//                    } else {
                        if(!in_array($v["id"],$id_arr[$r["collageNo"]])) {
                            $i++;
                            $id_arr[$r["collageNo"]][] = $v["id"];
                            $arr[] = $v;
                        }
//                    }
                }
                if (!$arr || count($arr) <= 3) {
                    unset($lists[$k]);
                    continue;
                } else {
                    $money_arr = array_column($arr,"collage_red_bag");
                    $money = $money + array_sum($money_arr) - (count($money_arr) >= 3 ? $money_arr[0]*3 : 0);
                    $lists[$k]["id_arr"] = $arr;
                }
            }
        }

        $this->assign('time',$time);
        $this->assign('lists',$lists);
        $this->assign('money',$money);

        return view();
    }

    public function collage_record_a ()
    {
        $id = input("id");
        $time = input("time");

        $title = db("content")->where("id",$id)->value("title");

        $start = strtotime("2022-07-03 00:00:00");
        $end = strtotime("2022-07-06 23:59:59");

        $where = ["cid"=>$id, "state"=>[">",1], "collage"=>["<",4], "paytime"=>["between",[$start,$end]]];
        $lists = db("order")->field("id,ggmoney,orderNo,openid,state,collage_red_bag,collage,collageNo,paytime,out_refund_no")->where($where)->group("collageNo")->order("paytime asc")->select();

        foreach ($lists as $k => $r) {
            $number = $r["out_refund_no"] ? 1 : 0;
            $state = $r["state"] == 3 || $r["collage"] == 2 ? 1 : 0;

            $lists[$k]["state_a"] = $state;

            $where_a = ["cid"=>$id, "id"=>["neq",$r["id"]], "state"=>[">",1], "collage"=>["<",4], "collageNo"=>$r["collageNo"],"paytime"=>["between",[$start,$end]]];
            $arr = db("order")->field("id,ggmoney,orderNo,openid,collage,collageNo,state,collage_red_bag,paytime,out_refund_no,collage_error_many")->where($where_a)->order("paytime asc")->select();

            if (count($arr) < 3) {
                unset($lists[$k]);
                continue;
            }

            foreach ($arr as $i => $v) {
                if ($v["out_refund_no"]) $number = $number + 1;
                if ($v["state"] == 3 || $v["collage"] == 2 && !$state) {
                    $state = 1;
                    $arr[$i]["state_a"] = 1;
                }
            }

            $lists[$k]["id_arr"] = $arr;
            $lists[$k]["number"] = $number;
            $lists[$k]["state_b"] = $state;
        }

        $this->assign('time',$time);
        $this->assign('lists',$lists);
        $this->assign('title',$title);

        return view();
    }

    public function collage_record_b ()
    {
        die();
        $lists = db("collage_many_money")->field("openid,money_reduce")->where("money_reduce",">",0)->select();
//        foreach ($lists as $v) {
//            db("member")->where("openid",$v["openid"])->setDec("collage_money",$v["money_reduce"] * 100);
//        }
        print_R($lists);
        die();
        $id = input("id");
        $time = input("time");

        $title = db("content")->where("id",$id)->value("title");

        $start = strtotime("2022-07-01 00:00:00");
        $end = strtotime("2022-07-06 23:59:59");


        $where = ["cid"=>80, "state"=>[">=",2], "collage"=>["<=",3], "paytime"=>[">=",$start]];
        $lists = db("order")->field("openid")->where($where)->group("openid")->order("paytime asc")->select();
        $lists_a = db("order")->field("cid")->where(["paytime"=>[">=",$start]])->group("cid")->order("cid asc")->select();

        $arr = [];
        $money = 0;
        foreach ($lists as $i => $r) {
            $data = db("order")->field("id,collage_red_bag")->where($where)->where(["openid"=>$r["openid"],"collage_count"=>0])->order("paytime asc")->find();
            $count = db("order")->where($where)->where(["openid"=>$r["openid"],"id"=>[">=",$data["id"]]])->count();

            $success = ceil($count / 5);
            $error = $count - $success;

            $money = db("order")->where($where)->where(["openid"=>$r["openid"],"id"=>[">=",$data["id"]],"out_refund_no"=>[">",0]])->sum("collage_red_bag");
            $money_a = $success * 4 * $data["collage_red_bag"];
            $money_b = $money - $money_a;

            $arr[$r["openid"]] = ["count"=>$count,"success"=>$success,"error"=>$success * 4,"money"=>$money,"money_a"=>$money_a,"money_b"=>$money_b];


        }
    }

    public function money_edit ()
    {
        $money = input("money");
        $openid = input("openid");

//        db("member")->where("openid",$openid)->setDec("collage_money",$money);

//        db("record_pay")->insert([
//            "openid"=>$openid,
//            "orderId"=>0,
//            "money"=>$money,
//            "state"=>19,
//            "type"=>"-",
//            "msg"=>"订单错误重置",
//            "intime"=>date("Y-m-d H:i:s")
//        ]);

        return "success";
    }
	
	
	
	    public function status ($id = 0, $num = 0)
    {
        db("content")->where("id",$id)->setField("status",$num);

        return "success";
    }
	
	
	
}

?>