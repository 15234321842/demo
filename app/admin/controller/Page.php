<?php
namespace app\admin\controller;
use common\auth\LoadController;

class Page extends LoadController{
    public function _initialize(){
        
    }
    public function welcome1(){
        return $this->fetch();
    }
    public function welcome2(){
        return $this->fetch();
    }
    public function welcome3(){
        return $this->fetch();
    }
    public function menu(){
        return $this->fetch();
    }
    public function setting(){
        return $this->fetch();
    }
    public function table(){
        return $this->fetch();
    }
    public function form(){
        return $this->fetch();
    }
    public function form_step(){
        return $this->fetch();
    }
    public function login1(){
        return $this->fetch();
    }
    public function login2(){
        return $this->fetch();
    }
    public function login3(){
        return $this->fetch();
    }
    public function a404(){
        return $this->fetch();
    }
    public function button(){
        return $this->fetch();
    }
    public function layer(){
        return $this->fetch();
    }
    
    public function icon(){
        return $this->fetch();
    }
    public function icon_picker(){
        return $this->fetch();
    }
    public function color_select(){
        return $this->fetch();
    }
    public function table_select(){
        return $this->fetch();
    }
    public function upload(){
        return $this->fetch();
    }
    
    public function editor(){
        return $this->fetch();
    }
    public function area(){
        return $this->fetch();
    }
    
}