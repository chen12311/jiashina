<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class Agent extends Controller
{
    public function __construct(){
        parent::__construct();

        $cur['controller'] = $controller = Request::instance()->controller(); //控制器名
        $cur['action'] = $action = Request::instance()->action(); //行动名
        $this->assign('cur',$cur);
    }

    //商户信息
    public function lists(){
        $state = input("state") != '' ? input("state") : 1;
        $nickname = input("nickname") ? input("nickname") : '';

        $where = array();
        $where['agent'] = $state;
        if($nickname){
            $where['nickname'] = ['like','%'.$nickname.'%'];
        }
        $lists = db("member")->where($where)->order("id desc")->paginate(20,false,['query'=>['nickname'=>$nickname]]);
        $pages = $lists->render();

        $this->assign('lists',$lists);
        $this->assign('pages',$pages);

        $this->assign('state',$state);
        $this->assign('nickname',$nickname);

        return view();
    }

    public function fxChange()
    {
        $id = input("id");
        $fx = input("fx");

        db("member")->where("id",$id)->update(['fx'=>$fx]);

        return true;
    }

    /**
     * 成为代理商
     */
    public function shop()
    {
        $id = input("id");

        $data = db("member")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $sffa_a = $data['sffa_a'];
            $file = request()->file('sffa_a');
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
                    $image->thumb(150, 150)->save('.'.$sffb_a);
                }
            }

            $info = array();
            $info['agent'] = input("agent");
            $info['phone_a'] = input("phone_a");
            $info['sffa_a'] = $sffa_a;
            $info['sffb_a'] = $sffb_a;
            $info['content_a'] = input("content_a");
            db("member")->where("id",$id)->update($info);

            header("location:".url("lists","state=".input("agent")));
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    /**
     * 商品列表
     */
    public function content_lists()
    {
        $uid = input("uid");

        $where = array();
        $where['catid'] = $uid;
        $where['order_type'] = 1;

        $lists = db("content")->where($where)->order("id asc")->paginate(15,false,['query'=>[]]);
        $pages = $lists->render();

        if(Request::instance()->isPost()){

        }

        $this->assign('uid',$uid);
        $this->assign('lists',$lists);
        $this->assign('pages',$pages);

        return view();
    }

    /**
     * 添加商品
     */
    public function content_add()
    {
        $catid = input("uid");

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
            $info['zcatid'] = 0;
            $info['catid'] = $catid;
            $info['money'] = (input("money") ? input("money") : 0) * 100;
            $info['thumb'] = $thumb;
            $info['images'] = input("images") ? array2string($info['images']) : '';
            $info['order_type'] = 1;
            $info['intime'] = time();
            db("content")->insert($info);

            header("location:".url("content_lists","uid=".$catid));
            die();
        }

        $this->assign('catid',$catid);

        return view();
    }

    /**
     * 修改商品
     */
    public function content_edit()
    {
        $id = input("id");
        $catid = input("uid");

        $data = db("content")->where("id",$id)->find();

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
            $info['images'] = input("images") ? array2string($info["images"]) : "";
            db("content")->where("id",$id)->update($info);

            header("location:".url("content_lists","uid=".$catid));
            die();
        }

        $this->assign('catid',$catid);
        $this->assign('data',$data);

        return view();
    }

    public function set_order() {
        $id = input("id");
        $catid = input("uid");

        $data = db("content")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $info = input("post.");
            $info['order_type'] = 1;
            $info['order_money'] = ($info['order_money'] ? $info['order_money'] : 0) * 100;
            $info['fwf_money'] = ($info['fwf_money'] ? $info['fwf_money'] : 0) * 100;

            db("content")->where("id",$id)->update($info);

            header("location:".url("content_lists","uid=".$catid));
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    /**
     * 删除商品
     */
    public function content_del()
    {
        $id = input("id");

        db("content")->delete($id);
        db("content_list")->where("zid",$id)->delete();

        return "succ";
    }

    /**
     * 规格列表
     */
    public function content_sub_lists()
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
    public function content_sub_add()
    {
        $id = input("id");
        $catid = input("uid");

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

            header("location:".url("content_sub_lists","uid=".$catid.'&id='.$id));
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    /**
     * 修改规格
     */
    public function content_sub_edit()
    {
        $id = input("id");
        $catid = input("uid");

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

            header("location:".url("content_sub_lists","uid=".$catid.'&id='.$data['id']));
            die();
        }

        $this->assign('data',$data);
        $this->assign('info',$info);

        return view();
    }

    /**
     * 规格删除
     */
    public function content_sub_del()
    {
        $id = input("id");

        db("content_list")->delete($id);

        return true;
    }

    /**
     * 闭店
     */
    public function close()
    {
        $id = input("id");
        $dic = input("dic");

        db("member")->where("id",$id)->update(['state'=>$dic]);

        if($dic == 0) {
            $this->success('已闭店', url('lists'));
        }
    }

    /**
     * 二维码
     */
    public function ewm()
    {
        $id = input("id");

        $data = db("member")->where("id",$id)->find();

        if(!$data['shopurl']) {
            require_once "./vendor/phpqrcode/phpqrcode.php";
            $value = "http://www.jiajiazxgg.com/index.php/index/index/index/id/" . $id;
            \QRcode::png($value, "./ewm/member$id.png", "L", 6, 2);

            $data['shopurl'] = $shopurl = "/ewm/member$id.png";
            db("member")->where("id",$id)->update(['shopurl'=>$shopurl]);
        }

        $this->assign('data',$data);

        return view();
    }

    /**
     * 交易记录
     */
    public function pay()
    {
        $id = input("id");
        $orderNo = input("orderNo") ? input("orderNo") : '';
        $starttime = input("starttime");
        $endtime = input("endtime");
        $dic = input("dic") ? input("dic") : 0;

        $member = db("member")->where("id",$id)->find();

        $where = "`state` >=2 and `status` = 1 and `cid` = ".$id." and `close` = 0";

        if($orderNo){
            $where .= ' and `orderNo` like "%'.$orderNo.'%"';
        }
        if($starttime){
            $where .= ' and `paytime` > '.strtotime($starttime.'00:00:00');
        }
        if($endtime){
            $where .= ' and `paytime` < '.strtotime($endtime.'23:59:59');
        }

        $lists = db("order")->where($where)->order("paytime asc")->select();

        $num = $money = $fwf = 0;
        foreach($lists as $v){
            $num++;
            $money += $v['ggmoney'];
            if($v['status'] == 1){
                $fwf += $v['fwf'];
            } else {
                $fwf += ($v['ggmoney'] * $v['fwf']) < 1 ? 1 : ($v['ggmoney'] * $v['fwf']);
            }
        }

        if(Request::instance()->isPost()){
            $submit = input("submit");
            if($submit) {
                $index = 1;
                foreach($lists as $k => $v){
                    $nickname = db("member")->where("openid",$v['openid'])->value("nickname");

                    if ($v['fx_b'] == 0 && $v['money_b'] == 0){
                        $str_a = '未返现';
                    } else {
                        $str_a = '已返现'.xiaoshu($v['money_b']);
                        if($v['scale'] - $v['money_b'] > 0){
                            $str_a .=' 剩余 '.xiaoshu($v['scale'] - $v['money_b']).' 未反';
                        }
                    }

                    if($v['fx_a'] == 0){
                        $str_b = '已被返现'.xiaoshu($v['fxmoney']);
                    } else {
                        $str_b = '已全部返现';
                    }

                    $str_c = xiaoshu($v['fwf']);

                    $lists[$k]['index'] = $index;
                    $lists[$k]['shopname'] = $member['shopname'];
                    $lists[$k]['nickname'] = $nickname.' | 线上反单';
                    $lists[$k]['ggmoney'] = xiaoshu($v['ggmoney'],1);
                    $lists[$k]['str_a'] = $str_a;
                    $lists[$k]['str_b'] = $str_b;
                    $lists[$k]['str_c'] = $str_c <= 0.01 ? 0.01 : $str_c;
                    $lists[$k]['intime'] = date("Y-m-d H:i:s",$v['intime']);
                    $index++;
                }
                $indexKey = array('index', 'shopname', 'orderNo', 'nickname', 'ggmoney', 'str_a', 'str_b', 'str_c', 'intime');
                $indexName = array('ID','店铺','订单号','下单人','支付金额','返现','被返现','服务费','下单时间');
                $indexWidth = array('10','15','25','30','12','22','18','15','25');
                toExcel($lists, '订单数据'.date("Y-m-d", time()), $indexKey, $indexName, $indexWidth);
            }
        }

        $this->assign('member',$member);
        $this->assign('lists',$lists);
        $this->assign('orderNo',$orderNo);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);
        $this->assign('num',$num);
        $this->assign('money',$money);
        $this->assign('fwf',$fwf);
        $this->assign('dic',$dic);

        return view();
    }

    public function fx_lists()
    {
//        orderFxa(8,1);die();
        $shopname = input("shopname") ? input("shopname") : '';
        $orderNo = input("orderNo") ? input("orderNo") : '';
        $starttime = input("starttime");
        $endtime = input("endtime");
        $dic = input("dic") ? input("dic") : 0;

//        $where = "(`ggid` = 0 and `state` = 7) or (`ggid` > 0 and `state` >= 2)";
//
//        if($shopname){
//            $where = '`ggid` = 0 and  `state` = 7 and (';
//            $lists = db("member")->where("shopname","like","%$shopname%")->select();
//            foreach($lists as $v){
//                $where .= '`cid` = '.$v['id'].' or';
//            }
//            $where = substr($where,0,strlen($where)-3);
//            $where .= ')';
//        }
//        if($orderNo){
//            $where = '(`ggid` = 0 and `state` = 7 and `orderNo` like "%'.$orderNo.'%") or (`ggid` > 0 and `state` >= 2 and `orderNo` = "%'.$orderNo.'%")';
//        }
//        $whereTime = '`paytime` > 0 and `close` = 0';
//        if($starttime){
//            $whereTime .= ' and `paytime` > '.strtotime($starttime.'00:00:00');
//        }
//        if($endtime){
//            $whereTime .= ' and `paytime` < '.strtotime($endtime.'23:59:59');
//        }
//
//        $lists = db("order")->where($where)->where($whereTime)->order("`paytime` asc")->paginate(500,false,['query'=>['shopname'=>$shopname,'orderNo'=>$orderNo,'starttime'=>$starttime,'endtime'=>$endtime]]);
//        $pages = $lists->render();

//        $where = "(`state` = 7 or (`state` >=2 and `status` = 1))";
        if($dic == 0){
            $where = '`state` = 7 and `status` = 0';
        } else {
            $where = '`state` >=2 and `status` = 1';
        }

        if($shopname){
            $lists = db("member")->where("shopname","like","%$shopname%")->select();

            if($lists) {
                $where .= ' and (';
                foreach ($lists as $v) {
                    $where .= '`cid` = ' . $v['id'] . ' or';
                }
                $where = substr($where, 0, strlen($where) - 3);
                $where .= ')';
            }

        }

        if($orderNo){
            $where .= ' and `orderNo` like "%'.$orderNo.'%"';
        }

        if($starttime){
            $where .= ' and `paytime` > '.strtotime($starttime.'00:00:00');
        }
        if($endtime){
            $where .= ' and `paytime` < '.strtotime($endtime.'23:59:59');
        }

        $lists = db("order")->where($where)->order("`paytime` asc")->paginate(500,false,['query'=>['shopname'=>$shopname,'orderNo'=>$orderNo,'starttime'=>$starttime,'endtime'=>$endtime]]);
        $pages = $lists->render();

        $this->assign('lists',$lists);
        $this->assign('pages',$pages);

        $this->assign('shopname',$shopname);
        $this->assign('orderNo',$orderNo);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);
        $this->assign('dic',$dic);

        return view();
    }

    public function pay_lists()
    {
        $shopname = input("shopname") ? input("shopname") : '';
        $orderNo = input("orderNo") ? input("orderNo") : '';
        $starttime = input("starttime");
        $endtime = input("endtime");

        $where = '`title` = "线下支付" and `ggid` = 0';
        if($shopname){
            $where .= ' and (';
            $lists = db("member")->where("shopname","like","%$shopname%")->select();
            foreach($lists as $v){
                $where .= '`cid` = '.$v['id'].' or';
            }
            $where = substr($where,0,strlen($where)-3);
            $where .= ')';
        }
        if($orderNo){
            $where .= ' and `orderNo` like "%'.$orderNo.'%"';
        }
        if($starttime){
            $where .= ' and `intime` > '.strtotime($starttime.'00:00:00');
        }
        if($endtime){
            $where .= ' and `intime` < '.strtotime($endtime.'23:59:59');
        }

        $lists = db("order")->where($where)->order("`intime` desc")->paginate(500,false,['query'=>['shopname'=>$shopname,'orderNo'=>$orderNo,'starttime'=>$starttime,'endtime'=>$endtime]]);
        $pages = $lists->render();

        $all = $allMoney = 0;
        $Lists = db("order")->where($where)->select();
        foreach($Lists as $v){
            $all++;
            if($v['state'] == 7) {
                $allMoney += $v['ggmoney'];
            }
        }

        if(Request::instance()->isPost()){
            $submit = input("submit");
            if($submit) {
                $lists = db("order")->where(['title'=>'线下支付','ggid'=>0])->order("intime desc")->select();
                $index = 1;
                foreach($lists as $k => $v){
                    $nickname = db("member")->where("openid",$v['openid'])->value("nickname");
                    $stateName = '';
                    switch ($v['state']){
                        case 1:$stateName = '未支付';break;
                        case 2:$stateName = '已支付';break;
                        case 7:$stateName = '已完成';break;
                        default:$stateName = '未支付';
                    }
                    $lists[$k]['index'] = $index;
                    $lists[$k]['ggmoney'] = xiaoshu($v['ggmoney'],1);
                    $lists[$k]['nickname'] = $nickname;
                    $lists[$k]['stateName'] = $stateName;
                    $lists[$k]['intime'] = date("Y-m-d H:i:s",$v['intime']);
                    $index++;
                }
                $indexKey = array('index', 'nickname', 'orderNo', 'title', 'ggmoney', 'number', 'stateName', 'intime');
                $indexName = array('ID','下单人微信昵称','订单号','商品','单价','数量','状态','下单时间');
                $indexWidth = array('10','30','25','15','12','10','20','25');
                toExcel($lists, '订单数据'.date("Y-m-d", time()), $indexKey, $indexName, $indexWidth);
            }
        }

        $this->assign('lists',$lists);
        $this->assign('pages',$pages);

        $this->assign('shopname',$shopname);
        $this->assign('orderNo',$orderNo);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);
        $this->assign('all',$all);
        $this->assign('allMoney',$allMoney);

        return view();
    }

    public function pay_succ()
    {
        $id = input("id");

        $data = db("order")->where("id", $id)->find();
        $website = db("website")->where("id",1)->find();
        $member = db("member")->where("id",$data['cid'])->find();

        $money = $data['ggmoney'];
        $orderId = $data['id'];
        $website['scale'] = $member['scale'] ? $member['scale'] : $website['scale'];
        $website['fwf'] = $member['fwf'] ? $member['fwf'] : $website['fwf'];

        $info = array();
        $info['openid'] = $data['openid'];
        $info['orderId'] = $orderId;
        $info['money'] = $money;
        $info['state'] = 2;
        $info['type'] = '-';
        $info['msg'] = "线下订单支付";
        $info['intime'] = time();
        db("record_pay")->insert($info);

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

        $money = floor($money*(1-($website['scale']+$website['fwf'])));
        $money = $money < 1 ? 1 : $money;

        db("order")->where("id", $data['id'])->update(['state' => 7, 'paytime' => $data['intime'], 'scale'=>0,'fwf'=>$website['fwf'], 'transaction_id' => $data['transaction_id']]);
        db("member")->where("openid",$member['openid'])->setInc("money",$money);

        orderFx($data['cid']);

        return 'succ';
    }

    public function close_order()
    {
        $id = input("id");

        db("order")->where("id",$id)->update(['close'=>1]);

        return "success";
    }

    public function fd()
    {
        $id = input("id");

        $data = db("member")->where("id",$id)->find();

        if(Request::instance()->isPost()) {
            $scale = input("scale");
            $fwf = input("fwf");
            db("member")->where("id",$id)->update(['scale'=>$scale,'fwf'=>$fwf]);

            echo "<script>alert('设置成功');history.go(-1);</script>";
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    /**
     * 设置
     */
    public function setup ()
    {
        $data = db("agent_setup")->where("id",1)->find();

        if(Request::instance()->isPost()) {
            $info = input();
            db("agent_setup")->where("id",1)->update($info);

            header("location:".url("setup"));
            die();
        }

        $this->assign('data',$data);

        return view();
    }
}
