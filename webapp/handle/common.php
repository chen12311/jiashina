<?php

/**
 * 获得access_token
 * 公众号的全局唯一
 */
function get_access_token(){
    $data = db("admin")->where("adminid",1)->find();

    if(time() - $data['access_token_time'] > 7000){
        $wechat = get_wechat();
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$wechat['appid']."&secret=".$wechat['secret'];
        $res = httpGet($url);
        $access_token = json_decode($res,true);
        db("admin")->where("adminid",1)->update(["access_token"=>$access_token['access_token'],"access_token_time"=>time()]);
    }else{
        $access_token['access_token'] = $data['access_token'];
    }

    return $access_token['access_token'];
}

/**
 * 获得jsapi_ticket
 * 公众号调用微信JS接口
 */
function get_jsapi_ticket(){
    $data = db("admin")->where("adminid",1)->find();

    if(time() - $data['jsapi_ticket_time'] > 7000){
        $access_token = get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
        $res = httpGet($url);
        $jsapi_ticket = json_decode($res,true);
        db("admin")->where("adminid",1)->update(["jsapi_ticket"=>$jsapi_ticket['ticket'],"jsapi_ticket_time"=>time()]);
    }else{
        $jsapi_ticket['ticket'] = $data['jsapi_ticket'];
    }

    return $jsapi_ticket['ticket'];
}

/**
 * 配置信息
 * 公众号微信JS-SDK配置信息
 */
function wx_js_config($url = ''){
    $wechat = get_wechat();
    $jsapi_ticket = get_jsapi_ticket();

    // 注意 URL 一定要动态获取，不能 hardcode.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = $url ? $url : "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $timestamp = time();
    $nonceStr = encrypt(16);

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapi_ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
    $signature = sha1($string);

    $wx_js_config = array(
        "appId"     => $wechat['appid'],
        "nonceStr"  => $nonceStr,
        "timestamp" => $timestamp,
        "url"       => $url,
        "signature" => $signature,
        "rawString" => $string
    );

    return $wx_js_config;
}