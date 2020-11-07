<?php
namespace app\admin\controller;
use common\auth\LoadController;

class Index extends LoadController{
    public function index(){
        return $this->fetch();
    }

}