<?php
    namespace KMC\Tra;
    /**
        Trait，php5.4以后出现的一种复用php代码的方式，不能被实例化.也不是类，可以理解为把类里面的部分统一代码提取了出来 , 同名方法的优先级:子类>Trait类>父类
    *
    *
        TModel 介绍，将模型Model中的一常用些代码，提取出来，统一管理；
        说明：
        1.所有使用了TModel的子类必需继承 Model；
        2.为了增加TModel代码的通用性，增加了hooks方式，进行埋点钩子处理
        3.注意，子类中的同名方法会直接覆盖Trait中的同名方法，Trait中的方法会直接覆盖父类中的同名方法
        4.放入Trait中的方法，必需是通用的，有意义的
    */
    //TModel
    trait TModel{

        //验证传入参数
        public function validate($data,$required=[],$allowEmpty=[]){
            $data = $this->hooks('_beforeValidate',$data);
            $require = $format =  array();
            foreach($required as $r){
                !isset($data[$r]) && $require[$r] = 1;
                !in_array($r,$allowEmpty) && empty($data[$r]) && $require[$r] = 1;
            }

            //默认只验证fields中的字段
            foreach($this->fields as $_f){
                $data[$_f] && !$this->regex($data[$_f],$this->_getFormat($_f))
                && $format[$_f] = 1;
            }
            if(!empty($require) || !empty($format)){
                $this->error = array('require'=>$require,'format'=>$format,'info'=>'验证失败','status'=>0);
                return false;
            }
            $data = parent::validate($data);
            return $this->hooks('_afterValidate',$data);
        }

        //根据ID获取单条资源详细信息
        public function getDetail($id){
            $ckey = $this->cacheKey.$id;
            if( $sc = static_cache($ckey) ){
                return $sc;
            }
            $data = cache($ckey);
            if($data===false){
                $map[$this->getPk()] = $id;
                $data = $this->where($map)->find();
                if($data){
                    $data = $this->hooks('_setDetail',$data);   
                }
                cache($ckey,$data);
            }
            $data && $data = $this->hooks('_afterGetDetail',$data);
            static_cache($ckey,$data);
            return $data;
        }

        //根据ID获取多条资源的信息
        public function getDetailByIds($ids){
            if(empty($ids)) return false;
            !is_array($ids) && $ids = explode(',', $ids);
            $cache_list = cache([$this->cacheKey,$ids]);
            $return  = array();
            foreach($ids as $v){
                $return[$v] =  !$cache_list[$v] ? $this->getDetail($v) : $cache_list[$v];
                if(!empty($cache_list[$v])){
                    //对于缓存外的数据，调用钩子方法设置
                    $return[$v] = $this->hooks('_afterGetDetail',$return[$v]);
                }
            }
            return $return;
        }

        //根据ID列表，批量返回格式化后的数据
        public function simpleIds($ids,$fields=[]){
            if(empty($ids)){
                return array();
            }
            !is_array($ids) && $ids = explode(',',$ids);
            $data = $this->getDetailByIds($ids);
            $res = array();
            //根据传入的顺序，返回数据
            foreach($ids as $_id){
                $item = $data[$_id];
                foreach($fields as $field){
                    $res[$item[$this->getPk()]][$field] = $item[$field];
                }
            }
            return $res;
        }

        //根据ID，返回一条格式化后的数据
        public function simple($id,$fields=[]){
            $data = $this->getDetail($id);
            foreach($fields as $field){
                $res[$field] = $data[$field];
            }
            return $res;
        }

        //根据ID，返回固定格式的数据
        public function getSourceInfo($id,$detail=[]){
            $skey = $this->cacheKey.$id.'_s';
            if($sc = static_cache($skey)){
                return $sc;
            }
            empty($detail) && $detail = $this->getDetail($id);
            $info['id'] = $id;
            $info['ctime'] = $detail['ctime'];
            $info['url']= U($this->appName.'/'.$this->modelName.'/detail',['id'=>$id]);
            $info['title'] = $detail['uname'];
            $info['is_del'] = $detail['is_del'];
            $info['model_name'] = $this->modelName;
            $info['app_name'] = $this->appName;
            $info['user_info'] = $this->simple($detail['uid'],['uid','uname','avatar_middle']);
            $info['tags'] = [];
            $info = $this->hooks('_setSourceInfo',$info,$detail);
            static_cache($skey,$info);
            return $info;
        }  


        //获取不分页的数据
        //注意传入参数字段是否正确
        public function getList($map=[],$order='',$limit=10,$fields='*'){
            $pk = $this->getPk();
            empty($order) && $order = "{$pk} desc";
            $ids = $this->where($map)->order($order)->field($fields)->limit($limit)->getFieldAsArray($pk);
            return $fields =='*'
                    ?$this->getDetailByIds($ids)
                    :$this->simpleIds($ids,explode(',',$fields));
        }

        //获取分页的数据
        //注意返回的参数字段是否正确
        public function getListPage($map=[],$order='',$limit=10,$fields='*'){
            $pk = $this->getPk();
            empty($order) && $order = "{$pk} desc";
            $data = $this->where($map)->order($order)->field($pk)->findPage($limit);

            foreach($data['data'] as $key=>$v){
                $data['data'][$key] = $fields =='*'
                    ?$this->getDetail($v[$pk])
                    :$this->simple($v[$pk],explode(',',$fields));
            }
            return $data;
        }

        //删除操作
        public function doDelete($id = null){
            $pk = $this->getPk();
            !$id && $id = $this->request->post[$pk];
            if( !$this->hooks('_beforeDoDelete',$id)){
                return false;
            }
            if(!$id) return false;
            $map[$pk] = $id;
            $save['is_del'] = 1;
            $this->where($map)->save($save);
            $this->cleanCache($id);
            return $this->hooks('_afterDoDelete',$id);
        }

        //批量方法
        public function doDeleteByIds($ids){
           foreach($ids as $id){
             $this->doDelete($id);
           }
        }

        //恢复资源
        public function doReback($id = null){
            $pk = $this->getPk();
            !$id && $id = $this->request->post[$pk];
            if( !$this->hooks('_beforeDoReback',$id)){
                return false;
            }
            if(!$id) return false;
            $map[$pk] = $id;
            $save['is_del'] = 0;
            $this->where($map)->save($save);
            $this->cleanCache($id);
            return $this->hooks('_afterDoReback',$id);
        }

        //批量方法
        public function doRebackByIds($ids){
           foreach($ids as $id){
             $this->doReback($id);
           }
        }

        //真的删除
        public function doTrueDelete($id = null){
            $pk = $this->getPk();
            !$id && $id = $this->request->post[$pk];
            if( !$this->hooks('_beforeDoTrueDelete',$id)){
                return false;
            }
            if(!$id) return false;
            $map[$pk] = $id;
            $this->where($map)->delete();
            $this->cleanCache($id);
            $this->hooks('_afterDoTrueDelete',$id);
        }

        //批量方法
        public function doTrueDeleteByIds($ids){
            foreach($ids as $id){
                $this->doTrueDelete($id);
            }
        }

        //如果需要，就实现这个方法
        public function doAudit($id = null,$is_audit=null){
            $pk = $this->getPk();
            !$id && $id = $this->request->post[$pk];
            !$is_audit && $is_audit = $this->request->post['is_audit'];
            if( !$this->hooks('_beforeDoAudit',$id)){
                return false;
            }
            if(!$id) return false;
            $map[$pk] = $id;
            $save['is_audit'] = $is_audit;
            $this->where($map)->save($save);
            $this->cleanCache($id);
            $this->hooks('_afterDoAudit',$id);
            return true;
        }

        //批量方法
        public function doAuditByIds($ids){
            foreach($ids as $id){
                $this->doAudit($id);
            }
        }


         //清空缓存
        public function cleanCache($ids){
            !is_array($ids) && $ids = explode(',',$ids);
            foreach($ids as $id){
                cache($this->cacheKey.$id,null);
            }
            $this->hooks('_afterCleanCache',$ids);
        }

        //获取字段类型
        public function _getFormat($key){
            //不存在的格式，默认为数字类型
            return isset($this->fieldsFormat[$key]) ? $this->fieldsFormat[$key]:'number';
        }

        /**
            钩子方法,针对数据进行特殊处理 (PHP5.6以上可用)
            说明1：默认的钩子格式，有以下三种,建议熟读当前Trait,记住钩子位置
            _beforeXxxxx 在传输传入时，进行数据处理
            _afterXxxxx 在数据返回前执行
            _setXxxxx   可以方法中的任意位置执行，只要有对应钩子就好
            说明2：如果没有方法，则直接返回第一个参数
        */
        public function hooks($method,...$args){
            if(method_exists($this,$method)){
                return $this->$method(...$args);
            }else{
                return $args[0];
            }
        }
        
    } 
