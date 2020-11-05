<?php
/**
* 客户资料 Model 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-05
* Time: 23:51:35
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\model;

class CrmCustomer
{
    const TABLE_NAME = 'crm_customer';
    const PRIMARY_KEY = 'customer_id';

    /**
    * 设置字段-值集合
    */
    private $set_data_list = array();

    private $customer_id;
    private $customer_name;
    private $mobile;
    private $sex;
    private $stature;
    private $weight;
    private $somatotype;
    private $marital_status;
    private $birthdate;
    private $customer_relations;
    private $customer_class;
    private $industry_id;
    private $position_id;
    private $post_id;
    private $education_id;
    private $specialty_id;
    private $school_id;
    private $birth_province_id;
    private $birth_city_id;
    private $birth_region_id;
    private $birth_province_name;
    private $birth_city_name;
    private $birth_region_name;
    private $birth_address;
    private $reside_province_id;
    private $reside_city_id;
    private $reside_region_id;
    private $reside_province_name;
    private $reside_city_name;
    private $reside_region_name;
    private $reside_address;
    private $company_name;
    private $office_address;
    private $office_phone;
    private $fax;
    private $company_site;
    private $person_site;
    private $company_email;
    private $person_email;
    private $hobbies_ids;
    private $customer_detail;
    private $add_uid;
    private $add_time;
    private $edit_uid;
    private $edit_time;
    private $is_del;
    private $regular_customer;
    private $aim_list;
    
    /**
    * 字段属性 - 客户ID
    * @return $customer_id
    */
    public function getCustomerId(){
        return $this->customer_id;
    }
    
    /**
    * 字段属性 - 客户名称
    * @return $customer_name
    */
    public function getCustomerName(){
        return $this->customer_name;
    }
    
    /**
    * 字段属性 - 手机号：多个手机号用","分隔
    * @return $mobile
    */
    public function getMobile(){
        return $this->mobile;
    }
    
    /**
    * 字段属性 - 性别：0 女 1 男 2 保密
    * @return $sex
    */
    public function getSex(){
        return $this->sex;
    }
    
    /**
    * 字段属性 - 身高：cm
    * @return $stature
    */
    public function getStature(){
        return $this->stature;
    }
    
    /**
    * 字段属性 - 体重：kg
    * @return $weight
    */
    public function getWeight(){
        return $this->weight;
    }
    
    /**
    * 字段属性 - 体型：0 标准  1 微瘦 2 瘦小 3 偏瘦  4 魁梧 5 微胖 6 偏胖
    * @return $somatotype
    */
    public function getSomatotype(){
        return $this->somatotype;
    }
    
    /**
    * 字段属性 - 婚姻状况：0 单身 1 已婚 2 离异 3 再婚 4 丧偶
    * @return $marital_status
    */
    public function getMaritalStatus(){
        return $this->marital_status;
    }
    
    /**
    * 字段属性 - 出生日期
    * @return $birthdate
    */
    public function getBirthdate(){
        return $this->birthdate;
    }
    
    /**
    * 字段属性 - 客户关系：0 陌生 1 老客 2 同事 3 同学 4 朋友 5 亲戚 6 亲人 7 家人
    * @return $customer_relations
    */
    public function getCustomerRelations(){
        return $this->customer_relations;
    }
    
    /**
    * 字段属性 - 客户分类：A,B,C,D
    * @return $customer_class
    */
    public function getCustomerClass(){
        return $this->customer_class;
    }
    
    /**
    * 字段属性 - 所属行业
    * @return $industry_id
    */
    public function getIndustryId(){
        return $this->industry_id;
    }
    
    /**
    * 字段属性 - 职位ID
    * @return $position_id
    */
    public function getPositionId(){
        return $this->position_id;
    }
    
    /**
    * 字段属性 - 岗位ID
    * @return $post_id
    */
    public function getPostId(){
        return $this->post_id;
    }
    
    /**
    * 字段属性 - 学历：0 不详 1 小学 2 初中 3 高中 4 中专 5 大专 6 本科 7 研究生 8 博士 9 博士后
    * @return $education_id
    */
    public function getEducationId(){
        return $this->education_id;
    }
    
    /**
    * 字段属性 - 专业
    * @return $specialty_id
    */
    public function getSpecialtyId(){
        return $this->specialty_id;
    }
    
    /**
    * 字段属性 - 毕业学校
    * @return $school_id
    */
    public function getSchoolId(){
        return $this->school_id;
    }
    
    /**
    * 字段属性 - 出生省份ID
    * @return $birth_province_id
    */
    public function getBirthProvinceId(){
        return $this->birth_province_id;
    }
    
    /**
    * 字段属性 - 出生城市ID
    * @return $birth_city_id
    */
    public function getBirthCityId(){
        return $this->birth_city_id;
    }
    
    /**
    * 字段属性 - 出生区域ID
    * @return $birth_region_id
    */
    public function getBirthRegionId(){
        return $this->birth_region_id;
    }
    
    /**
    * 字段属性 - 出生省份名称
    * @return $birth_province_name
    */
    public function getBirthProvinceName(){
        return $this->birth_province_name;
    }
    
    /**
    * 字段属性 - 出生城市名称
    * @return $birth_city_name
    */
    public function getBirthCityName(){
        return $this->birth_city_name;
    }
    
    /**
    * 字段属性 - 出生区域名称
    * @return $birth_region_name
    */
    public function getBirthRegionName(){
        return $this->birth_region_name;
    }
    
    /**
    * 字段属性 - 出生住址
    * @return $birth_address
    */
    public function getBirthAddress(){
        return $this->birth_address;
    }
    
    /**
    * 字段属性 - 居住省份ID
    * @return $reside_province_id
    */
    public function getResideProvinceId(){
        return $this->reside_province_id;
    }
    
    /**
    * 字段属性 - 居住城市ID
    * @return $reside_city_id
    */
    public function getResideCityId(){
        return $this->reside_city_id;
    }
    
    /**
    * 字段属性 - 居住区域ID
    * @return $reside_region_id
    */
    public function getResideRegionId(){
        return $this->reside_region_id;
    }
    
    /**
    * 字段属性 - 居住省份名称
    * @return $reside_province_name
    */
    public function getResideProvinceName(){
        return $this->reside_province_name;
    }
    
    /**
    * 字段属性 - 居住城市名称
    * @return $reside_city_name
    */
    public function getResideCityName(){
        return $this->reside_city_name;
    }
    
    /**
    * 字段属性 - 居住区域名称
    * @return $reside_region_name
    */
    public function getResideRegionName(){
        return $this->reside_region_name;
    }
    
    /**
    * 字段属性 - 居住住址
    * @return $reside_address
    */
    public function getResideAddress(){
        return $this->reside_address;
    }
    
    /**
    * 字段属性 - 企业名称
    * @return $company_name
    */
    public function getCompanyName(){
        return $this->company_name;
    }
    
    /**
    * 字段属性 - 办公地址
    * @return $office_address
    */
    public function getOfficeAddress(){
        return $this->office_address;
    }
    
    /**
    * 字段属性 - 办公电话
    * @return $office_phone
    */
    public function getOfficePhone(){
        return $this->office_phone;
    }
    
    /**
    * 字段属性 - 传真号码
    * @return $fax
    */
    public function getFax(){
        return $this->fax;
    }
    
    /**
    * 字段属性 - 企业网址
    * @return $company_site
    */
    public function getCompanySite(){
        return $this->company_site;
    }
    
    /**
    * 字段属性 - 个人网址
    * @return $person_site
    */
    public function getPersonSite(){
        return $this->person_site;
    }
    
    /**
    * 字段属性 - 企业邮箱
    * @return $company_email
    */
    public function getCompanyEmail(){
        return $this->company_email;
    }
    
    /**
    * 字段属性 - 个人邮箱
    * @return $person_email
    */
    public function getPersonEmail(){
        return $this->person_email;
    }
    
    /**
    * 字段属性 - 兴趣爱好：多个有","分隔
    * @return $hobbies_ids
    */
    public function getHobbiesIds(){
        return $this->hobbies_ids;
    }
    
    /**
    * 字段属性 - 客户详情
    * @return $customer_detail
    */
    public function getCustomerDetail(){
        return $this->customer_detail;
    }
    
    /**
    * 字段属性 - 添加用户ID
    * @return $add_uid
    */
    public function getAddUid(){
        return $this->add_uid;
    }
    
    /**
    * 字段属性 - 添加时间
    * @return $add_time
    */
    public function getAddTime(){
        return $this->add_time;
    }
    
    /**
    * 字段属性 - 编辑用户ID
    * @return $edit_uid
    */
    public function getEditUid(){
        return $this->edit_uid;
    }
    
    /**
    * 字段属性 - 编辑时间
    * @return $edit_time
    */
    public function getEditTime(){
        return $this->edit_time;
    }
    
    /**
    * 字段属性 - 是否删除：0 正常 1 删除
    * @return $is_del
    */
    public function getIsDel(){
        return $this->is_del;
    }
    
    /**
    * 字段属性 - 新老客户：0 新 1 老
    * @return $regular_customer
    */
    public function getRegularCustomer(){
        return $this->regular_customer;
    }
    
    /**
    * 字段属性 - 目标名单：0 否 1 是
    * @return $aim_list
    */
    public function getAimList(){
        return $this->aim_list;
    }
    
    /**
    * 字段方法 - 客户ID
    * @param $customer_id
    * @return void
    */
    public function setCustomerId($customer_id){
        $this->customer_id = $customer_id;
        $this->set_data_list['customer_id'] = &$this->customer_id;
    }
    
    /**
    * 字段方法 - 客户名称
    * @param $customer_name
    * @return void
    */
    public function setCustomerName($customer_name){
        $this->customer_name = $customer_name;
        $this->set_data_list['customer_name'] = &$this->customer_name;
    }
    
    /**
    * 字段方法 - 手机号：多个手机号用","分隔
    * @param $mobile
    * @return void
    */
    public function setMobile($mobile){
        $this->mobile = $mobile;
        $this->set_data_list['mobile'] = &$this->mobile;
    }
    
    /**
    * 字段方法 - 性别：0 女 1 男 2 保密
    * @param $sex
    * @return void
    */
    public function setSex($sex){
        $this->sex = $sex;
        $this->set_data_list['sex'] = &$this->sex;
    }
    
    /**
    * 字段方法 - 身高：cm
    * @param $stature
    * @return void
    */
    public function setStature($stature){
        $this->stature = $stature;
        $this->set_data_list['stature'] = &$this->stature;
    }
    
    /**
    * 字段方法 - 体重：kg
    * @param $weight
    * @return void
    */
    public function setWeight($weight){
        $this->weight = $weight;
        $this->set_data_list['weight'] = &$this->weight;
    }
    
    /**
    * 字段方法 - 体型：0 标准  1 微瘦 2 瘦小 3 偏瘦  4 魁梧 5 微胖 6 偏胖
    * @param $somatotype
    * @return void
    */
    public function setSomatotype($somatotype){
        $this->somatotype = $somatotype;
        $this->set_data_list['somatotype'] = &$this->somatotype;
    }
    
    /**
    * 字段方法 - 婚姻状况：0 单身 1 已婚 2 离异 3 再婚 4 丧偶
    * @param $marital_status
    * @return void
    */
    public function setMaritalStatus($marital_status){
        $this->marital_status = $marital_status;
        $this->set_data_list['marital_status'] = &$this->marital_status;
    }
    
    /**
    * 字段方法 - 出生日期
    * @param $birthdate
    * @return void
    */
    public function setBirthdate($birthdate){
        $this->birthdate = $birthdate;
        $this->set_data_list['birthdate'] = &$this->birthdate;
    }
    
    /**
    * 字段方法 - 客户关系：0 陌生 1 老客 2 同事 3 同学 4 朋友 5 亲戚 6 亲人 7 家人
    * @param $customer_relations
    * @return void
    */
    public function setCustomerRelations($customer_relations){
        $this->customer_relations = $customer_relations;
        $this->set_data_list['customer_relations'] = &$this->customer_relations;
    }
    
    /**
    * 字段方法 - 客户分类：A,B,C,D
    * @param $customer_class
    * @return void
    */
    public function setCustomerClass($customer_class){
        $this->customer_class = $customer_class;
        $this->set_data_list['customer_class'] = &$this->customer_class;
    }
    
    /**
    * 字段方法 - 所属行业
    * @param $industry_id
    * @return void
    */
    public function setIndustryId($industry_id){
        $this->industry_id = $industry_id;
        $this->set_data_list['industry_id'] = &$this->industry_id;
    }
    
    /**
    * 字段方法 - 职位ID
    * @param $position_id
    * @return void
    */
    public function setPositionId($position_id){
        $this->position_id = $position_id;
        $this->set_data_list['position_id'] = &$this->position_id;
    }
    
    /**
    * 字段方法 - 岗位ID
    * @param $post_id
    * @return void
    */
    public function setPostId($post_id){
        $this->post_id = $post_id;
        $this->set_data_list['post_id'] = &$this->post_id;
    }
    
    /**
    * 字段方法 - 学历：0 不详 1 小学 2 初中 3 高中 4 中专 5 大专 6 本科 7 研究生 8 博士 9 博士后
    * @param $education_id
    * @return void
    */
    public function setEducationId($education_id){
        $this->education_id = $education_id;
        $this->set_data_list['education_id'] = &$this->education_id;
    }
    
    /**
    * 字段方法 - 专业
    * @param $specialty_id
    * @return void
    */
    public function setSpecialtyId($specialty_id){
        $this->specialty_id = $specialty_id;
        $this->set_data_list['specialty_id'] = &$this->specialty_id;
    }
    
    /**
    * 字段方法 - 毕业学校
    * @param $school_id
    * @return void
    */
    public function setSchoolId($school_id){
        $this->school_id = $school_id;
        $this->set_data_list['school_id'] = &$this->school_id;
    }
    
    /**
    * 字段方法 - 出生省份ID
    * @param $birth_province_id
    * @return void
    */
    public function setBirthProvinceId($birth_province_id){
        $this->birth_province_id = $birth_province_id;
        $this->set_data_list['birth_province_id'] = &$this->birth_province_id;
    }
    
    /**
    * 字段方法 - 出生城市ID
    * @param $birth_city_id
    * @return void
    */
    public function setBirthCityId($birth_city_id){
        $this->birth_city_id = $birth_city_id;
        $this->set_data_list['birth_city_id'] = &$this->birth_city_id;
    }
    
    /**
    * 字段方法 - 出生区域ID
    * @param $birth_region_id
    * @return void
    */
    public function setBirthRegionId($birth_region_id){
        $this->birth_region_id = $birth_region_id;
        $this->set_data_list['birth_region_id'] = &$this->birth_region_id;
    }
    
    /**
    * 字段方法 - 出生省份名称
    * @param $birth_province_name
    * @return void
    */
    public function setBirthProvinceName($birth_province_name){
        $this->birth_province_name = $birth_province_name;
        $this->set_data_list['birth_province_name'] = &$this->birth_province_name;
    }
    
    /**
    * 字段方法 - 出生城市名称
    * @param $birth_city_name
    * @return void
    */
    public function setBirthCityName($birth_city_name){
        $this->birth_city_name = $birth_city_name;
        $this->set_data_list['birth_city_name'] = &$this->birth_city_name;
    }
    
    /**
    * 字段方法 - 出生区域名称
    * @param $birth_region_name
    * @return void
    */
    public function setBirthRegionName($birth_region_name){
        $this->birth_region_name = $birth_region_name;
        $this->set_data_list['birth_region_name'] = &$this->birth_region_name;
    }
    
    /**
    * 字段方法 - 出生住址
    * @param $birth_address
    * @return void
    */
    public function setBirthAddress($birth_address){
        $this->birth_address = $birth_address;
        $this->set_data_list['birth_address'] = &$this->birth_address;
    }
    
    /**
    * 字段方法 - 居住省份ID
    * @param $reside_province_id
    * @return void
    */
    public function setResideProvinceId($reside_province_id){
        $this->reside_province_id = $reside_province_id;
        $this->set_data_list['reside_province_id'] = &$this->reside_province_id;
    }
    
    /**
    * 字段方法 - 居住城市ID
    * @param $reside_city_id
    * @return void
    */
    public function setResideCityId($reside_city_id){
        $this->reside_city_id = $reside_city_id;
        $this->set_data_list['reside_city_id'] = &$this->reside_city_id;
    }
    
    /**
    * 字段方法 - 居住区域ID
    * @param $reside_region_id
    * @return void
    */
    public function setResideRegionId($reside_region_id){
        $this->reside_region_id = $reside_region_id;
        $this->set_data_list['reside_region_id'] = &$this->reside_region_id;
    }
    
    /**
    * 字段方法 - 居住省份名称
    * @param $reside_province_name
    * @return void
    */
    public function setResideProvinceName($reside_province_name){
        $this->reside_province_name = $reside_province_name;
        $this->set_data_list['reside_province_name'] = &$this->reside_province_name;
    }
    
    /**
    * 字段方法 - 居住城市名称
    * @param $reside_city_name
    * @return void
    */
    public function setResideCityName($reside_city_name){
        $this->reside_city_name = $reside_city_name;
        $this->set_data_list['reside_city_name'] = &$this->reside_city_name;
    }
    
    /**
    * 字段方法 - 居住区域名称
    * @param $reside_region_name
    * @return void
    */
    public function setResideRegionName($reside_region_name){
        $this->reside_region_name = $reside_region_name;
        $this->set_data_list['reside_region_name'] = &$this->reside_region_name;
    }
    
    /**
    * 字段方法 - 居住住址
    * @param $reside_address
    * @return void
    */
    public function setResideAddress($reside_address){
        $this->reside_address = $reside_address;
        $this->set_data_list['reside_address'] = &$this->reside_address;
    }
    
    /**
    * 字段方法 - 企业名称
    * @param $company_name
    * @return void
    */
    public function setCompanyName($company_name){
        $this->company_name = $company_name;
        $this->set_data_list['company_name'] = &$this->company_name;
    }
    
    /**
    * 字段方法 - 办公地址
    * @param $office_address
    * @return void
    */
    public function setOfficeAddress($office_address){
        $this->office_address = $office_address;
        $this->set_data_list['office_address'] = &$this->office_address;
    }
    
    /**
    * 字段方法 - 办公电话
    * @param $office_phone
    * @return void
    */
    public function setOfficePhone($office_phone){
        $this->office_phone = $office_phone;
        $this->set_data_list['office_phone'] = &$this->office_phone;
    }
    
    /**
    * 字段方法 - 传真号码
    * @param $fax
    * @return void
    */
    public function setFax($fax){
        $this->fax = $fax;
        $this->set_data_list['fax'] = &$this->fax;
    }
    
    /**
    * 字段方法 - 企业网址
    * @param $company_site
    * @return void
    */
    public function setCompanySite($company_site){
        $this->company_site = $company_site;
        $this->set_data_list['company_site'] = &$this->company_site;
    }
    
    /**
    * 字段方法 - 个人网址
    * @param $person_site
    * @return void
    */
    public function setPersonSite($person_site){
        $this->person_site = $person_site;
        $this->set_data_list['person_site'] = &$this->person_site;
    }
    
    /**
    * 字段方法 - 企业邮箱
    * @param $company_email
    * @return void
    */
    public function setCompanyEmail($company_email){
        $this->company_email = $company_email;
        $this->set_data_list['company_email'] = &$this->company_email;
    }
    
    /**
    * 字段方法 - 个人邮箱
    * @param $person_email
    * @return void
    */
    public function setPersonEmail($person_email){
        $this->person_email = $person_email;
        $this->set_data_list['person_email'] = &$this->person_email;
    }
    
    /**
    * 字段方法 - 兴趣爱好：多个有","分隔
    * @param $hobbies_ids
    * @return void
    */
    public function setHobbiesIds($hobbies_ids){
        $this->hobbies_ids = $hobbies_ids;
        $this->set_data_list['hobbies_ids'] = &$this->hobbies_ids;
    }
    
    /**
    * 字段方法 - 客户详情
    * @param $customer_detail
    * @return void
    */
    public function setCustomerDetail($customer_detail){
        $this->customer_detail = $customer_detail;
        $this->set_data_list['customer_detail'] = &$this->customer_detail;
    }
    
    /**
    * 字段方法 - 添加用户ID
    * @param $add_uid
    * @return void
    */
    public function setAddUid($add_uid){
        $this->add_uid = $add_uid;
        $this->set_data_list['add_uid'] = &$this->add_uid;
    }
    
    /**
    * 字段方法 - 添加时间
    * @param $add_time
    * @return void
    */
    public function setAddTime($add_time){
        $this->add_time = $add_time;
        $this->set_data_list['add_time'] = &$this->add_time;
    }
    
    /**
    * 字段方法 - 编辑用户ID
    * @param $edit_uid
    * @return void
    */
    public function setEditUid($edit_uid){
        $this->edit_uid = $edit_uid;
        $this->set_data_list['edit_uid'] = &$this->edit_uid;
    }
    
    /**
    * 字段方法 - 编辑时间
    * @param $edit_time
    * @return void
    */
    public function setEditTime($edit_time){
        $this->edit_time = $edit_time;
        $this->set_data_list['edit_time'] = &$this->edit_time;
    }
    
    /**
    * 字段方法 - 是否删除：0 正常 1 删除
    * @param $is_del
    * @return void
    */
    public function setIsDel($is_del){
        $this->is_del = $is_del;
        $this->set_data_list['is_del'] = &$this->is_del;
    }
    
    /**
    * 字段方法 - 新老客户：0 新 1 老
    * @param $regular_customer
    * @return void
    */
    public function setRegularCustomer($regular_customer){
        $this->regular_customer = $regular_customer;
        $this->set_data_list['regular_customer'] = &$this->regular_customer;
    }
    
    /**
    * 字段方法 - 目标名单：0 否 1 是
    * @param $aim_list
    * @return void
    */
    public function setAimList($aim_list){
        $this->aim_list = $aim_list;
        $this->set_data_list['aim_list'] = &$this->aim_list;
    }
    
    /**
    * 获取设置字段-值集合，标记添加、更新的字段集合
    */
    public function getSetDataList(){
        return $this->set_data_list;
    }

    /**
    * 清空设置字段-值集合
    */
    public function clearSetDataList(){
        $this->set_data_list = array();
    }
}