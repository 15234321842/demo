<?php
/**
 * 用户 Logic 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-09-27
 * Time: 17:46:11
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace common\logic;

use common\model\User;
use common\model\Result;
use common\dao\UserDao;
use think\Db;
use think\db\Expression;

class UserLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new UserDao();
    }

    /**
     * 添加
     * @param User $vo
     * @return Result
     */
    public function add(User $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '添加用户失败！';

        $mapVo = new User();
        $mapVo->setUserName($vo->getUserName());

        $user = $this->find($mapVo);
        if($user){
            $result->success = false;
            $result->msg = '用户已经存在！';
            return $result;
        }

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '添加用户成功！';
        }

        return $result;
    }

    /**
     * 更新
     * @param User $vo
     * @param User $mapVo
     * @return Result
     */
    public function update(User $vo,User $mapVo){
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
     * @param User $mapVo
     * @return Result
     */
    public function delete(User $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
     * 查找单条
     * @param User $mapVo
     * @return array
     */
    public function find(User $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
     * 查找列表
     * @param User $mapVo
     * @return array_list
     */
    public function findList(User $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 用户登录
     * @param User $mapVo
     * @return Result
     */
    public function userLogin($mapVo){
        $result = new Result();
        $result->success = false;
        $result->msg = '登录失败！';

        $map = new User();
        $map->setUserName($mapVo->getUserName());

        $user = $this->find($map);
        if(!$user){
            $result->msg = '用户不存在！';
            $result->success = false;
            return $result;
        }

        //最多登录失败次数
        $max_login_fail = 5;
        $login_fail = $user['login_fail'];

        //24小时后自动解锁
        $login_datetime = $user['login_datetime'];
        $lock_login_datetime = $login_datetime + 86400;
        if($login_fail > 0 && ($lock_login_datetime < time())){
            $unlockVo = new User();
            $unlockVo->setLoginFail(0);

            $mapVo->clearSetDataList();
            $mapVo->setUserId($user['user_id']);

            $success = $this->dao->update($unlockVo,$mapVo);
            if($success){
                $login_fail = 0;
            }
        }

        if($user['login_password'] != md5($mapVo->getLoginPassword())){
            $result->success = false;
            $result->msg = '密码不对！';

            $login_fail += 1;

            if($login_fail >= $max_login_fail){
                $result->msg = '账号锁定，请24小时后再试！';
            }elseif($login_fail <= $max_login_fail){
                $residue_login_fail = $max_login_fail - $login_fail;
                if((0 < $residue_login_fail) && ($residue_login_fail < 4)){
                    $result->msg = '密码不对，还有【'.$residue_login_fail.'】次机会！';
                }
            }

            //记录登录密码错误次数
            $failVo = new User();
            $failVo->setLoginFail($login_fail);
            $failVo->setLoginDatetime(time());

            $mapVo->clearSetDataList();
            $mapVo->setUserId($user['user_id']);

            $this->dao->update($failVo,$mapVo);

            return $result;
        }

        if($login_fail >= $max_login_fail){
            $result->success = false;
            $result->msg = '账号锁定，请24小时后再次！';

            return $result;
        }

        //登录成功，重置登录失败数次为0
        if($login_fail > 0){
            $failVo = new User();
            $failVo->setLoginFail(0);
            $failVo->setLoginDatetime(time());

            $mapVo->clearSetDataList();
            $mapVo->setUserId($user['user_id']);

            $this->dao->update($failVo,$mapVo);
        }

        if($user['user_status'] != 1){
            $result->success = false;
            $result->msg = '用户已经被禁用！';
            return $result;
        }

        if($user['is_del'] != 0){
            $result->success = false;
            $result->msg = '用户已经被注销！';
            return $result;
        }

        $result->success = true;
        $result->msg = '登录成功！';
        $result->data = $user;

        return $result;
    }

    /**
     * 设置密码
     * @param int $user_id
     * @param string $old_password
     * @param string $new_password
     * @return Result
     */
    public function setPassword($user_id,$old_password,$new_password){
        $result = new Result();
        $result->success = false;
        $result->msg = '设置密码失败！';

        $mapVo = new User();
        $mapVo->setUserId($user_id);
        $mapVo->setIsDel(0);
        $mapVo->setUserStatus(1);

        $user = $this->find($mapVo);
        $old_password_md5 = md5($old_password);
        if($user){
            if($old_password_md5 == $user['login_password']){
                $vo = new User();
                $vo->setLoginPassword(md5($new_password));

                if($user['init_password_change'] == 0){
                    $vo->setInitPasswordChange(1);
                }

                $result = $this->update($vo,$mapVo);
                if($result->success){
                    $result->msg = '设置密码功能！';
                }
            }else{
                $result->success = false;
                $result->msg = '原始密码不正确！';
            }
        }

        return $result;
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

        if(isset($params['user_name']) && !string_empty($params['user_name'])){
            $map['user_name'] = new Expression("like '%".$params['user_name']."%'");
            $config['query']['user_name'] = $params['user_name'];
        }
        if(isset($params['nickname']) && !string_empty($params['nickname'])){
            $map['nickname'] = new Expression("like '%".$params['nickname']."%'");
            $config['query']['nickname'] = $params['nickname'];
        }
        if(isset($params['reg_mobile']) && !string_empty($params['reg_mobile'])){
            $map['reg_mobile'] = new Expression("like '%".$params['reg_mobile']."%'");
            $config['query']['reg_mobile'] = $params['reg_mobile'];
        }
        if(isset($params['sex']) && !string_empty($params['sex'])){
            $map['sex'] = $params['sex'];
        }
        if(isset($params['vip_level']) && !string_empty($params['vip_level'])){
            $map['vip_level'] = $params['vip_level'];
        }
        if(isset($params['user_type']) && !string_empty($params['user_type'])){
            $map['user_type'] = $params['user_type'];
        }
        if(isset($params['user_status']) && !string_empty($params['user_status'])){
            $map['user_status'] = $params['user_status'];
        }
        if(isset($params['is_del']) && !string_empty($params['is_del'])){
            $map['is_del'] = $params['is_del'];
        }
        if(isset($params['last_time']) && $params['last_time'] > 0){
            $map['edit_time'] = new Expression('<='.$params['last_time']);
        }

        $list = Db::name('user')
            ->field("user_id,user_name,nickname,reg_mobile,reg_source,sex,user_type,vip_level,reg_time
            ,lecoin")
            ->order(['edit_time'=>'desc','user_id'=>'desc'])
            ->where($map)
            ->paginate(null,false,$config);

        return $list;
    }
}