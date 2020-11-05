<?php
/**
* 话术 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-22
* Time: 19:53:57
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\MyTalkScript;
use common\model\Result;
use common\dao\MyTalkScriptDao;
use think\Db;
use think\db\Expression;

class MyTalkScriptLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new MyTalkScriptDao();
    }

    /**
    * 添加
    * @param MyTalkScript $vo
    * @return Result
    */
    public function add(MyTalkScript $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '话术添加失败！';

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '话术添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param MyTalkScript $vo
    * @param MyTalkScript $mapVo
    * @return Result
    */
    public function update(MyTalkScript $vo,MyTalkScript $mapVo){
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
    * @param MyTalkScript $mapVo
    * @return Result
    */
    public function delete(MyTalkScript $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param MyTalkScript $mapVo
    * @return array
    */
    public function find(MyTalkScript $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param MyTalkScript $mapVo
    * @return array_list
    */
    public function findList(MyTalkScript $mapVo){
        return $this->dao->findList($mapVo);
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
        $config['query'] = array();

        if(isset($params['script_title']) && !string_empty($params['script_title'])){
            $map['s.script_title'] = array('like','%'.$params['script_title'].'%');
            $config['query']['script_title'] = $params['script_title'];
        }
        if(!string_empty($params['user_id'])){
            $map['s.user_id'] = $params['user_id'];
        }
        if(isset($params['script_type']) && !string_empty($params['script_type'])){
            $map['s.script_type'] = $params['script_type'];
        }
        if(!string_empty($params['is_del'])){
            $map['s.is_del'] = $params['is_del'];
        }
        if(isset($params['last_time']) && $params['last_time'] > 0){
            $map['s.edit_time'] = new Expression('<='.$params['last_time']);
        }

        $list = Db::name('my_talk_script s')
            ->field("s.script_id,s.user_id,s.script_type,s.script_title,s.add_time,s.edit_time")
            ->order(['s.edit_time'=>'desc','s.script_id'=>'desc'])
            ->where($map)
            ->paginate(null,false,$config);

        return $list;
    }
}