<?php

/*
 * 网站基本设置
 * */
function get_basic(){
	$bsicInfo = db('basic')->find();
	return $bsicInfo;
}

/**
 * 生成随机字符串
 * @param $length
 * @return string
 */
function encrypt($length = '6'){
    $encrypt = '';
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    for( $i = 0; $i < $length; $i++ ){
        $encrypt .= $chars[mt_rand(0,strlen($chars)-1)];
    }
    return $encrypt;
}

/*
 * 密码加密
 * $password 密码
 * $encrypt  加密密文
 * */
function password($password,$encrypt){
    if($encrypt){
        $password = md5($password);
        return md5($password."Ya".$encrypt);
    }
}

/**
 * 字符截取 支持UTF8/GBK
 * @param $string
 * @param $length
 * @param $dot
 */
function str_cut($str, $len, $dot = '...') {
    $tmpstr = "";
    $start = 0;
    $strlen = $len - $start;    //定义需要截取字符的长度
    for($i=0;$i<$strlen;$i++){                   //使用循环语句，单字截取，并用$tmpstr.=$substr(？，？，？)加起来
        if(ord(substr($str,$i,1))>0xa0){     //ord()函数取得substr()的第一个字符的ASCII码，如果大于0xa0的话则是中文字符
            $tmpstr.=substr($str,$i,3);        //设置tmpstr递加，substr($str,$i,3)的3是指三个字符当一个字符截取(因为utf8编码的三个字符算一个汉字)
            $i+=2;
        }else{                                             //其他情况（英文）按单字符截取
            $tmpstr.=substr($str,$i,1);
        }

    }
    $length = strlen($str);
    if($length > $len){
        return $tmpstr."...";
    }else{
        return $tmpstr;
    }
}

/**
* 将字符串转换为数组
*
* @param	string	$data	字符串
* @return	array	返回数组格式，如果，data为空，则返回空数组
*/
function string2array($data) {
	if($data == '') return array();
	@eval("\$array = $data;");
	return $array;
}

/**
* 将数组转换为字符串
*
* @param	array	$data		数组
* @param	bool	$isformdata	如果为0，则不使用new_stripslashes处理，可选参数，默认为1
* @return	string	返回字符串，如果，data为空，则返回空
*/
function array2string($data, $isformdata = 0) {
	if($data == '') return '';
	if($isformdata) $data = new_stripslashes($data);
	return var_export($data, TRUE);
}

/**
 * 返回经stripslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_stripslashes($string) {
	if(!is_array($string)) return stripslashes($string);
	foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
	return $string;
}

/**
 * 金额格式化
 * @param $total_money 金额
 * @param string $state 类型
 * @return float|int|string
 */
function xiaoshu($total_money,$state = ''){
    if($state) {
        $total_money = $total_money/100;
    }else{
        $total_money = sprintf("%.2f", $total_money/100);
    }
    return $total_money;
}

/**
 * 查找数据
 * @param string $db 数据表
 * @param array $where 条件
 * @param string $field 字段
 * @param string $order 排序
 * @return string $res
 */
function getData($db = '', $where = array(), $field = '', $order = '')
{
    $res = '';
    if($db){
        $res = db($db)->where($where)->order($order)->find();
        if($field && !isset($data[$field])){
            $res = $res[$field];
        }
    }

    return $res;
}

/**
 * 获取远程浏览器内容
 * $url 链接
 **/
function httpGet($url,$data = null){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    if(!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
}

/*
 * 过滤掉微信昵称emoji表情
 */
function filterEmoji($str)
{
    $str = preg_replace_callback( '/./u',function (array $match) {
        return strlen($match[0]) >= 4 ? '' : $match[0];
    }, $str);

    return $str;
}

/*
 * 生成订单号
 */
function orderNo($length = '12'){
    $chars = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9" );
    $charsLen = count($chars) - 1;
    shuffle($chars);
    $orderNo = "";
    for ($i=1; $i<=$length; $i++){
        $orderNo .= $chars[mt_rand(0, $charsLen)];
    }
    $orderNo = trim($orderNo);

    return $orderNo;
}

function collageNo () {
    $str = date("ymdH").orderNo(8);

    $count = db("order")->where("collageNo",$str)->count("id");
    if ($count) $str = collageNo();

    return $str;
}

function collage_order_no () {
    $str = date("ymdH").orderNo(8);

    $count = db("order_collage")->where("collageNo",$str)->count();
    if ($count) $str = collage_order_no();

    return $str;
}

/**
 * 微信信息
 **/
function get_wechat(){
    $wechat['appid'] = 'wxcd685d02ba1ce202';
    $wechat['secret'] = '1ceaaf24ac3d60b485efa99e83612e4c';

    return $wechat;
}

/**
 * 处理网址
 * urlEncode处理
 **/
function get_url(){
    $wechat = get_wechat();
    $uri = urlencode('http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]);
    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$wechat['appid'].'&redirect_uri='.$uri.'&response_type=code&scope=snsapi_userinfo&state=';

    return $url;
}

/**
 * 判断会员是否存存在 否:添加
 * $code 微信code
 **/
function wechat_member($code){
    $wechat = get_wechat();

    $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$wechat['appid'].'&secret='.$wechat['secret'].'&code='.$code.'&grant_type=authorization_code';
    $res = httpGet($get_token_url);
    $json_obj = json_decode($res,true);

    $openid = $json_obj['openid'];
    $access_token = $json_obj['access_token'];

    $data = db('member')->where("openid",$openid)->find();

    $get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
    $res = httpGet($get_user_info_url);
    $user_obj = json_decode($res,true);

    if(!$data){
        $info = array();
        $info['openid'] = $openid;
        $info['usertx'] = $user_obj['headimgurl'];
        $info['nickname'] = filterEmoji($user_obj['nickname']);
        $info['intime'] = time();
        $userid = db('member')->insertGetId($info);

        session("Member_userid",$userid);
        session("Member_openid",$openid);
    }else{
        $info = array();
        $info['usertx'] = $user_obj['headimgurl'];
        $info['nickname'] = filterEmoji($user_obj['nickname']);
        db('member')->where("id",$data['id'])->update($info);

        session("Member_userid",$data['id']);
        session("Member_openid",$data['openid']);
    }

    return $openid;
}

/**
 * 支付
 * @param $openId
 * @param $goods
 * @param $order_sn
 * @param $total_fee
 * @param string $attach
 * @return json数据，可直接填入js函数作为参数
 * @throws WxPayException
 */
function wxpay($openId,$goods,$order_sn,$total_fee,$attach = ""){
    require_once "../vendor/weixin/lib/WxPay.Api.php";
    require_once '../vendor/weixin/example/WxPay.JsApiPay.php';
    require_once "../vendor/weixin/example/WxPay.Config.php";
    require_once '../vendor/weixin/example/log.php';

    //初始化日志
    $logHandler= new CLogFileHandler("../vendor/weixin/logs/".date('Y-m-d').'.log');
    $log = Log::Init($logHandler, 15);
//    $total_fee = 1;
    $tools = new JsApiPay();
    $config = new WxPayConfig();

    $input = new WxPayUnifiedOrder();
    $input->SetBody($goods);                					//商品名称
    $input->SetAttach($attach);                  				//附加参数,可填可不填,填写的话,里边字符串不能出现空格
    $input->SetOut_trade_no($order_sn);							//订单号
    $input->SetTotal_fee($total_fee);            				//支付金额,单位:分
    $input->SetTime_start(date("YmdHis"));       		//支付发起时间
    $input->SetTime_expire(date("YmdHis", time() + 600));		//支付超时
    $input->SetGoods_tag("");                             //代金券功能参数
    $input->SetNotify_url("http://www.jiajiazxgg.com/index.php/handle/wxhd/index"); //回调地址
    $input->SetTrade_type("JSAPI");              			//支付类型
    $input->SetOpenid($openId);                  				//用户openID

    $order = WxPayApi::unifiedOrder($config, $input);
    $jsApiParameters = $tools->GetJsApiParameters($order);

    return $jsApiParameters;
}


/**
 * 余额支付20220707
 * @param $openId
 * @param $goods
 * @param $order_sn
 * @param $total_fee
 * @param string $attach
 * @return json数据，可直接填入js函数作为参数
 * @throws WxPayException
 */
function yuepay($openId,$goods,$order_sn,$total_fee,$attach = ""){
//    $total_fee = 1;
	$member = db("member")->where("openid",$openId)->find();
    $yuee= db("member")->where("openid", $openId)->setDec("collage_money", $total_fee);
	if($yuee){
		$model = controller('Wxhd');
		$pay= $model->yuepayoks($openId,$total_fee,$order_sn);
		if($pay=='success'){
			$data['msg']='支付成功';
			$data['code']='success';
		}else{
			$data['msg']='支付失败E002';
			$data['msgs']=$pay;
			$data['code']='error';
		}
	}else{
		$data['msg']='支付失败E001';
		$data['code']='error';
	}
    return $data;
}

function orderFxb($cid,$id,$a = '')
{
    $dataA = db("order")->where("id",$id)->find();  //返现

    if($dataA && $dataA['fx_b'] != 1) {
        $whereB = "`fx_a` = 0 and `close` = 0 and `cid` = ".$cid." and `status` = 0 and `state` >= 2";
        $dataB = db("order")->where($whereB)->order("`paytime` asc")->find(); //被返现

        if($dataB) {
            $fx = db("member")->where("openid", $dataA['openid'])->value("fx");

            if ($fx == 1) {
                return true;
            } else {
                $contentData = db("content")->where("id", $dataA['cid'])->find();

                //被返现的金额 = 商品价格 * 数量（订单金额）
                $moneyB = $dataB['ggmoney'] * $dataB['number'];

                //返现的金额 = 返单设置的返现金额
                $fxmoneySum = $contentData['order_money'] * $dataA['number'];
                //返现的金额 = 返现的金额 - 订单已经返现出去的金额
                $fxmoney = $fxmoneySum - $dataA['money_b'];

                if ($fxmoney > 0) {
                    //被返现的订单已被返现的金额 + 这次返现的金额 >= 被返现的订单应该返现的总金额
                    if ($dataB['fxmoney'] + $fxmoney >= $moneyB) {
                        //重新计算返现的金额  被返现的订单应该返现的总金额 - 被返现的订单已被返现的金额
                        $fxmoney = $moneyB - $dataB['fxmoney'];

                        //改变被返现的订单已全部返现
                        db("order")->where("id", $dataB['id'])->update(['fx_a' => 1]);
                    }
                    
                    //返现的金额 = 订单已经返现出去的金额 + 重新计算后返现的金额
                    if ($fxmoneySum == $dataA['money_b'] + $fxmoney) {
                        //改变返现的订单的状态为返现已经完成
                        db("order")->where("id", $dataA['id'])->update(['fx_b' => 1]);
                    }

                    //改变订单已经返现的金额 已经返现的金额 + 这次返现的金额
                    db("order")->where("id", $dataA['id'])->setInc('money_b', $fxmoney);

                    //改变被返现的订单已被返现的金额 已被返现的金额 + 这次返现的金额
                    db("order")->where("id", $dataB['id'])->setInc("fxmoney", $fxmoney);

                    $info = array();
                    $info['openid'] = $dataB['openid'];
                    $info['orderid'] = $dataA['id'];
                    $info['money'] = $fxmoney;
                    $info['orderid_v'] = $dataB['id'];
                    $info['intime'] = time();
                    db("record_fx")->insert($info);
                    db("member")->where("openid", $dataB['openid'])->setInc("money", $fxmoney);
                } else {
                    db("order")->where("id", $dataA['id'])->update(['fx_b' => 1]);
                }
                orderFxb($cid,$id);
            }
        }
    }
    return true;
}

function orderFxa($cid,$status)
{
//    $whereA = "`fx_b` = 0 and `close` = 0 and `cid` = ".$cid." and `state` = 7 and `id` > 2860";
    $whereA = "`fx_b` = 0 and `close` = 0 and `cid` = ".$cid." and `status` = ".$status." and `state` >= 2 and `id` > 2860";
    $dataA = db("order")->where($whereA)->order("`paytime` asc")->find();  //返现
    if($dataA) {
//        $whereB = " `state` = 7 and `fx_a` = 0 and `close` = 0 and `cid` = " . $cid;
        $whereB = "`fx_a` = 0 and `close` = 0 and `cid` = ".$cid." and `status` = ".$status." and `state` >= 2";
        $dataB = db("order")->where($whereB)->order("`paytime` asc")->find(); //被返现
        if($dataB) {
            $dataJudge = db("order")->where("id", $dataA['id'])->find();
            $fx = db("member")->where("openid", $dataJudge['openid'])->value("fx");

            if ($fx == 1) {
                return true;
            } else {
                $contentData = db("content")->where("id", $dataA['goods_id'])->find();

                //被返现的金额 = 商品价格 * 数量（订单金额）
                $moneyB = $dataB['ggmoney'] * $dataB['number'];

                //返现的金额 = 返单设置的返现金额
                $fxmoneySum = $contentData['order_money'];
                //返现的金额 = 返现的金额 - 订单已经返现出去的金额
                $fxmoney = $fxmoneySum - $dataA['money_b'];

                if ($fxmoney > 0) {
                    //被返现的订单已被返现的金额 + 这次返现的金额 >= 被返现的订单应该返现的总金额
                    if ($dataB['fxmoney'] + $fxmoney >= $moneyB) {
                        //重新计算返现的金额  被返现的订单应该返现的总金额 - 被返现的订单已被返现的金额
                        $fxmoney = $moneyB - $dataB['fxmoney'];

                        //改变被返现的订单已全部返现
                        db("order")->where("id", $dataB['id'])->update(['fx_a' => 1]);
                    }


                    //商品价格 * 数量（订单金额） = 订单已经返现出去的金额 + 重新计算后返现的金额
                    if ($fxmoneySum == $dataA['money_b'] + $fxmoney) {

                        //改变返现的订单的状态为返现已经完成
                        db("order")->where("id", $dataA['id'])->update(['fx_b' => 1]);
                    }

                    //改变订单已经返现的金额 已经返现的金额 + 这次返现的金额
                    db("order")->where("id", $dataA['id'])->setInc('money_b', $fxmoney);

                    //改变被返现的订单已被返现的金额 已被返现的金额 + 这次返现的金额
                    db("order")->where("id", $dataB['id'])->setInc("fxmoney", $fxmoney);

                    $info = array();
                    $info['openid'] = $dataB['openid'];
                    $info['orderid'] = $dataA['id'];
                    $info['money'] = $fxmoney;
                    $info['orderid_v'] = $dataB['id'];
                    $info['intime'] = time();
                    db("record_fx")->insert($info);
                    db("member")->where("openid", $dataB['openid'])->setInc("money", $fxmoney);
                } else {
                    db("order")->where("id", $dataA['id'])->update(['fx_b' => 1]);
                }
                orderFxa($cid, $status);
            }
        }
    }
    return true;
}

function orderFx($cid,$status)
{
//    $whereA = "`fx_b` = 0 and `close` = 0 and `cid` = ".$cid." and `state` = 7 and `id` > 2860";
    $whereA = "`fx_b` = 0 and `close` = 0 and `cid` = ".$cid." and `status` = ".$status." and (`state` = 7 or `state` >= 2) and `id` > 2860";
    $dataA = db("order")->where($whereA)->order("`paytime` asc")->find();  //返现
    if($dataA) {
//        $whereB = " `state` = 7 and `fx_a` = 0 and `close` = 0 and `cid` = " . $cid;
        $whereB = "`fx_a` = 0 and `close` = 0 and `cid` = ".$cid." and `status` = ".$status." and (`state` = 7 or `state` >= 2)";
        $dataB = db("order")->where($whereB)->order("`paytime` asc")->find(); //被返现
        if($dataB) {
            $dataJudge = db("order")->where("id", $dataA['id'])->find();
            $fx = db("member")->where("openid", $dataJudge['openid'])->value("fx");

            if ($fx == 1) {
                return true;
            } else {
                $member = db("member")->where("id", $cid)->find();
                $website = db("website")->where("id", 1)->find();
                $website['scale'] = $member['scale'] || $member['fwf'] == 0 ? $member['scale'] : $website['scale'];
                $website['fwf'] = $member['fwf'] || $member['fwf'] == 0 ? $member['fwf'] : $website['fwf'];

                //订单金额 = 商品价格 * 数量（订单金额）
                $moneyA = $dataA['ggmoney'] * $dataA['number'];
                //被返现的金额 = 商品价格 * 数量（订单金额）
                $moneyB = $dataB['ggmoney'] * $dataB['number'];

                //返现的金额 = 订单金额 * 返现比例
                $fxmoneySum = intval($moneyA * $website['scale']);
                //返现的金额 = 返现的金额 - 订单已经返现出去的金额
                $fxmoney = $fxmoneySum - $dataA['money_b'];

                if ($fxmoney > 0) {
                    //被返现的订单已被返现的金额 + 这次返现的金额 >= 被返现的订单应该返现的总金额
                    if ($dataB['fxmoney'] + $fxmoney >= $moneyB) {
                        //重新计算返现的金额  被返现的订单应该返现的总金额 - 被返现的订单已被返现的金额
                        $fxmoney = $moneyB - $dataB['fxmoney'];

                        //改变被返现的订单已全部返现
                        db("order")->where("id", $dataB['id'])->update(['fx_a' => 1]);
                    }


                    //商品价格 * 数量（订单金额） = 订单已经返现出去的金额 + 重新计算后返现的金额
                    if ($fxmoneySum == $dataA['money_b'] + $fxmoney) {

                        //改变返现的订单的状态为返现已经完成
                        db("order")->where("id", $dataA['id'])->update(['fx_b' => 1]);
                    }

                    //改变订单已经返现的金额 已经返现的金额 + 这次返现的金额
                    db("order")->where("id", $dataA['id'])->setInc('money_b', $fxmoney);

                    //改变被返现的订单已被返现的金额 已被返现的金额 + 这次返现的金额
                    db("order")->where("id", $dataB['id'])->setInc("fxmoney", $fxmoney);

                    //改变返现的订单的返现比例
                    db("order")->where("id", $dataA['id'])->update(['scale' => $website['scale']]);

                    $info = array();
                    $info['openid'] = $dataB['openid'];
                    $info['orderid'] = $dataA['id'];
                    $info['money'] = $fxmoney;
                    $info['orderid_v'] = $dataB['id'];
                    $info['intime'] = time();
                    db("record_fx")->insert($info);
                    db("member")->where("openid", $dataB['openid'])->setInc("money", $fxmoney);
                } else {
                    db("order")->where("id", $dataA['id'])->update(['fx_b' => 1]);
                }
                orderFx($cid, $status);
            }
        }
    }
    return true;
}

function orderFxc()
{

//    SELECT * FROM `yado_order` WHERE `id` >= 3015 and `id` <= 3081 and `state` = 7
//ORDER BY `yado_order`.`id`  ASC
//
//    UPDATE `yado_order` SET `money_b` = 0 WHERE `id` >= 3015 and `id` <= 3081 and `state` = 7
    $start_id = 3015;   //2737//180970
    $end_id = 3081;     //2806//181031
    for($i = 3015; $i <= 3081; $i++){
        $dataA = db("order")->where("id",$i)->find();
        if($dataA) {
            $cid = $dataA['cid'];
            $whereB = "`fx_a` = 0 and `close` = 0 and `cid` = ".$cid." and `status` = 0 and (`state` = 7 or `state` >= 2)";
            $dataB = db("order")->where($whereB)->order("`paytime` asc")->find(); //被返现
            $member = db("member")->where("id",$cid)->find();
            $website['scale'] = $dataA['scale'];
            $website['fwf'] = $dataA['fwf'];

            //订单金额 = 商品价格 * 数量（订单金额）
            $moneyA = $dataA['ggmoney'] * $dataA['number'];
            //被返现的金额 = 商品价格 * 数量（订单金额）
            $moneyB = $dataB['ggmoney'] * $dataB['number'];

            //返现的金额 = 订单金额 * 返现比例
            $fxmoneySum = intval($moneyA * $website['scale']);
            //返现的金额 = 返现的金额 - 订单已经返现出去的金额
            $fxmoney = $fxmoneySum - $dataA['money_b'];

            if ($fxmoney > 0) {
                //被返现的订单已被返现的金额 + 这次返现的金额 >= 被返现的订单应该返现的总金额
                if ($dataB['fxmoney'] + $fxmoney >= $moneyB) {
                    //重新计算返现的金额  被返现的订单应该返现的总金额 - 被返现的订单已被返现的金额
                    $fxmoney = $moneyB - $dataB['fxmoney'];
                    //改变被返现的订单已全部返现
                    db("order")->where("id", $dataB['id'])->update(['fx_a' => 1]);
                }

                //改变订单已经返现的金额 已经返现的金额 + 这次返现的金额
                db("order")->where("id", $dataA['id'])->setInc('money_b', $fxmoney);

                //改变被返现的订单已被返现的金额 已被返现的金额 + 这次返现的金额
                db("order")->where("id", $dataB['id'])->setInc("fxmoney", $fxmoney);

                $info = array();
                $info['openid'] = $dataB['openid'];
                $info['orderid'] = $dataA['id'];
                $info['money'] = $fxmoney;
                $info['orderid_v'] = $dataB['id'];
                $info['intime'] = time();
                db("record_fx")->insert($info);
                db("member")->where("openid", $dataB['openid'])->setInc("money", $fxmoney);
            }
        }
    }
    return true;
}

/**
 * 企业付款到零钱
 * @param int $orderNo
 * @param string $openid 久其社区微信会员openid
 * @param int $money 金额
 * @return string $res
 */
function withdraw($orderNo,$openid,$money,$ip){
    $weChat = get_wechat();

    $data = array(
        'mch_appid' => $weChat['appid'],//商户账号appid
        'mchid' => '1550831061',//商户号
        'nonce_str' => encrypt(32),//随机字符串
        'partner_trade_no' => $orderNo,//商户订单号
        'openid' => $openid,//用户openid
        'check_name' =>'NO_CHECK',//校验用户姓名选项,
        're_user_name' => '',//收款用户姓名
        'amount' => $money,//金额
        'desc' => '提现',//企业付款描述信息
        'spbill_create_ip'=> $ip,//Ip地址
    );
    $data = array_filter($data);
    ksort($data);

    $str = '';
    foreach($data as $k=>$v) {
        $str .= $k.'='.$v.'&';
    }
    $str .= 'key=8s5r5cfqd453e775dq95795cy7b09x9d';
    $data['sign'] = md5($str);
    $xml = arraytoxml($data);

    $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers'; //调用接口
    $res = curl($xml,$url);

    return $res;
}

/**
 * 数组转xml
 * @param array $data 数组
 * @return string $str xml
 */
function arraytoxml($data)
{
    $str='<xml>';
    foreach($data as $k=>$v) {
        $str.='<'.$k.'>'.$v.'</'.$k.'>';
    }
    $str.='</xml>';

    return $str;
}

function curl($param="",$url) {
    $postUrl = $url;
    $curlPost = $param;
    $ch = curl_init();                                      //初始化curl
    curl_setopt($ch, CURLOPT_URL,$postUrl);                 //抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);           // 增加 HTTP Header（头）里的字段
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch,CURLOPT_SSLCERT,ROOT_PATH .'./vendor/weixin/cert/apiclient_cert.pem'); //这个是证书的位置绝对路径
    curl_setopt($ch,CURLOPT_SSLKEY,ROOT_PATH .'./vendor/weixin/cert/apiclient_key.pem'); //这个也是证书的位置绝对路径
    $data = curl_exec($ch);                                 //运行curl
    curl_close($ch);

    return $data;
}

/**
 * xml转数组
 * @param string $xml
 * @return mixed $val
 */
function xmltoarray($xml) {
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    $val = json_decode(json_encode($xmlstring),true);

    return $val;
}


function toExcel($list,$filename,$indexKey,$indexName,$indexWidth,$excel2007 = '') {
    include('../vendor/PHPExcel/Classes/PHPExcel.php');
    include('../vendor/PHPExcel/Classes/PHPExcel/Writer/Excel2007.php');

    ob_end_clean();

    $header_arr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    //初始化PHPExcel()
    $objPHPExcel = new PHPExcel();

    //设置保存版本格式
    if ($excel2007) {
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $filename = $filename . '.xlsx';
    } else {
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        $filename = $filename . '.xls';
    }

    //接下来就是写数据到表格里面去
    $objActSheet = $objPHPExcel->getActiveSheet();
    $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');//字体
    $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//居中

    $header_field = '';
    foreach($indexName as $k => $v){
        if($v){
            if($indexKey[$k] == 'orderNo'){
                $header_field = $header_arr[$k];
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension($header_arr[$k])->setWidth($indexWidth[$k]);//宽度
            $objActSheet->setCellValue($header_arr[$k].'1',$v);
        }
    }
    $objPHPExcel->getActiveSheet()->getStyle('A1:'.$header_arr[count($indexName)-1].'1')->getFont()->setBold(true);//加粗

    $startRow = 2;
    foreach ($list as $row) {
        foreach ($indexKey as $key => $value) {
            //这里是设置单元格的内容
            $objActSheet->setCellValue($header_arr[$key] . $startRow, $row[$value]);
            if($header_field){
                $objActSheet->getStyle($header_field)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
            }
        }
        $startRow++;
    }

    // 下载这个表格，在浏览器输出
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
    header("Content-Type:application/force-download");
    header("Content-Type:application/vnd.ms-execl");
    header("Content-Type:application/octet-stream");
    header("Content-Type:application/download");
    header('Content-Disposition:attachment;filename=' . $filename . '');
    header("Content-Transfer-Encoding:binary");
    $objWriter->save('php://output');
}

function collage_order_data ($starttime = '', $endtime = '') {

    $where = [];
    if ($starttime && !$endtime) $where["paytime"] = [">=",strtotime($starttime.'00:00:00')];
    if (!$starttime && $endtime) $where["paytime"] = ["<=",strtotime($endtime.'23:59:59')];
    if ($starttime && $endtime) $where["paytime"] = ["between",[strtotime($starttime.'00:00:00'),strtotime($endtime.'23:59:59')]];

    $count_a = db("order")->where(["id"=>[">",4625], "status"=>4, "state"=>2, 'openid'=>["neq",""]])->where($where)->count();
    $money_a = db("order")->where(["id"=>[">",4625], "status"=>4, "state"=>2, 'openid'=>["neq",""]])->where($where)->sum("ggmoney");
    $count_b = db("order")->where(["id"=>[">",4625], "status"=>4, "collage"=>3, 'openid'=>["neq",""]])->where($where)->count();
    $money_b = db("order")->where(["id"=>[">",4625], "status"=>4, "collage"=>3, 'openid'=>["neq",""]])->where($where)->sum("ggmoney");
    $count_c = db("order")->where(["id"=>[">",4625], "status"=>4, "collage"=>2, 'openid'=>["neq",""]])->where($where)->count();
    $money_c = db("order")->where(["id"=>[">",4625], "status"=>4, "collage"=>2, 'openid'=>["neq",""]])->where($where)->sum("ggmoney");

    return [$count_a,$money_a,$count_b,$money_b,$count_c,$money_c];
}

function sms_send($mobile){
	$statusStr = array(
	"0" => "短信发送成功",
	"-1" => "参数不全",
	"-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
	"30" => "密码错误",
	"40" => "账号不存在",
	"41" => "余额不足",
	"42" => "帐户已过期",
	"43" => "IP地址限制",
	"50" => "内容含有敏感词"
	);
	$smsapi = "http://api.smsbao.com/";
	$user = "***"; //短信平台帐号
	$pass = md5("****"); //短信平台密码
	$content="短信内容";//要发送的短信内容
	$phone = "*****";//要发送短信的手机号码
	$sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
	$result =file_get_contents($sendurl) ;
	echo $statusStr[$result];
	
}