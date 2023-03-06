<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Loader;
use think\db\Query;
use index\Temp;
use think\Url;

class Index extends Controller
{
    //自动获取模板
    private $tempHtml,$userid;
    public function __construct(){
		parent::__construct();
		
        $temp = new Temp();
		$this->tempHtml = $temp->tempHtml();
    }
    
    public function index()
    {
        $id = input("id");

        $member = db("member")->where("id",$id)->find();
        
        if($member['state'] < 1){
            $this->error("不是店铺","http://www.jiajiazxgg.com/#/?index=0");
        }

        $this->assign('id', $id);
        $this->assign('member', $member);

		return view($this->tempHtml);
    }

    public function pay()
    {
        $openid = session("Member_openid");
        $order = orderNo();
        $money = input("money") * 100;
        $shopid = input("shopid");

        $info = array();
        $info['cid'] = $shopid;
        $info['openid'] = $openid;
        $info['orderNo'] = $order;
        $info['out_trade_no'] = $order;
        $info['title'] = '线下支付';
        $info['thumb'] = '';
        $info['ggid'] = 0;
        $info['ggtitle'] = '线下支付';
        $info['ggmoney'] = $money;
        $info['number'] = 1;
        $info['state'] = 0;
        $info['intime'] = time();
        db("order")->insert($info);

        $jsApiParameters = wxpay($openid, '线下支付', $order, $money, 1);

        $this->assign('id', $shopid);
        $this->assign('money', $money);
        $this->assign('data', $jsApiParameters);

        return view($this->tempHtml);
    }
    

}
