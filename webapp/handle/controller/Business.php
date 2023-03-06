<?php

namespace app\handle\controller;
use think\Controller;
use think\Request;
use think\Loader;
use think\db\Query;

class Business extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

	public function category(){
		$category = db("category")->field("id,name")->where(["level"=>3])->order("id desc")->select();
        return json_encode($category);
	}
	public function add(){
        $data = input();
		 $contents=explode(',', $data['contents']);
	//	var_dump($contents);die;
		$contentss='';
		foreach ($contents as $v=>$k) {
			$contentss.='<p><img src="'.$k.'"/></p>';
		}
		$sjid=db("category")->field("id,pid")->where(["id"=>$data['zid']])->find();
		$sjida=db("category")->field("id,pid")->where(["id"=>$sjid['pid']])->find();
		$sjidb=db("category")->field("id,pid")->where(["id"=>$sjida['pid']])->find(); 


		$id = db("content")->insertGetid([
			"zcatid"=>$sjidb['id'],
			"catid"=>$data["zid"],
			"title"=>$data["title"],
			"thumb"=>$data["thumb"],
			"images"=>array2string(explode(',', $data['images'])),
			"stock"=>0,
			"content"=>'<p><span>'.$data["content"]."</span>".$contentss,
			//"contents"=>$contentss,
			"intime"=>time(),
			'shopuser_id'=>$data['shopuser_id'],
			"collage"=>1,
			"status"=>0,
			"collage_number"=>5,
		]);
		 return json(['code' => 1, 'message' => '商品上传成功']);

	}
	
	public function avatarUpload(){
		$File = $this->request->file("image");
		//var_dump($arryFile);die;
        $pathImg = "";
            $info = $File->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'business' . DS . date('Y-m-d'), md5(microtime(true)));
            if ($info) {
                $pathImg .= "/uploads/business/".date('Y-m-d') . '/' . $info->getFilename();
            } else {
                return json(['errid' => 1, 'message' => '图片上传失败！','data' =>$File->getError()]);
            }
        return json(['errid' => 0, 'message' => '图片上传成功！','data' =>$pathImg]);
	}
	
	
	public function googs_list(){
		$data = input();
        $openid = input("openid");

		$shopuser_id=db("member")->where(["openid"=>$data['openid']])->find();
		$content = db("content")->field("id,thumb,title,sales,cbmoney")->where(["shopuser_id"=>$shopuser_id['id']])->order("id desc")->select();
		
		foreach ($content as $k=>$v){
			$jiage =  db("order_collage_item")->where(["goodsid"=>$v['id'],"collage_state"=>2])->whereTime('ctime', '2022-08-04 12:00:00')->count();
			$content[$k]['sales']=$jiage;
			$content[$k]['money']=xiaoshu($jiage*$v['cbmoney']);
			$content[$k]['cbmoney']=xiaoshu($v['cbmoney']);

		}
		$data['data']=$content;

		return json_encode($data);
	}
	
	public function order_list(){
		$data = input();
        $openid = input("openid");

		$content = db("content")->field("id,shopuser_id")->where(["id"=>$data['goodsid']])->find();

		$shopuser_id=db("member")->where(["openid"=>$data['openid']])->find();
		
		if($content['shopuser_id'] !=$shopuser_id['id'] ){
			$data['data']='该商品不属于您';
			return json_encode($data);
		}
		$content = db("order_collage_item")->field('address,phone,name,title,thumb,orderNo,state')->where(["goodsid"=>$data['goodsid'],"collage_state"=>2])->whereTime('ctime', '2022-08-04')->order("state asc")->select();
		 foreach ($content as $k=>$v){
			         $content[$k]['address'] = $v["address"];
                    if (!stristr($content[$k]['address'],"保定市"))  $content[$k]['address'] = "保定市".$content[$k]['address'];
                    if (!stristr($content[$k]['address'],"河北省"))  $content[$k]['address'] = "河北省".$content[$k]['address'];
					$content[$k]['address']=$content[$k]['address'].','.$v['name'].','.$v['phone'];
		} 
		$data['data']=$content;
		return json_encode($data);
	}
	
	public function fh(){
		$data = input();
        $openid = input("openid");
		$order_collage_item = db("order_collage_item")->where(["orderNo"=>$data['orderNo']])->find();
		$content = db("content")->field("id,shopuser_id")->where(["id"=>$order_collage_item['goodsid']])->find();
		$member=db("member")->where(["openid"=>$data['openid']])->find();
		if($content['shopuser_id'] !=$member['id'] ){
			$data['msg']='该商品不属于您';
							$data['code']=0;
			return json_encode($data);
		}
		
		if($order_collage_item){
			$res= db("order_collage_item")->where("orderNo",$data['orderNo'])->update(['state' =>1,'kd_orderNo'=>$data['kd_orderNo']]);
			if($res){
				$datas['msg']='发货成功';
				$datas['code']=1;
				return json_encode($datas);
			}else{
				$datas['msg']='发货失败';
				$datas['code']=0;
				return json_encode($datas);
			}

		}
		
		
	}
	
}