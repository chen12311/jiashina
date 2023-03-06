<?php
namespace app\handle\controller;
use think\Controller;
use think\Db;
//use think\Session;
use think\Request;
use think\Cache; // 引入 tp 框架的缓存类

 
class Xcx extends Controller{
	private $appId;
	private $appSecret;
	public $error;
	public $token;

	protected $resultSetType = "collection"; // 设置返回类型
	protected $autoWriteTimestamp = true; // 自动记录时间戳
	/**
	* Wxuser constructor
	* @param $appId
	* @param $appSecret
	*/
	public function __construct() {
		$this->appId = "wx63e3f8696c8618e8";
		$this->appSecret = "449365bc735d2facca83ff9e4a6e9182";
	}

	/**
	* 获取用户信息        $openid = input("openid");

	* @param $token
	* @return null|static
	* @throws \think\exception\DbException
	*/
	public static  function getUser($token) {
		$openid = Cache::get($token)['openid'];
		$userInfo = db("member_wxapp")->where("openid",$openid)->find();
		if ($userInfo) {
			$userInfo["create_time"] = date('Y-m-d',$userInfo["create_time"]);
			$userInfo["update_time"] = date('Y-m-d',$userInfo["update_time"]);
		}
		return $userInfo;
	}
	public  function getUserInfo() {
		 $data = input();
		$userInfo = db("member_wxapp")->where("id",$data['id'])->find();
		
		//var_dump($userInfo);die;
		  return json_encode($userInfo);
	}

	/**
	* 用户登陆
	*/
	public function login($post) {
		// 微信登陆 获取session_key
		$session = $this->wxlogin($post["code"]);
		// 自动注册用户
		$user_id = $this->register($session["openid"],$post["nickName"],$post["avatarUrl"],$post["gender"],$post["pid"]);
		// 生成token
		$this->token = $this->token($session["openid"]);
		// 记录缓存 7天
		Cache::set($this->token, $session, 86400 * 7);
		return $user_id;
	}
	
	public function get_openid(){
		$data = input();
				$session = $this->wxlogin($data["code"]);
				return $session["openid"];

	}
	/**
	* 微信登陆
	* @param $code
	* @return array|mixed
	* @throws BaseException
	* @throws \think\exception\DbException
	*/
	private function wxlogin($code) {
		// 获取当前小程序信息
		if (empty($this->appId) || empty($this->appSecret)) {
			//throw new BaseException(['msg' => '请到 [后台-小程序设置] 填写appid 和 appsecret']);
						        return json_encode(['msg' => '请到 [后台-小程序设置] 填写appid 和 appsecret']);

		}
		// 微信登录 (获取session_key)
        if (!$session = $this->sessionKey($code)) {
			
			        return json_encode(['msg' => $this->error]);

           // throw new BaseException(['msg' => $this->error]);
        }
        return $session;
	}

	 /**
     * 获取session_key
     * @param $code
     * @return array|mixed
     */
    public function sessionKey($code) {
        /**
         * code 换取 session_key
         * ​这是一个 HTTPS 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。
         * 其中 session_key 是对用户数据进行加密签名的密钥。为了自身应用安全，session_key 不应该在网络上传输。
         */
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $result = json_decode(httpGet($url, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => 'authorization_code',
            'js_code' => $code
        ]), true);
        if (isset($result['errcode'])) {
            $this->error = $result['errmsg'];
            return false;
        }
        return $result;
    }
	
	/**
     * 生成用户认证的token
     * @param $openid
     * @return string
     */
    private function token($openid) {
        return md5($openid . 'token_salt');
    }

	 /**
     * 获取token
     * @return mixed
     */
    public function getToken() {
        return $this->token;
    }

	/**
     * 自动注册用户
     * @param $open_id
     * @param $userInfo
     * @return mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function register($openid, $nickName,$avatarUrl,$gender,$pid) {
        $userInfo['openid'] = $openid;
        $userInfo['nickname'] = preg_replace('/[\xf0-\xf7].{3}/', '', $nickName);
        $userInfo['usertx'] = $avatarUrl;
        $userInfo['gender'] = $gender+1;
		
		if($pid != 0){
			$userInfo['pid'] = $pid;
		}
        $data=db('member_wxapp')->where('openid',$openid)->find();
        if(!$data){
        	$userInfo['create_time']=time();     
        	$userInfo['update_time']=time();   
            $user_id = db('member_wxapp')->insertGetId($userInfo);
        	if (!$user_id) {
	        	return json_encode(['code'=>0,'msg' => '用户注册失败']);
	        }
	        return $user_id;
        }else{
        	$userInfo['update_time']=time();
        	db('member_wxapp')->where('id',$data['id'])->update($userInfo);
        	return $data['id'];
        }
    }
	
	
	
	/**
     * 用户自动登录
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function wxapplogin() {
		        $data = input();
				$pid = input("pid");

				if(!$pid){
					$data['pid']= 0;
				}
				
        $user_id = $this->login($data);
        $token =  $this->getToken();  
        return json_encode(['code'=>200,'user_id' => $user_id,'token'=>$token]);
    }

	/**
     * 获取用户信息
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
     public function loginInfo() {
		 
		   $data = input();
		 if (!$token =  input("token")) {
			 			        return json_encode(['code' => 0, 'msg' => '缺少必要的参数：token']);

			//throw new BaseException(['code' => 0, 'msg' => '缺少必要的参数：token']);
		 }
		 if (!$user = $this->getUser($token)) {
			 			        return json_encode(['code' => 0, 'msg' => '没有找到用户信息']);

			// throw new BaseException(['code' => 0, 'msg' => '没有找到用户信息']);
		 }
		 return json_encode(['code'=>200,'data'=>$user]);
	 }
	 


	/**
     * 保存用户信息
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
     public function saveInfo() {
		 
		   $data = input();
		   
        	$gxuser = db('member_wxapp')->where('openid',$data['openid'])->update(["nickname"=>$data['nickname'],"usertx"=>$data['usertx'],"phone"=>$data['phone']]);
			
			if($gxuser){
							 			        return json_encode(['code' => 0, 'msg' => '更新成功']);

			}else{
											 			        return json_encode(['code' => 1, 'msg' => '更新失败']);

			}

	}
	 
	 
	 
	  //获取用户手机号
    public function user_phone(){
		
        //获取前端传过来的code，如果前端不知道code是啥，就刁他。
      $post = input();
        if(!isset($post['code']) || empty($post['code'])){
            $return['status'] = 222;
            $return['msg'] = '非法请求';
            return json_encode($return);
            
        }
        
        
      //获取accesstoken
        
       $accessToken = $this->get_access_token();
      
    //请求地址
       $url = 'https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token='.$accessToken;
     //前端传递的code
       $code = $post['code'];
       //组成数组
       $data=[
            'code'=>$code,
        ];
 
          //这里要把传递的参数给转成json，不然小程序接口会报数据类型错误。
        $result = json_decode(httpGet($url,json_encode($data)),true);
        //开始判断获取是否成功
        if($result['errmsg'] == 0){
			
			
			//echo  var_dump($result);die;
            //获取成功
            $phoen = $result['phone_info']['phoneNumber'];
            $return['smg'] = '获取手机号成功！';
            $return['code'] = 200;
            $return['phone'] = $phoen;
            //把手机号返回给前端，或者自己进行存储。看需求
            //Db::name('user')->add();
            return json_encode($return);
            
        }else{
            $return['smg'] = '获取手机号失败！';
            $return['code'] = 201;
            return json_encode($return);
            
        }
 
      
    }
 
 
     //获取小程序二维码的token
    public function get_access_token()
    {
        //先判断缓存里面的access_token过期了没有
        if(Cache::get('access_token')){
            //没过期直接拿出来用
            $a = Cache::get('access_token');
            return $a;
        }else{
            //过期了就重新获取
            $appid = $this->appId;
            $secret = $this->appSecret;
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
            //请求接口，获取accesstoken
            $user_obj = httpGet($url);
            //然后将accesstoken存入缓存里面，官方过期时间7200秒，缓存里面可以过期的早一点，自己把控
			$user_obj = json_decode($user_obj,true);
            Cache::set('access_token',$user_obj['access_token'],7100);
            return Cache::get('access_token');
        }
    }


}