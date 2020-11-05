<?php
/**
 * 插件控制器 Plugin 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-09-27
 * Time: 17:46:11
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace app\houtai\controller;


use common\logic\CrmCustomerLogic;
use common\logic\IndustryLogic;
use common\logic\ProvinceCityLogic;
use common\logic\SchoolLogic;
use common\logic\SpecialtyLogic;
use common\logic\WorkPositionLogic;
use common\logic\WorkPostLogic;
use common\model\CrmCustomer;
use common\model\Industry;
use common\model\ProvinceCity;
use common\model\Result;
use common\model\School;
use common\model\Specialty;
use common\model\WorkPosition;
use common\model\WorkPost;

class Plugin extends Base {

    /**
     * 测试页面
     * @return mixed
     */
    public function letu_picker(){
        $customerRelationsList = config('customer_relations');
        $this->assign('customerRelationsList',$customerRelationsList);
        return $this->fetch();
    }

    /**
     * 测试页面
     * @return mixed
     */
    public function letu_picker_user(){
        $lastTime = time();

        $params = $this->request->param();
        if(isset($params['add'])){
            $add = $params['add'];
        }

        $params['add_uid'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new CrmCustomerLogic();
        $list = $logic->paginate($params);

        $this->assign('list',$list);
        $this->assign('lastTime',$lastTime);
        $this->assign('params',$params);

        return $this->fetch();
    }

    /**
     * 测试页面
     * @return mixed
     */
    public function letu_picker_city(){
        $customerRelationsList = config('customer_relations');
        $this->assign('customerRelationsList',$customerRelationsList);
        return $this->fetch();
    }

    /**
     * 获取行业数据
     * @return \think\Response
     */
    public function industry_list(){
        $result = new Result();
        $result->success = false;
        $result->msg = '获取数据失败！';

        $mapVo = new Industry();

        $logic = new IndustryLogic();
        $list = $logic->industryList($mapVo);
        if($list){
            $result->success = true;
            $result->msg = '获取数据成功！';
            $result->data = $list;
        }
        $result->dataType = Result::DATATYPE_ARRAY;

        return response($result,200,[],'json');
    }

    /**
     * 添加行业
     * @return \think\Response
     */
    public function industry_add(){
        $result = new Result();
        $result->success = false;
        $result->msg = '添加失败！';

        if(!$this->request->isPost()){
            $result->success = false;
            $result->msg = '非法操作！';

            return response($result,200,[],'json');
        }

        $time = time();

        $params = $this->request->post();

        if(trim($params['industry_name']) == ''){
            $result->success = false;
            $result->msg = '行业名称不能为空！';

            return response($result,200,[],'json');
        }

        if(trim($params['name_first_letter']) == ''){
            $result->success = false;
            $result->msg = '名称首字母不能为空！';

            return response($result,200,[],'json');
        }

        $vo = new Industry();
        $vo->setIndustryName($params['industry_name']);
        $vo->setNameFirstLetter(strtoupper($params['name_first_letter']));
        $vo->setAddUid($this->getUserId());
        $vo->setEditUid($this->getUserId());
        $vo->setAddTime($time);
        $vo->setEditTime($time);

        $logic = new IndustryLogic();
        $result = $logic->add($vo);

        return response($result,200,[],'json');;
    }

    /**
     * 获取职位数据
     * @return \think\Response
     */
    public function position_list(){
        $result = new Result();
        $result->success = false;
        $result->msg = '获取数据失败！';

        $mapVo = new WorkPosition();

        $logic = new WorkPositionLogic();
        $list = $logic->positionList($mapVo);
        if($list){
            $result->success = true;
            $result->msg = '获取数据成功！';
            $result->data = $list;
        }
        $result->dataType = Result::DATATYPE_ARRAY;

        return response($result,200,[],'json');
    }

    /**
     * 添加职位
     * @return \think\Response
     */
    public function position_add(){
        $result = new Result();
        $result->success = false;
        $result->msg = '添加失败！';

        if(!$this->request->isPost()){
            $result->success = false;
            $result->msg = '非法操作！';

            return response($result,200,[],'json');
        }

        $time = time();

        $params = $this->request->post();

        if(trim($params['position_name']) == ''){
            $result->success = false;
            $result->msg = '职位名称不能为空！';

            return response($result,200,[],'json');
        }

        if(trim($params['name_first_letter']) == ''){
            $result->success = false;
            $result->msg = '名称首字母不能为空！';

            return response($result,200,[],'json');
        }

        $vo = new WorkPosition();
        $vo->setPositionName($params['position_name']);
        $vo->setNameFirstLetter(strtoupper($params['name_first_letter']));
        $vo->setAddUid($this->getUserId());
        $vo->setEditUid($this->getUserId());
        $vo->setAddTime($time);
        $vo->setEditTime($time);

        $logic = new WorkPositionLogic();
        $result = $logic->add($vo);

        return response($result,200,[],'json');;
    }

    /**
     * 获取岗位数据
     * @return \think\Response
     */
    public function post_list(){
        $result = new Result();
        $result->success = false;
        $result->msg = '获取数据失败！';

        $mapVo = new WorkPost();

        $logic = new WorkPostLogic();
        $list = $logic->postList($mapVo);
        if($list){
            $result->success = true;
            $result->msg = '获取数据成功！';
            $result->data = $list;
        }
        $result->dataType = Result::DATATYPE_ARRAY;

        return response($result,200,[],'json');
    }

    /**
     * 添加岗位
     * @return \think\Response
     */
    public function post_add(){
        $result = new Result();
        $result->success = false;
        $result->msg = '添加失败！';

        if(!$this->request->isPost()){
            $result->success = false;
            $result->msg = '非法操作！';

            return response($result,200,[],'json');
        }

        $time = time();

        $params = $this->request->post();

        if(trim($params['post_name']) == ''){
            $result->success = false;
            $result->msg = '岗位名称不能为空！';

            return response($result,200,[],'json');
        }

        if(trim($params['name_first_letter']) == ''){
            $result->success = false;
            $result->msg = '名称首字母不能为空！';

            return response($result,200,[],'json');
        }

        $vo = new WorkPost();
        $vo->setPostName($params['post_name']);
        $vo->setNameFirstLetter(strtoupper($params['name_first_letter']));
        $vo->setAddUid($this->getUserId());
        $vo->setEditUid($this->getUserId());
        $vo->setAddTime($time);
        $vo->setEditTime($time);

        $logic = new WorkPostLogic();
        $result = $logic->add($vo);

        return response($result,200,[],'json');;
    }

    /**
     * 获取专业数据
     * @return \think\Response
     */
    public function specialty_list(){
        $result = new Result();
        $result->success = false;
        $result->msg = '获取数据失败！';

        $mapVo = new Specialty();

        $logic = new SpecialtyLogic();
        $list = $logic->specialtyList($mapVo);
        if($list){
            $result->success = true;
            $result->msg = '获取数据成功！';
            $result->data = $list;
        }
        $result->dataType = Result::DATATYPE_ARRAY;

        return response($result,200,[],'json');
    }

    /**
     * 添加专业
     * @return \think\Response
     */
    public function specialty_add(){
        $result = new Result();
        $result->success = false;
        $result->msg = '添加失败！';

        if(!$this->request->isPost()){
            $result->success = false;
            $result->msg = '非法操作！';

            return response($result,200,[],'json');
        }

        $time = time();

        $params = $this->request->post();

        if(trim($params['specialty_name']) == ''){
            $result->success = false;
            $result->msg = '专业名称不能为空！';

            return response($result,200,[],'json');
        }

        if(trim($params['name_first_letter']) == ''){
            $result->success = false;
            $result->msg = '名称首字母不能为空！';

            return response($result,200,[],'json');
        }

        $vo = new Specialty();
        $vo->setSpecialtyName($params['specialty_name']);
        $vo->setNameFirstLetter(strtoupper($params['name_first_letter']));
        $vo->setAddUid($this->getUserId());
        $vo->setEditUid($this->getUserId());
        $vo->setAddTime($time);
        $vo->setEditTime($time);

        $logic = new SpecialtyLogic();
        $result = $logic->add($vo);

        return response($result,200,[],'json');;
    }

    /**
     * 获取学校数据
     * @return \think\Response
     */
    public function school_list(){
        $result = new Result();
        $result->success = false;
        $result->msg = '获取数据失败！';

        $mapVo = new School();

        $logic = new SchoolLogic();
        $list = $logic->schoolList($mapVo);
        if($list){
            $result->success = true;
            $result->msg = '获取数据成功！';
            $result->data = $list;
        }
        $result->dataType = Result::DATATYPE_ARRAY;

        return response($result,200,[],'json');
    }

    /**
     * 添加学校
     * @return \think\Response
     */
    public function school_add(){
        $result = new Result();
        $result->success = false;
        $result->msg = '添加失败！';

        if(!$this->request->isPost()){
            $result->success = false;
            $result->msg = '非法操作！';

            return response($result,200,[],'json');
        }

        $time = time();

        $params = $this->request->post();

        if(trim($params['school_name']) == ''){
            $result->success = false;
            $result->msg = '学校名称不能为空！';

            return response($result,200,[],'json');
        }

        if(trim($params['name_first_letter']) == ''){
            $result->success = false;
            $result->msg = '名称首字母不能为空！';

            return response($result,200,[],'json');
        }

        $vo = new School();
        $vo->setSchoolName($params['school_name']);
        $vo->setNameFirstLetter(strtoupper($params['name_first_letter']));
        $vo->setAddUid($this->getUserId());
        $vo->setEditUid($this->getUserId());
        $vo->setAddTime($time);
        $vo->setEditTime($time);

        $logic = new SchoolLogic();
        $result = $logic->add($vo);

        return response($result,200,[],'json');;
    }

    /**
     * 获取省份城市数据
     * @return \think\Response
     */
    public function city_list(){
        $result = new Result();
        $result->success = false;
        $result->msg = '获取数据失败！';

        $parent_id = 0;
//        $pcity_level = 1;

        $params = $this->request->param();
        if(isset($params['pid'])){
            $parent_id = $params['pid'];
        }

        $mapVo = new ProvinceCity();
        $mapVo->setParentId($parent_id);

        $logic = new ProvinceCityLogic();
        $list = $logic->provinceCityList($mapVo);
        if($list){
            $result->success = true;
            $result->msg = '获取数据成功！';
            $result->data = $list;
        }
        $result->dataType = Result::DATATYPE_ARRAY;

        return response($result,200,[],'json');
    }

    /**
     * 获取客户列表数据
     * @return \think\Response
     */
    public function customer_list(){
        $result = new Result();
        $result->success = false;
        $result->msg = '获取数据失败！';

        $params = $this->request->param();
        $idList = array();
        if(isset($params['ids'])){
            $idList = array_filter(explode(',',$params['ids']));
        }

        $mapVo = new CrmCustomer();
        $mapVo->setAddUid($this->getUserId());

        $logic = new CrmCustomerLogic();
        $list = $logic->customerListShortFields($mapVo);
        if($list){
            $data['listLeft'] = array();
            $data['listRight'] = array();

            $length = count($list);
            for($i=0;$i<$length;$i++){
                $item = &$list[$i];
                $item['regular_customer_text'] = config('app.regular_customer.'.$item['regular_customer']);
                unset($item['mobile']);
                unset($item['sex']);
                unset($item['aim_list']);
                if(in_array($item['customer_id'],$idList)){
                    array_push($data['listRight'],$item);
                }else{
                    array_push($data['listLeft'],$item);
                }
            }

            $result->success = true;
            $result->msg = '获取数据成功！';
            $result->data = $data;
        }
        $result->dataType = Result::DATATYPE_ARRAY;

        return response($result,200,[],'json');
    }
}