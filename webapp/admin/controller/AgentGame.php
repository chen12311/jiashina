<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Loader;
use admin\Admin;
use think\db\Query;
new Admin();

class AgentGame extends Controller
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
        $where['agent_game'] = $state;
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

    /**
     * 成为代理商
     */
    public function shop ()
    {
        $id = input("id");

        $data = db("member")->where("id",$id)->find();

        if(Request::instance()->isPost()){
            $agent_game_sfz_a = $data['agent_game_sfz_a'];
            $file = request()->file('agent_game_sfz_a');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $agent_game_sfz_a = "/uploads/" . date("Ymd", time()) . '/' . $url;
                    $image = \think\Image::open('.'.$agent_game_sfz_a);
                    $image->thumb(450, 450)->save('.'.$agent_game_sfz_a);
                }
            }

            $agent_game_sfz_b = $data['agent_game_sfz_b'];
            $file = request()->file('agent_game_sfz_b');
            if ($file) {
                $infos = $file->move('./uploads');
                if ($infos) {
                    $url = $infos->getFilename();
                    $agent_game_sfz_b = "/uploads/" . date("Ymd", time()) . '/' . $url;
                    $image = \think\Image::open('.'.$agent_game_sfz_b);
                    $image->thumb(150, 150)->save('.'.$agent_game_sfz_b);
                }
            }

            $info = array();
            $info['agent_game'] = input("agent_game");
            $info['agent_game_phone'] = input("agent_game_phone");
            $info['agent_game_sfz_a'] = $agent_game_sfz_a;
            $info['agent_game_sfz_b'] = $agent_game_sfz_b;
            $info['agent_game_content'] = input("agent_game_content");
            db("member")->where("id",$id)->update($info);

            header("location:".url("lists","state=".input("agent_game")));
            die();
        }

        $this->assign('data',$data);

        return view();
    }

    public function record ()
    {
        $id = input("id");
        $starttime = input("starttime") ? strtotime(input("starttime").' 00:00:00') : '';
        $endtime = input("endtime") ? strtotime(input("endtime").' 23:59:59') : '';
        $dic = input("dic") ? input("dic") : 0;

        $where = ["b.id"=>$id,"a.state"=>["in","6,7,8,9,10,11"]];

        if($starttime && !$endtime) $where["a.intime"] = [">=",$starttime];
        if(!$starttime && $endtime) $where["a.intime"] = ["<=",$endtime];
        if($starttime && $endtime) $where["a.intime"] = ["between",[$starttime,$endtime]];

        $lists = db("record_pay")->alias("a")
                ->join("yado_member b","a.openid = b.openid")
                ->field("a.id,a.money,a.type,a.state,a.msg,a.intime,b.nickname")
                ->where($where)
                ->order("b.id asc")
                ->select();

        $data = ["money_a"=>0,"money_b"=>0,"money_c"=>0];
        foreach ($lists as $v) {
            if($v["state"] == 6) $data["money_a"] += $v["money"];
            if($v["state"] == 7 || $v["state"] == 8) $data["money_b"] += $v["money"];
            if($v["state"] == 11) $data["money_c"] += $v["money"];

        }

        if(Request::instance()->isPost()){
            $submit = input("submit");
            if($submit) {
                if(count($lists)) {
                    $i = 0;
                    $arr = [];
                    foreach ($lists as $v) {
                        $arr[$i]["id"] = $i;
                        $arr[$i]["nickname"] = $v["nickname"];
                        $arr[$i]["money"] = "{$v["type"]} ".xiaoshu($v["money"]);
                        $arr[$i]["msg"] = $v["msg"];
                        $arr[$i]["intime"] = date("Y-m-d",$v["intime"]);
                        $i++;
                    }
                    $indexKey = array('id', 'nickname', 'money', 'msg', 'intime');
                    $indexName = array('ID', '昵称', '金额', '类型', '时间');
                    $indexWidth = array('10', '15', '15', '40', '25');
                    toExcel($arr, '资金流水' . date("Y-m-d", time()), $indexKey, $indexName, $indexWidth);
                }
            }
        }

        $this->assign('starttime',$starttime ? date("Y-m-d",$starttime) : '');
        $this->assign('endtime',$endtime ? date("Y-m-d",$endtime) : '');
        $this->assign('dic',$dic);
        $this->assign('lists',$lists);
        $this->assign('data',$data);

        return view();
    }

    /**
     * 设置
     */
    public function setup ()
    {
        $data = db("agent_game_setup")->where("id",1)->find();

        if(Request::instance()->isPost()) {
            $info = input();
            db("agent_game_setup")->where("id",1)->update($info);

            header("location:".url("setup"));
            die();
        }

        $this->assign('data',$data);

        return view();
    }
}
