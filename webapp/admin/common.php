<?php
/**
 * 把数组转换成session 数组键值为session名称 
 * @ $data  array  
 **/
function arraySession($data){
    foreach ($data as $key=>$v){
        session("YaDo_".$key,$v);
    }
}

/**
 * 栏目面包屑导航
 * @param int $id 自增ID
 * @param int $type 类型
 * @return array
 */
function cateNav($id = 0, $type = 0)
{
    $topid = 0;
    $level = 1;
    $html = '<li><span><a href="'.url('lists').'">顶级栏目</a></span><i class="fa fa-circle"></i></li> ';
    if($id){
        $data = getData("category",['id'=>$id]);
        if($data){
            $level = $data['level'] + 1;
            if($data['pid'] != 0){
                $dataTop = getData("category",['id'=>$data['pid']]);
                $topid = $dataTop['pid'];
                $html .= '<li><span><a href="'.url('lists','id='.$data['pid']).'">' . $dataTop['name'] . '</a></span><i class="fa fa-circle"></i></li> ';
            }
            $html .= '<li><span><a href="'.url('lists','id='.$id).'">' . $data['name'] . '</a></span><i class="fa fa-circle"></i></li> ';
        }
    }
    if($type){

        $name = $type == 1 ? '添加' : '修改';
        $html .= '<li><span>'.$name.'栏目</span><i class="fa fa-circle"></i></li>';
    }

    $data = array();
    $data['html'] = $html;
    $data['level'] = $level;
    $data['topid'] = $topid;

    return $data;
}

/**
 * @param $k            第几单
 * @param $money_a      返现金额
 * @param $money_b      被返现的总金额
 * @param $money_c      上一单未返现金额
 */
function ajax_index_a($a,$k,$money_a,$money_b,$money_c)
{
    $str = "";
    if($money_c == 0){
        $str .= "第".$k."单：";
        if($k != 1){
            if($money_c > 0){
                $str .= "上单剩余".$money_c."元可以返现，";
            }
            if($money_c == 0){
                $str .= "上单已全部返现，";
            }
            if($money_c < 0) {
                $str .= "剩余".$money_c."元需要返现，";
            }
        }
        $str .= "需要返现".$money_b."元，返现".$money_a.'元，';
        $money = $money_a - $money_b;
        if($money > 0){
            $str .= "返现完成，剩余".$money."元可以返现。<br/>";
            $arr = ajax_index_b($a,$k,$money_a,$money_b,$money);
            $a = $arr['a'];
            $k = $arr['k'];
            $money = $arr['money'];
            $str .= $arr['str'];
        } else if($money == 0){
            $a++;
            $k++;
            $str .= "返现完成。<br/>";
        } else {
            $str .= "返现未完成，剩余".($money*-1)."元需要返现。<br/>";
        }
    }

    $data = array('a'=>$a,'k'=>$k,'money'=>$money,'str'=>$str);
    return $data;
}
function ajax_index_b($a,$k,$money_a,$money_b,$money_c){
    $a++;
    $k++;
    $str = "";
    $str .= "第".$k."单：";
    if($money_c > 0){
        $str .= "上单剩余".$money_c."元可以返现，";
    }
    if($money_c == 0){
        $str .= "上单已全部返现，";
    }
    $str .= "需要返现".$money_b."元，上单剩余金额返现".$money_c."元，";
    $money = $money_c - $money_b;
    if($money > 0){
        $str .= "返现完成，剩余".$money."元可以返现。<br/>";
        $arr = ajax_index_b($a,$k,$money_a,$money_b,$money);
        $a = $arr['a'];
        $k = $arr['k'];
        $money = $arr['money'];
        $str .= $arr['str'];
    } else if($money == 0){
        $a++;
        $k++;
        $str .= "返现完成。<br/>";
    } else {
        $str .= "返现未完成，剩余".($money*-1)."元需要返现。<br/>";
    }

    $data = array('a'=>$a,'k'=>$k,'money'=>$money,'str'=>$str);
    return $data;
}
function ajax_index_c($a,$k,$money_a,$money_b,$money)
{
    $str = "";
    $str .= "第".$k."单：";
    if($money < 0) {
        $str .= "剩余".($money*-1)."元需要返现，返现".$money_a."元，";
    }
    $money = $money_a + $money;
    if($money > 0){
        $str .= "返现完成，剩余".$money."元可以返现。<br/>";
        $arr = ajax_index_b($a,$k,$money_a,$money_b,$money);
        $a = $arr['a'];
        $k = $arr['k'];
        $money = $arr['money'];
        $str .= $arr['str'];
    } else if($money == 0){
        $a++;
        $k++;
        $str .= "返现完成。<br/>";
    } else {
        $str .= "返现未完成，剩余".($money*-1)."元需要返现。<br/>";
    }

    $data = array('a'=>$a,'k'=>$k,'money'=>$money,'str'=>$str);
    return $data;
}

function collage_data ($starttime,$endtime) {
    $where = [];
    if ($starttime && !$endtime) $where["pay_time"] = [">=",($starttime.' 00:00:00')];
    if (!$starttime && $endtime) $where["pay_time"] = ["<=",($endtime.' 23:59:59')];
    if ($starttime && $endtime) $where["pay_time"] = ["between",[($starttime.' 00:00:00'),($endtime.' 23:59:59')]];

    $count_a = db("order_collage_item")->where(["collage_state"=>1, 'openid'=>["neq",""]])->where($where)->count("id");
    $money_a = db("order_collage_item")->where(["collage_state"=>1, 'openid'=>["neq",""]])->where($where)->sum("gg_money");
    $count_b = db("order_collage_item")->where(["collage_state"=>2, 'openid'=>["neq",""]])->where($where)->count("id");
    $money_b = db("order_collage_item")->where(["collage_state"=>2, 'openid'=>["neq",""]])->where($where)->sum("gg_money");
    $count_c = db("order_collage_item")->where(["collage_state"=>3, 'openid'=>["neq",""]])->where($where)->count("id");
    $money_c = db("order_collage_item")->where(["collage_state"=>3, 'openid'=>["neq",""]])->where($where)->sum("gg_money");

    return [$count_a,$money_a,$count_b,$money_b,$count_c,$money_c];
}