<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Loader;
use think\db\Query;
use index\Temp;
use index\Login;
new Login();


class Member extends Controller
{
    //自动获取模板
    private $tempHtml,$memberDb,$mdataDb;
    public function __construct(){
		parent::__construct();
		
        $temp = new Temp();
        $this->tempHtml = $temp->tempHtml();
		
		$this->basic = get_basic();
		$this->assign('basic',$this->basic);
		
		$this->userid = session('Member_userid');
		$member = get_member($this->userid);
		$this->assign('member',$member);
		
		$this->memberDb = db('member');
		$this->mdataDb = db('member_data');
	}
	
	// 注册
	public function register(){
		$this->assign("catid","注册");
		$this->assign("id","");
		
		if(Request::instance()->isPost()){
			$captcha = input('code');
            if(!captcha_check($captcha,1)){
                $this->error(lang('code_error'),url('register'));
            }
			
			$info = input();
			$encrypt = encrypt();
			
			$memberarr = array();
			$memberarr['username'] = $info['username'];
			$memberarr['password'] = password($info['password'], $encrypt);
			$memberarr['encrypt'] = $encrypt;
			$memberarr['inserttime'] = time();
			$memberarr['level'] = "0";
			
			$userid = $this->memberDb->insertGetId($memberarr);
			
			$mdataarr = array();
			$mdataarr['userid'] = $userid;
			$mdataarr['company'] = $info['company'];
			$mdataarr['name'] = $info['name'];
			$mdataarr['sex'] = "";
			$mdataarr['tel'] = $info['tel1'].'-'.$info['tel2'];
			$mdataarr['email'] = $info['email'];
			$mdataarr['phone'] = $info['phone'];
			$mdataarr['type'] = $info['type'];
			$mdataarr['keyword'] = $info['keyword'];
			$mdataarr['sheng'] = "";
			$mdataarr['shi'] = "";
			$mdataarr['qu'] = "";
			
			$this->mdataDb->insert($mdataarr);
			
			$this->success('注册成功',url('index/member/register'));
		}
		
		return view($this->tempHtml);
	}
	
    // 登录
	public function login(){
		$this->assign("catid","登陆");
		$this->assign("id","");
		
		if(Request::instance()->isPost()){
			$captcha = input('code');
            if(!captcha_check($captcha,1)){
                $this->error(lang('code_error'),url('login'));
            }
			
			$memberInfo = $this->memberDb->where("username = '".input('username')."'")->find();
            $encrypt = $memberInfo['encrypt'];
            $info = array();
            $info['username'] = input('username');
            $info['password'] = password(input('password'), $encrypt);
            $memberDecide = $this->memberDb->where($info)->find();
			
			if($memberDecide){
				//登录成功设置session
				session("Member_userid",$memberDecide['userid']);
				$this->success('登录成功',url('index/index/index'));
            }else{
				$this->error('用户名或密码错误',url('index/member/login'));
            }
		}
		return view($this->tempHtml);
	}
	
	//会员退出
	public function logout(){
		session("Member_userid",null);
		$this->success('退出成功！',url('index.php/index/member/login'));
	}
	
	// 首页
    public function index(){
		return view($this->tempHtml);
    }
	
	// 提现
	public function tx(){
		return view($this->tempHtml);
    }
	
	// 修改资料
	public function edit(){
		if(Request::instance()->isPost()){
			$userid = $this->userid;
			$info = array();
			$info = input();
			
			$fileurl = 'adminstatic/upload/member/img_'.$userid.'.jpg';
			move_uploaded_file($_FILES['image']['tmp_name'],$fileurl);
			$info['image'] = $fileurl;
			
			$memberDb = db('member');
			$memberDb->where("userid",$userid)->update($info);
			$this->success('修改成功',url('index'));
		}
        return view($this->tempHtml);
    }
	
	// 订单-列表
	function order_list() {
		$userid = $this->userid;
		$state = (input('state') <= "3" && input('state')) ? input('state') : '0';
		$order = order_list($userid,$state);
	
		$this->assign('order',$order);
		return view($this->tempHtml);
	}
	
	// 晒单
	function order_comment() {
		$userid = $this->userid;
		
		$orderid = input('dataid');
		$orderDb = db('order');
		$order = $orderDb->where("dataid = $orderid")->find();
		$this->assign('order',$order);
		
		$catid = input('catid');
		$id = input('id');
		$data = get_content_show($catid,$id);
		$this->assign('data',$data);
		
		if(Request::instance()->isPost()){
			$info = input();
			
			$implode = implode(';',$info['img_url']);   //处理数组为字符串
			
			$data = array();
			$data['userid'] = $userid;
			$data['catid'] = $catid;
			$data['id'] = $id;
			$data['orderid'] = $orderid;
			$data['content'] = $info['content'];
			$data['images'] = $implode;
			$data['judge'] = '0';
			$data['inserttime'] = time();
			
			$order_commentDb = db('order_comment');
			$order_commentDb->insert($data);
			$this->success('晒单成功',url('order_comment_list'));
		}
		return view($this->tempHtml);
	}
	
	// 晒单-列表
	function order_comment_list() {
		$userid = $this->userid;
		$order_commentDb = db('order_comment');
		$data = $order_commentDb->where("userid = $userid")->order("dataid DESC")->select();
		$this->assign('data',$data);
		return view($this->tempHtml);
	}
	
	// 收货地址-列表 
	function address_list() {
		$data['jugde'] = input('jugde');
		$data['order'] = input('order');
		$data['catid'] = input('catid');
		$data['id'] = input('id');
		$addressDb = db('address');
		$address = $addressDb->where("userid = $this->userid")->select();
		$this->assign('address',$address);
		$this->assign('data',$data);
		return view($this->tempHtml);
	}
	
	// 收货地址-添加
	function address_add() {
		if(Request::instance()->isPost()){
			$info = array();
			$info = input();
			$info['userid'] = $this->userid;
			$info['address'] = $info['sheng'].$info['shi'].$info['xian'];
			$info['inserttime'] = time();
			$addressDb = db('address');
			unset($info['catid']);
			unset($info['id']);
			
			if($info['judge'] == '1'){
				$addressDb->where("userid = $this->userid")->update(array("judge" => "0"));
			}
			
			$addressDb->insert($info);
			if(input('catid') && input('id')){
				$this->success('添加成功',url('index/index/buy','catid='.input('catid').'&id='.input('id')));
			}else{
				$this->success('添加成功',url('address_list'));
			}
		}
		return view($this->tempHtml);
	}
	
	// 收货地址-修改
	function address_edit() {
		$addressDb = db('address');
		$id = input('id');
		$data = $addressDb->where("id = $id")->find();
		$this->assign('data',$data);
		if(Request::instance()->isPost()){
			$info = array();
			$info = input();
			$info['address'] = $info['sheng'].$info['shi'].$info['xian'];
			$info['inserttime'] = time();
			unset($info['id']);
			
			if($info['judge'] == '1'){	
				$addressDb->where("userid = $this->userid")->update(array("judge" => "0"));
			}
			$addressDb->where("id = $id")->update($info);
			$this->success('修改成功',url('address_list'));
		}
		return view($this->tempHtml);
	}
	
	// 退换货
	public function returns(){
		$orderid  = input("orderid");
		$orderDb = db('order');
		$order = $orderDb->where("dataid = $orderid")->find();
		$this->assign('order', $order);
		
		$openDb = db('open');
		$open = $openDb->where("dataid = $order[open]")->find();
		$this->assign('open', $open);
		
		$categoryDb = db('category');
		$category = $categoryDb->where("id = $order[catid]")->find();
		
		$modelDb = db('model');
		$model = $modelDb->where("id = $category[modelid]")->find();
		
		$temphtmlDb = db($model['modelname']);
		$data = $temphtmlDb->where("catid = $order[catid] and id = $order[id]")->find();
		$this->assign('data', $data);
		if(Request::instance()->isPost()){
			$data = array();
			$data['orderid'] = $orderid;
			$data['userid'] = $this->userid;
			$data['inserttime'] = time();
			
			$returnsDb = db('returns');
			$returnsDb->insert($data);
			
			$arr = array();
			if($order['state'] == '0'){
				$arr['state'] = '2';
			}elseif($order['state'] == '1'){
				$arr['state'] = '3';
			}
			$arr['tuih'] = '0';
			$orderDb->where("dataid = $orderid")->update($arr);
			header('location:'.url("index/member/qd","dic=退换货"));
			die();
		}
		return view($this->tempHtml);
	}
	
	// 退换货-列表
	public function returns_list(){
		
		$userid = $this->userid;
		
		$returns = get_returns_list($userid);
		$this->assign('returns', $returns);
		
		return view($this->tempHtml);
	}
	
	// 退换货-确定	
	public function qd(){
		$dic = input("dic");
		$this->assign('dic', $dic);
		return view($this->tempHtml);
	}
	// 历史
	public function history(){
		$historyDb = db('history');
		$history = $historyDb->where("userid = $this->userid")->select();
		$this->assign('history',$history);
		return view($this->tempHtml);
	}
	
	// 帮助反馈
	public function help(){
		if(Request::instance()->isPost()){
			$helpDb = db('help');
			$info = array();
			$info = input();
			$info['userid'] = $this->userid;
			$info['inserttime'] = time();
			$helpDb->insert($info);
			$this->success('发布成功',url('index'));
		}
		return view($this->tempHtml);
	}
	
	// 交易记录
	public function record(){
		$recordDb = db('record');
		
		$userid = $this->userid;
		$record = $recordDb->where("userid = $userid")->select();
		
		$this->assign('record',$record);
		return view($this->tempHtml);
	}
	
	//选择默认地址
	function ajax_judge(){
		$addressDb = db('address');
		$info = array();
		$info['judge'] = $_POST['judge'];
		if($info['judge'] == '1'){
			$addressDb->where("userid = $this->userid")->update(array("judge" => "0"));
		}
		$addressDb->where("id = $_POST[id]")->update($info);
		echo "succ";
	}
	
	// 地址删除
	function ajax_del(){
		$addressDb = db('address');
		$addressDb->where("id = $_POST[id]")->delete();
		echo "succ";
	}
	
	//ajax图片上传处理
	public function ajax_img() {
		$userid = $this->userid;
		$dataid = input('dataid');
		$str = '';
		foreach($_FILES['file']['tmp_name'] as $k => $v){
			$fileurl = 'adminstatic/upload/order/img_'.$userid.'_'.$dataid.'_'.$k.'.jpg';
			move_uploaded_file($v,$fileurl);
			$str .= "<input type='hidden' name='img_url[]' value='$fileurl'>";
		}
		echo $str;
	}
	
	
}
