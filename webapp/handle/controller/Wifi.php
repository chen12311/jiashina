<?php
namespace app\handle\controller;
use think\Controller;
use think\Db;
//use think\Session;
use think\Request;
 
class Wifi extends Controller{
	
	    //二进制转图片image/png
    public function data_uri($contents, $mime)
    {
        $base64   = base64_encode($contents);
        return ('data:' . $mime . ';base64,' . $base64);

	}
	  public function wxapplist(){
		          $data = db("wxappad")->order("id desc")->select();
		  		 return json_encode($data);

	  }
	  
	  
	  private function saveBase64($base64_image_content){

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
                  //图片后缀
                  $type = $result[2];
                  //保存位置--图片名
                  $image_name=date('His').str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT).".".$type;
                  $image_file_path = '/erweicode/'.date('Ymd');
                  $image_file = ROOT_PATH.'public'.$image_file_path;
                  $imge_real_url = $image_file.'/'.$image_name;
                  $imge_web_url = $image_file_path.'/'.$image_name;
				  
				  
                  if (!file_exists($image_file)){
                    mkdir($image_file, 0755);
                    fopen($image_file.'\\'.$image_name, "w");
                  } 
				  
				  
                  //解码
                  $decode=base64_decode(str_replace($result[1], '', $base64_image_content));
                  if (file_put_contents($imge_real_url, $decode)){
                        $data['code']=0;
                        $data['imageName']=$image_name;
                        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ?"https://": "http://";
                        //$wzurl = $protocol . $_SERVER['HTTP_HOST'];
                        $data['url']=$protocol .$_SERVER['HTTP_HOST'].$imge_web_url;
                        $data['msg']='保存成功！';
                  }else{
                    $data['code']=1;
                    $data['imgageName']='';
                    $data['url']='';
                    $data['msg']='图片保存失败！';
                  }
        }else{
            $data['code']=1;
            $data['imgageName']='';
            $data['url']='';
            $data['msg']='base64图片格式有误！';
        }       
        return $data['url'];
    }
	  
	 public function add_wifi(){
		 
		   $data = input();
		    $wifiInfo['openid'] = $data["openid"];
			$wifiInfo['name'] = $data["name"];
			$wifiInfo['ssid'] = $data["ssid"];
			$wifiInfo['MAC'] = $data["MAC"];
			$wifiInfo['password'] =$data["password"];
			$wifiInfo['weizhi'] = $data["weizhi"];
			$wifiInfo['latitude'] = $data["latitude"];
			$wifiInfo['longitude'] = $data["longitude"];

			
		     $wifi = db('wifi')->insertGetId($wifiInfo);

				if($wifi){
					
					
								/////////////////////////
								$aaaaa = new Xcx();
								 $access_token=$aaaaa->get_access_token();
										$url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=$access_token";
										//$scene = '?id='.$wifi;
										$scene = $wifi;
										//拼接请求参数
										$data=[
											'scene'=>$scene,
											'page'=>"pages/store_detail/store_detail",
										];
										//参数转为json类型
										$data=json_encode($data);
										//开始请求拿到二维码
										$result = httpGet($url,$data);
								//
								//        $res=$this->isJson($result);
								//        if($res){
								//            dump($res);die;
								//            $res=json_decode($res);
								//            $this -> json(201,"二维码失败出现失误！".$res->errmsg);
								//        }
										//调用方法，将二维码转为base64返回给前端
										$result=$this->data_uri($result,'image/png');
																		$img= $this->saveBase64($result);

								 
								db('wifi')->where('id',$wifi)->update(["ewm"=>$img]);

						/////////////////////
						
					return json_encode(['code'=>0000,'msg' => 'wifi创建成功']);

				}else{
					return json_encode(['code'=>1111,'msg' => 'wifi创建失败']);

				}
	  } 
	  
	  public function edit_wifi(){
		 
		   $data = input();
		   
			$xiugai = 	db('wifi')->where('id',$data['id'])->update($data);

				if($xiugai){
					
						
					return json_encode(['code'=>0000,'msg' => 'wifi修改成功']);

				}else{
					return json_encode(['code'=>1111,'msg' => 'wifi修改失败']);

				}
	  }
	  
	  
	  	  public function wifiliebbiao(){
			  		   $data = input();

		          $data = db("wifi")->where("openid",$data["openid"])->order("id desc")->select();
		  		 return json_encode($data);

	  }
	  	  	 

			 public function wifi_ss(){
			  		   $data = input();
						$where['name|ssid'] = array('like', '%'.$data['key'].'%');
						$where['openid'] = $data["openid"];

		          $data = db("wifi")->where($where)->order("id desc")->select();
		  		 return json_encode($data);

	  }
	  			
		public function merchant_fenlei(){
					          $data = db("wifi_merchant_fenlei")->order("id asc")->select();


		  		 return json_encode($data);

		}		
				
		public function merchant_lists(){
				$data = db("wifi_merchant_info")->order("id asc")->select();


		  		 return json_encode($data);

		}				
		
		public function dianpu_lists(){
				$data = db("wifi_merchant_info")->order("id asc")->select();


		  		 return json_encode($data);

		}		
		
		public function dianpu_fl_lists(){
			$datas = input();
				$data = db("wifi_merchant_info")->where("type",$datas['type'])->order("id asc")->select();


		  		 return json_encode($data);

		}		
		public function dianpu_xiangqing(){
				$data = input();
				$datas = db("wifi_merchant_info")->where("id",$data['id'])->find();
				$wifi = db("wifi")->where("id",$datas['wifiid'])->find();
				$datas['xiangqing']=$datas;
				$datas['wifi']=$wifi;

		  		 return json_encode($datas);

		}		
				
				
		public function wifi_xiangqing(){
				$data = input();
				$wifi = db("wifi")->where("id",$data['id'])->find();
				$datas = db("wifi_merchant_info")->where("wifiid",$data['id'])->find();
				
				$user= db("wifi")->where("openid",$datas['openid'])->find();
				if(!$datas){
									$datas['xiangqing']='';

				}else{
									$datas['xiangqing']=$datas;

				}
				$datas['wifi']=$wifi;
				$datas['sjid']=$user['id'];
				$datas['tzyid']=$user['pid'] ?? 2616;

		  		 return json_encode($datas);

		}		
		
		public function wifi_agree(){
						  		   $data = input();

					          $data = db("content_agree")->where("id",$data['id'])->order("id asc")->find();


		  		 return json_encode($data);

		}		
		
		public function join(){
			$data = input();
			$join = db("wifi_join")->where(["openid"=>$data['openid'],"type"=>$data['type']])->find();
			if($join){
				$datas['code']='404';
				$datas['msg']="您已申请过该类型";
				return json_encode($datas);
			} else{
				$joinInfo['openid'] = $data["openid"];
				$joinInfo['name'] = $data["name"];
				$joinInfo['type'] = $data["type"];
				$joinInfo['phone'] =$data["phone"];
				$wifi_join = db('wifi_join')->insertGetId($joinInfo);
				
												db('member_wxapp')->where('openid',$data["openid"])->update(["type"=>$data['type']]);


				$datas['code']='200';
				$datas['msg']="成功！";
				 return json_encode($datas);
			} 
		}		
		
		public function shop_info(){
			$data = input();
			$join = db("wifi_merchant_info")->where(["openid"=>$data['openid'],"name"=>$data['name']])->find();
			if($join){
				$datas['code']='404';
				$datas['msg']="您已添加过此店铺";
				return json_encode($datas);
			} else{
				$wifi_merchant_info['type'] = $data["type"];
				$wifi_merchant_info['shijian'] = $data["shijian"];
				$wifi_merchant_info['phone'] = $data["phone"];
				$wifi_merchant_info['dizhi'] =$data["dizhi"];				
				$wifi_merchant_info['logo'] = $data["logo"];
				$wifi_merchant_info['biaoqian'] = $data["biaoqian"];
				$wifi_merchant_info['wifiid'] = $data["wifiid"];
				$wifi_merchant_info['openid'] = $data["openid"];
				$wifi_merchant_info['name'] =$data["name"];
				$wifi_merchant_info['latitude'] = $data["latitude"];
				$wifi_merchant_info['longitude'] =$data["longitude"];
				$wifi_join = db('wifi_merchant_info')->insertGetId($wifi_merchant_info);
				$datas['code']='200';
				$datas['msg']="成功！";
				 return json_encode($datas);
			} 
		}
	  		
			public function expand_info(){
			$data = input();
			$join = db("wifi_merchant_expand")->where(["openid"=>$data['openid'],"name"=>$data['name']])->find();
			if($join){
				$datas['code']='404';
				$datas['msg']="您已完善过信息";
				return json_encode($datas);
			} else{

				$wifi_merchant_info['openid'] = $data["openid"];
				$wifi_merchant_info['name'] =$data["name"];
				$wifi_merchant_info['phone'] = $data["phone"];
				$wifi_merchant_info['shenfen_zheng'] =$data["shenfen_zheng"];
				$wifi_merchant_info['shenfen_fan'] =$data["shenfen_fan"];
				$wifi_join = db('wifi_merchant_expand')->insertGetId($wifi_merchant_info);
				$datas['code']='200';
				$datas['msg']="成功！";
				 return json_encode($datas);
			} 
		}
	  
	  public function wifi_lj(){
		  	$data = input();
			$data["sjid"]= $data["sjid"] ?? 2616;
			$data["tzyid"]= $data["tzyid"] ?? 2616;
		  	$log = db("wifi_lj_log")->where(["openid"=>$data['openid'],"wifiid"=>$data['wifiid']])->whereTime('lianjietime','today')->find();
			if($log){
				$wifi_lj =$log['id'];
			}else{
				$wifi_lj_log['openid'] = $data["openid"];
				$wifi_lj_log['sjid'] =$data["sjid"];
				$wifi_lj_log['tzyid'] = $data["tzyid"];
				$wifi_lj_log['wifiid'] =$data["wifiid"];
				$wifi_lj_log['lianjietime'] =time();
				$wifi_lj = db('wifi_merchant_expand')->insertGetId($wifi_lj_log);
				
			}
			
			
				$datas['code']='200';
				$datas['id']=$wifi_lj;
				 return json_encode($datas);

		  //_wifi_lj_log
	  }
	  


	    /**

     * 提现申请保存

     */

    public function tixian_add()
    {
        $img = input("img");
        $openid = input("openid");
        $money = input("money");
        $orderNo = orderNo();

            $member = db("member_wxapp")->where("openid", $openid)->find();
			/* if($type==1){
				$day= date("j");
				$arr=array('5','10','15','20','25','28');
				$aa = in_array($day,$arr);
				if(!$aa){
					$data["err_code_des"] = "当前不在可提现日期";
					return json_encode($data);
					die();
				}
			}	 */
            if ($member['money'] < $money) {
               $data["msg"] = "操作频繁，请稍后";
               $data["code"] = "1111";
				return json_encode($data);
                die();
            }
                $res = db("member_wxapp")->where("openid", $openid)->setDec('money', $money);
                if ($res) {
                        $info = array();
                        $info['orderNo'] = $orderNo;
                        $info['openid'] = $openid;
                        $info['money'] = $money;
                        $info['state'] = 0;
                        $info['img'] = $img;
                        $info['intime'] = time();
                        db("wifi_tixian")->insert($info);
						
						                       
						$infos = array();
                        $infos['openid'] = $openid;
                        $infos['type'] = '-';
                        $infos['money'] = $money;
                        $infos['state'] = 3;
                        $infos['text'] = '用户提现';
                        $infos['intime'] = time();
                        db("wifi_zjmx")->insert($infos);
						
						
						
						
						$data["msg"] = "申请成功，请等待审核！";
						$data["code"] = "0000";
						return json_encode($data);
					
                }

    }
	public function tixian_list()
	{
			$datas = input();
				$data = db("wifi_tixian")->where("openid",$datas['openid'])->order("id asc")->select();


		  		 return json_encode($data);

	}	
	
	public function zjmx()
	{
			$datas = input();
				$data = db("wifi_zjmx")->where("openid",$datas['openid'])->order("id asc")->select();


		  		 return json_encode($data);

	}
	
}