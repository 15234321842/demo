<?php 
	//+----------------------------------------------------------------------
	// | QimingDao 
	// +----------------------------------------------------------------------
	// | Copyright (c) 2012 http://www.qimingdao.cn All rights reserved.
	// +----------------------------------------------------------------------
	// | Author: lbm <lbm@yeah.net>
	// +----------------------------------------------------------------------
	// 
	
	/**
	 +------------------------------------------------------------------------------
	 * 无限极分类控制模型 
	 +------------------------------------------------------------------------------
	 * 
	 * @example $ctree = model('CateTree',$table);	//表名必须指定
	 			$ctree = $ctree->setField($param);	//不指定字段名将使用默认字段
	 			$data = $ctree->getTree();                              
	 * @author    lbm <lbm@yeah.net> 
	 * @version   1.0
	 +------------------------------------------------------------------------------
	 */
	/**
	 * 
	 * @author lbm
	 * 
	 * 说明：利用此分类模型，需要指定数据表,相关模型字段。
	 * 必须属性：
	 *  table => 表名
     *	id    => 主键	字段名 
     *	name  => 分类名称   字段名
     *  pid   => 上级节点ID 字段名
     *  sort  => 排序 字段名
     *
	 *  demo:
	 *  $ctree = model('CateTree',$table);	//表名必须指定
	 *  $ctree = $ctree->setField($param);	//不指定字段名将使用默认字段
	 *  $data = $ctree->getTree();
	 *
	 *
	 */

	
	namespace common\auth;

	use think\Db;
	use think\db\Expression;

	class CateTree {

		private static $tree  = array(); 	//树
		private static $hash  = array(); 	//树的HASH表
		public $cacheKey 	  = ''; //缓存前缀
		private $cacheKeyPrefix  = 'CateTree_'; //缓存前缀
		private $_table	      = '';	//默认表
		private $defaultField = array(	'id'	=>'id', 	//id字段名
										'pid'	=>'pid', 	//pid字段名
										'name'	=>'name',	//name字段名
										'sort'	=>'sort',   //排序字段名称
									);
		private $has_del = false;

		public function __construct($table=''){
			if(empty($table)){
				$this->error='没有此库！';
				return false;
			}
			$this->_table = $table;
			$this->cacheKey = $this->cacheKeyPrefix.$table; 
		}
		
		public function setHasDel($has_del = false)
		{
			$this->has_del = $has_del;
			return $this;
		}

		public function setCacheKey($key){
			$this->cacheKey =$this->cacheKeyPrefix.$key;
			return $this;
		}
		public function setField($field = array()){
			if(empty($field)) return $this;
			foreach ($field as $k => $v) {
				$this->defaultField[$k]=$v;
			}	
			return $this;
		}
		
		/**
		 * 获取一棵树 
		 */
		public function getTree($rootId='0'){
			//减少缓存读取次数
			if(isset(self::$tree[$this->cacheKey])){	
				return self::$tree[$this->cacheKey][$rootId];
			}
			//重建树及hash缓存
			$this->createTree();
			return self::$tree[$this->cacheKey][$rootId];
		}
		/**
		 *  获取所有分类以主键为key的 hash 数组
		 * 
		 */
		public function getAllHash(){
			//重建树及hash缓存
			$this->createTree();
			return self::$hash[$this->cacheKey];
		}
		
		private $tempHashData = array();
		//获取所有子集的ID
		public function getChildHash($id){
			$data = $this->getTree($id);
			$this->_getChildHash($data);
			$r = $this->tempHashData;
			$this->tempHashData = array();
			return $r;
		}

		private function _getChildHash($data){
			!empty($data[$this->defaultField['id']]) && $this->tempHashData[] = $data[$this->defaultField['id']];
			if(!empty($data['_child'])){
				foreach($data['_child'] as $v){
					$this->_getChildHash($v);
				}
			}
		}
		/**
		 * 更改一个节点的父亲节点
		 * $data 表示其他需要保存的字段，由实现此model的类完成
		 */
		
		public function moveTree($id,$newPid,$newSort='255',$data = array()){
			
			if(!$id || (!$newPid && $newPid!=0)){
				$this->error  = L('PUBLIC_DATA_MOVE_ERROR');
				return false;
			}

			//更新数据库

			$map[$this->defaultField['id']] = $id;
			$data[$this->defaultField['pid']] = $newPid;
			$data[$this->defaultField['sort']] = $newSort;
			
			if(D('')->table($this->_table)->where($map)->save($data)){
				//TODO  可优化成更新缓存而不是清除缓存
				$this->cleanCache();
			}
			return true;
		}
		
		/**
		 * 批量移动树的节点 ids,newPids key值必须对应
		 * 移动规则 父节点不能移动到自己的子树下面去
		 * @param unknown_type $ids 
		 * @param unknown_type $newPids
		 * @param unknown_type $newSorts
		 */
		public function moveTreeByArr($ids,$newPids,$newSorts = array()){
			foreach($ids as $k=>$v){
				$sort = isset($newSorts[$k]) && !empty($newSorts[$k]) ? $newSorts[$k] : 255;
				$this->moveTree($v,$newPids[$k],$sort,false);
			}
			$this->cleanCache();
			return true;
		}
		
		/**
		 * 清除某个分类缓存 不允许删除单个子树（因为一整棵树是存在一个缓存里面的）
		 */
		
		public function cleanCache(){
			self::$tree = array();
			return true;
		}
		
		#####################################
		#									#
		#	下面为私有函数  一般不要去修改		#	
		#									#
		#####################################
		
		
		/**
		 * 双数组生成整棵树
		 */ 
		
		private function createTree(){
			//从数据库取树的数据 
			$data = $this->getTreeData();
			if(empty($data)){
				return array();	
			}
			//临时树
			$tree = array();
			//所有节点的子节点
			$child = array();
			//hash缓存数组
			$hash = array();
			foreach($data as $dv){
				$hash[$dv[$this->defaultField['id']]] = $dv;
				$tree[$dv[$this->defaultField['id']]] = $dv;
				!isset($child[$dv[$this->defaultField['id']]]) && $child[$dv[$this->defaultField['id']]] = array();
				$tree[$dv[$this->defaultField['id']]]['_child'] = &  $child[$dv[$this->defaultField['id']]];
				$child[$dv[$this->defaultField['pid']]][] = & $tree[$dv[$this->defaultField['id']]];		
			}

			$tree[0]['_child'] = $child[0]; //整个树 根节点ID=0
			
			self::$tree[$this->cacheKey] = $tree;
			self::$hash[$this->cacheKey] = $hash;
		}

		private function getTreeData(){
			// 下面是从数据库读取的 分类很少会有大量数据
			if($this->has_del){
				$map['is_del'] = 0;
				$list = Db($this->_table)->where($map)->order($this->defaultField['sort'].' asc,'.$this->defaultField['id'].' asc')->select();
			}else{
				$list = Db($this->_table)->order($this->defaultField['sort'].' asc,'.$this->defaultField['id'].' asc')->select();
			}
			return $list;
		}

	}