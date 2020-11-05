<?php
/**
 * Created by PhpStorm.
 * User: lenvji
 * Date: 2018/11/5
 * Time: 14:58
 */

namespace app\home\controller;


use common\logic\FlowInfoLogic;
use common\model\FlowInfo;
use common\model\Result;

class Special extends Base {
    public function index(){
        return $this->fetch();
    }

    /**
     * 统计访问流量
     * @return \think\Response
     */
    public function visit_count(){
        $result = new Result();
        $result->success = false;
        $result->msg = '操作失败！';

        $ip = $_SERVER["REMOTE_ADDR"];
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $visit_url = $_SERVER['HTTP_REFERER'];
        $time = time();

        $vo = new FlowInfo();
        $vo->setUserId(0);
        $vo->setVisitIp($ip);
        $vo->setVisitAgent($agent);
        $vo->setVisitUrl($visit_url);
        $vo->setVisitUrlMd5(md5($visit_url));
        $vo->setVisitTime($time);

        $logic =  new FlowInfoLogic();
        $result = $logic->add($vo);

        return response($result,200,[],'json');
    }
}