<?php
namespace app\handle\controller;
use think\Controller;
use think\Db;
//use think\Session;
use think\Request;
 
class Wxnofiy extends Controller{
	
    public function tuan_success($openid,$orderNo,$nikname,$goodname,$money){
		//echo 11;die;
				$info = db("order_collage_item")->field("id,pid,openid,gg_money,collage_state,collage_win,paytype,title,goodsid")->where("orderNo",$orderNo)->find();

        if($openid && $orderNo && $info['collage_state'] == 2){
          $set_up =  Db::name('setup')->where("id",1)->find();
		  
		            //提交成功，触发信息推送
          $data=[
              'touser'=>$openid,
              'template_id'=>'NbGdcoyz_TiUaEv1TGFq_e9n4kYrneloZjHHHMoavLo',
              'url'=>$web_url = "http://".$_SERVER['SERVER_NAME'].'/index.html#/order?state=2&id='.$info['goodsid'].'&openid='.$openid,
              'topcolor'=>"#FF0000",
              'data'=>array(
                'first'=>array('value'=>"恭喜您拼团成功！",'color'=>"#fc0101"),
                'keyword1'=>array('value'=>$orderNo,'color'=>"#173177"), //拼团订单编号
                'keyword2'=>array('value'=>$nikname,'color'=>"#173177"), //拼团人员
                'keyword3'=>array('value'=>$goodname,'color'=>"#173177"), //拼团商品
                'keyword4'=>array('value'=>$money,'color'=>"#173177"), //拼团金额
                'remark'=>array('value'=>"感谢您的使用。",'color'=>"#173177"),
              )
          ];
		  
		 // var_dump($data);die;
          $get_all_access_token = $this->get_all_access_token();
          $json_data=json_encode($data);//转化成json数组让微信可以接收
          $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$get_all_access_token;//模板消息请求URL
          $res=$this->https_request($url,urldecode($json_data));
          //请求开始
          $res=json_decode($res,true);
          if($res['errcode']==0 && $res['errmsg']=="ok"){ 
          //发送成功    
            return json(['code'=>1]);
          }else{
            return json(['code'=>-4]);
          }
        }
    }    
	public function tuan_fail($openid,$orderNo,$nikname,$goodname,$money){
		
		
		$info = db("order_collage_item")->field("id,pid,openid,gg_money,collage_state,collage_win,paytype,title,goodsid")->where("orderNo",$orderNo)->find();


         if($openid && $orderNo && $info['collage_state'] == 3){
          $set_up =  Db::name('setup')->where("id",1)->find();
          //提交成功，触发信息推送
          $data=[
              'touser'=>$openid,
              'template_id'=>'k7lkV5uzuQQ_V18JWT-tpqgYnMIYVkGVXddyOsJVq5I',
              //'url'=>$web_url = "http://".$_SERVER['SERVER_NAME'].'/index.html',
			  'url'=>$web_url = "http://".$_SERVER['SERVER_NAME'].'/index.html#/order?state=2&id='.$info['goodsid'].'&openid='.$openid,
              'topcolor'=>"#FF0000",
              'data'=>array(
                'first'=>array('value'=>"您参加的拼团拼团失败！",'color'=>"#fc0101"),
                'keyword1'=>array('value'=>$goodname,'color'=>"#173177"), //参团商品
                'keyword2'=>array('value'=>$money,'color'=>"#173177"), //实付金额
                'keyword3'=>array('value'=>'拼团失败，请参加其他拼团','color'=>"#173177"), //失败原因	
                'remark'=>array('value'=>"您支付的金额将原路退还至支付账户，请注意查收。感谢您的使用！",'color'=>"#173177"),
              )
          ];
          $get_all_access_token = $this->get_all_access_token();
          $json_data=json_encode($data);//转化成json数组让微信可以接收
          $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$get_all_access_token;//模板消息请求URL
          $res=$this->https_request($url,urldecode($json_data));
          //请求开始
          $res=json_decode($res,true);
          if($res['errcode']==0 && $res['errmsg']=="ok"){ 
          //发送成功    
            return json(['code'=>1]);
          }else{
            return json(['code'=>-4]);
          }
        }
    }    
	public function tuan_tk($openid,$orderNo,$nikname,$goodname,$money){
		$info = db("order_collage_item")->field("id,pid,openid,gg_money,collage_state,collage_win,paytype,title,goodsid")->where("orderNo",$orderNo)->find();
        if($openid && $orderNo){
          $set_up =  Db::name('setup')->where("id",1)->find();
          //提交成功，触发信息推送
          $data=[
              'touser'=>$openid,
              'template_id'=>'Sok_h41NdFGzsWytT8hj2qVo27_EctdO4SFtVxqTlC0',
			  'url'=>$web_url = "http://".$_SERVER['SERVER_NAME'].'/index.html#/order?state=2&id='.$info['goodsid'].'&openid='.$openid,
             // 'url'=>$web_url = "http://".$_SERVER['SERVER_NAME'].'/index.html',
              'topcolor'=>"#FF0000",
              'data'=>array(
                'first'=>array('value'=>"由于限定时间内未能成团（还差1人），系统自动取消了本次团购。",'color'=>"#fc0101"),
                'keyword1'=>array('value'=>$goodname,'color'=>"#173177"), //拼团商品
                'keyword2'=>array('value'=>$nikname,'color'=>"#173177"), //拼团人员
                'keyword3'=>array('value'=>date("Y-m-d H:i:s",time()),'color'=>"#173177"), //取消时间
                'remark'=>array('value'=>"感谢您的使用。",'color'=>"#173177"),
              )
          ];
          $get_all_access_token = $this->get_all_access_token();
          $json_data=json_encode($data);//转化成json数组让微信可以接收
          $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$get_all_access_token;//模板消息请求URL
          $res=$this->https_request($url,urldecode($json_data));
          //请求开始
          $res=json_decode($res,true);
          if($res['errcode']==0 && $res['errmsg']=="ok"){ 
          //发送成功    
            return json(['code'=>1]);
          }else{
            return json(['code'=>-4]);
          }
        }
    }
 
    public function https_request($url,$data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
 

 //微信access_token默认时间是7200s，设置每6000s获取一次并保存入库
    public function get_all_access_token(){
      $access_token_jilu = db('setup')->where('id',1)->find();
	  $wechat=get_wechat();
       if(time()-$access_token_jilu['token_exp']>6000){
            $appid = $wechat['appid'];
            $secret = $wechat['secret'];
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
            $res = $this->http_curl($url);
             $access_token = $res['access_token'];
            //session('uacc',$res);
            //$access_token =session('uacc.access_token');
            $update_data =[
                'token_exp' =>time(),
                'token'=>$access_token
            ];
            $update_data = db('setup')->where('id',1)->update($update_data);
        }else{
             $access_token = $access_token_jilu['token'];
        }
        return $access_token;
    }


   //获取access_token的curl方法
    public function http_curl($url,$type='get',$res='json',$arr=''){
        //1.初始化curl
        $ch = curl_init();
        //2.设置curl的参数
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }
        //3.采集
        $output = curl_exec($ch);
        //4.关闭
        curl_close($ch);
        if ($res == 'json') {
            return json_decode($output,true);
        }
    }


}