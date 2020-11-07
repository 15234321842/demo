<?php
/**
* 消息 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-27
* Time: 16:00:51
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace app\houtai\model;

use common\model\Message;
use common\model\Result;
use common\dao\MessageDao;
use think\Db;
use think\db\Expression;

class MessageLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new MessageDao();
    }

    /**
     * 生成消息
     * @param $receive_uid 接收者UID
     * @param $advance_days 提前的天数
     * @return Result
     */
    public function buildMessage($receive_uid,$advance_days){
        $result = new Result();
        $result->success = false;
        $result->msg = '生成消息失败！';

        $time = time();

        try{
            //生成客户生日提醒消息
            $logic = new CrmCustomerLogic();
            $list = $logic->getAllBirthdate($receive_uid,$advance_days);
            foreach($list as $k=>$v){
                $mapVo = new Message();
                $mapVo->setSourceId($v['customer_id']);
                $mapVo->setMsgType(1);
                $mapVo->setMsgTime($time);

                //同一条消息，一天只能生成一条。
                $message = $this->checkMessageExist($mapVo);
                if(!$message){
                    $vo = new Message();
                    $vo->setReceiveUid($receive_uid);
                    $vo->setSendUid(0);
                    $vo->setMsgType(1);

                    $birthdate = date('Y',$time).'-'.date('m-d',$v['birthdate']);
                    $title = '【生日】'.$v['customer_name'].'将于 '.$birthdate.' 过生日';
                    $vo->setMsgTitle($title);

                    $content = '您的客户，'.$v['customer_name'].'将于 '.$birthdate.' 过生日,届时请及时为客户送上生日祝福。';
                    $vo->setMsgSummary(mb_substr($content,0,40,'utf-8'));
                    $vo->setMsgContent($content);
                    $vo->setMsgTime($time);
                    $vo->setIsRead(0);
                    $vo->setReadTime(0);
                    $vo->setAddUid(0);
                    $vo->setEditUid(0);
                    $vo->setAddTime($time);
                    $vo->setEditTime($time);
                    $vo->setSourceId($v['customer_id']);

                    $this->add($vo);
                }
            }

            //生成下次交费的保单提醒消息
            $logic = new MyInsuranceLogic();
            $list = $logic->getAllNextPayInsurance($receive_uid,$advance_days);
            foreach($list as $k=>$v){
                $mapVo = new Message();
                $mapVo->setSourceId($v['insurance_id']);
                $mapVo->setMsgType(2);
                $mapVo->setMsgTime($time);

                //同一条消息，一天只能生成一条。
                $message = $this->checkMessageExist($mapVo);
                if(!$message){
                    $vo = new Message();
                    $vo->setReceiveUid($receive_uid);
                    $vo->setSendUid(0);
                    $vo->setMsgType(2);

                    $first_pay_date = date('Y',$time).'-'.date('m-d',$v['first_pay_date']);
                    $title = '【保单】'.$v['policy_holder'].'的保险单将于 '.$first_pay_date.' 要缴费';
                    $vo->setMsgTitle($title);

                    $content = '';
                    if($v['policy_type'] == 1){
                        $content = '您的客户，'.$v['policy_holder'].'的保险单将于 '.$first_pay_date.' 要缴费,届时请及时提醒客户缴纳保险费用。';
                    }else{
                        $content = '您的投保人，'.$v['policy_holder'].'的保险单将于 '.$first_pay_date.' 要缴费,届时请及时缴纳保险费用。';
                    }
                    $vo->setMsgSummary(mb_substr($content,0,40,'utf-8'));
                    $vo->setMsgContent($content);
                    $vo->setMsgTime($time);
                    $vo->setIsRead(0);
                    $vo->setReadTime(0);
                    $vo->setAddUid(0);
                    $vo->setEditUid(0);
                    $vo->setAddTime($time);
                    $vo->setEditTime($time);
                    $vo->setSourceId($v['insurance_id']);

                    $this->add($vo);
                }
            }

            //生成目标到期提醒消息
            $logic = new WorkAimLogic();
            $list = $logic->getAllExpireAim($receive_uid,$time);
            foreach($list as $k=>$v){
                $mapVo = new Message();
                $mapVo->setSourceId($v['aim_id']);
                $mapVo->setMsgType(3);
                $mapVo->setMsgTime($time);

                //同一条消息，一天只能生成一条。
                $message = $this->checkMessageExist($mapVo);
                if(!$message){
                    $vo = new Message();
                    $vo->setReceiveUid($receive_uid);
                    $vo->setSendUid(0);
                    $vo->setMsgType(3);

                    $date_end = date('Y-m-d',$v['date_end']);
                    $title = '【目标】'.$v['aim_name'].'已于 '.$date_end.' 到期';
                    $vo->setMsgTitle($title);

                    $content = '您的目标，'.$v['aim_name'].'，已于 '.$date_end.' 到期,请检查目标是否完成。';
                    $vo->setMsgSummary(mb_substr($content,0,40,'utf-8'));
                    $vo->setMsgContent($content);
                    $vo->setMsgTime($time);
                    $vo->setIsRead(0);
                    $vo->setReadTime(0);
                    $vo->setAddUid(0);
                    $vo->setEditUid(0);
                    $vo->setAddTime($time);
                    $vo->setEditTime($time);
                    $vo->setSourceId($v['aim_id']);

                    $this->add($vo);
                }
            }

            $result->success = true;
            $result->msg = '生成消息成功！';
        }catch (\Exception $e){
            $result->success = false;
            $result->msg = $e->getMessage();
        }

        return $result;
    }

    /**
    * 添加
    * @param Message $vo
    * @return Result
    */
    public function add(Message $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '消息添加失败！';

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '消息添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param Message $vo
    * @param Message $mapVo
    * @return Result
    */
    public function update(Message $vo,Message $mapVo){
        $result = new Result();
        $result->success = false;
        $result->msg = '更新失败！';

        $success = false;
        $success = $this->dao->update($vo,$mapVo);
        if($success !== false){
            $result->success = true;
            $result->msg = '更新成功！';
        }

        return $result;
    }

    /**
    * 删除
    * @param Message $mapVo
    * @return Result
    */
    public function delete(Message $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param Message $mapVo
    * @return array
    */
    public function find(Message $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param Message $mapVo
    * @return array_list
    */
    public function findList(Message $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 检查消息是否存在
     * @param Message $mapVo
     * @return array_list
     */
    private function checkMessageExist(Message $mapVo){
        $row = array();
        $map = array();
        $whereExp = '1=1 ';

        $map['source_id'] = $mapVo->getSourceId();
        $map['msg_type'] = $mapVo->getMsgType();

        $msg_time = strtotime(date('Y-m-d',$mapVo->getMsgTime()));

        $whereExp .= "AND unix_timestamp(from_unixtime(msg_time,'%Y-%m-%d'))=$msg_time";

        $row = Db::name('message')
            ->field('msg_id,receive_uid,send_uid,msg_type,msg_title,msg_summary,msg_time,is_read,read_time')
            ->where($map)->whereRaw($whereExp)->find();

        return $row;
    }

    /**
     * 获取未读消息数
     * @param int $receive_uid
     * @return int
     */
    public function notReadMessageCount($receive_uid){
        $count = 0;
        $map = array();

        $map['receive_uid'] = $receive_uid;
        $map['is_del'] = 0;
        $map['is_read'] = 0;

        $count = Db::name('message')
            ->where($map)->count('msg_id');

        return $count;
    }

    /**
     * 分页查询
     * @param array $params
     * @return null|\think\paginator\Collection
     */
    public function paginate($params = array())
    {
        $list = null;
        $map = array();
        $whereExp = '1=1 ';
        $config['query'] = array();

        if(isset($params['receive_uid']) && !string_empty($params['receive_uid'])){
            $map['receive_uid'] = $params['receive_uid'];
        }
        if(isset($params['msg_time']) && !string_empty($params['msg_time'])){
            $whereExp .= "and (unix_timestamp(from_unixtime(msg_time, '%Y-%m-%d')) = ".strtotime($params['msg_time']).')';
        }
        if(isset($params['msg_type']) && !string_empty($params['msg_type'])){
            $map['msg_type'] = $params['msg_type'];
        }
        if(isset($params['is_read']) && !string_empty($params['is_read'])){
            $map['is_read'] = $params['is_read'];
        }
        if(isset($params['last_time']) && $params['last_time'] > 0){
            $map['edit_time'] = new Expression('<='.$params['last_time']);
        }
        if(isset($params['is_del']) && !string_empty($params['is_del'])){
            $map['is_del'] = $params['is_del'];
        }

        $list = Db::name('message')
            ->field("msg_id,receive_uid,send_uid,msg_type,msg_title,msg_summary,msg_time,is_read")
            ->order(['is_read'=>'asc','msg_time'=>'desc','msg_id'=>'desc'])
            ->where($map)->whereRaw($whereExp)
            ->paginate(null,false,$config);

        return $list;
    }
}