<?php
/**
 * Created by 比谷网络.
 * User: niuchao
 * Date: 2018-03-06
 * Time: 13:51
 * 一个测试接口的类
 */
namespace app\appapi\controller;

class Test extends BaseController
{

    /**
     * @return array
     * 测试接口方法test
     */
    public function test()
    {
        /**
         * 判断用户是否登录的方法
         */
//        $this->getUserId();//暂时注释

        /**
         * 组织数据输出
         */
        $out = [
            'code' => 1,
            'msg' => 1,
            'data' => APPAPI,
        ];
        /**
         * 在配置中已配置，直接return即可输出json格式
         */
        return $out;
    }

    /**
     * 测试基类的成功方法返回
     */
    public function success_return()
    {
        $this->success('成功了', ['a' => '1', 'b' => '2']);
    }

    /**
     * 测试基类中的失败方法返回
     */
    public function error_return()
    {
        $this->error('失败了');
    }
}