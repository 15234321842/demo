<?php
/**
 * 应用控制器 App 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-07
 * Time: 10:16:00
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace app\houtai\controller;


class App extends Base {
    public function index(){
        return $this->fetch();
    }
}