<?php
//+----------------------------------------------------------------------
// | QimingDao 
// +----------------------------------------------------------------------
// | Copyright (c) 2012 http://www.qimingcx.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: jason <yangjs17@yeah.net>
// +----------------------------------------------------------------------
// 

/**
 +------------------------------------------------------------------------------
 * 部门模型
 +------------------------------------------------------------------------------
 * 
 * @example [path|url] description               
 * @todo    description                           
 * @author    jason <yangjs17@yeah.net> 
 * @version   1.0
 +------------------------------------------------------------------------------
 */
namespace app\houtai\model;
use common\auth\Catetree;
use think\Db;
use common\dao\MyTalkScriptDao;
use think\Model;


class ClothingCate extends Model {
	protected $table = 'clothing_cate';

    private $tq;

	protected $treeDo;		// 树形对象

	// 初始化方法 - 建立对应的数据对象
	public function _initialize() {
        $this->tq = new MyTalkScriptDao();

		$field = array('id'=>'department_id','name'=>'title','pid'=>'parent_dept_id','sort'=>'display_order');
		
		$this->treeDo = new CateTree('clothing_cate');
		$this->treeDo->setField($field);
		$this->treeDo->setHasDel(true);
	}

	/**
	 * 获取问答部门树形结构
	 * @param int $pid 树形结构的父级ID
	 * @return array 树形结构数组
	 */
	public function getCategory($pid = 0) {
		$map['parent_dept_id'] = 0;
		$map['is_del'] = 0;
		$department_id = $this->where($map)->field('department_id')->find();
		$department_id = $department_id['department_id'];
		if($pid == 0){
			$pid = $department_id;
		}
		$data = $this->treeDo->getTree($pid);
		return $data;
	}

	/**
	 * 获取全部部门的Hash数组
	 * @return array 部门的Hash数组
	 */
	public function getAllHash() {
		$data = $this->treeDo->getAllHash();
		return $data;
	}

	//获取部门下的所有子部门
	public function getChildIds($cid = 0){
		return  $this->treeDo->getChildHash($cid);
	}

	//获取单个部门信息
	public function getCateInfo($cid = 1){
		$map['department_id'] = $cid;
		$info = $this->where($map)->find();
    	return  $info;
	}

	/**
	 * 获取指定部门的信息
	 * @param int $cid 部门ID
	 * @param boolean $child 是否显示子节点信息
	 * @return array 返回部门信息
	 */
    public function getCategoryData($cid = 0, $child = true) {

    	$info = $this->getCateInfo($cid);
    	$cateInfo['id'] = $cid;
    	$cateInfo['name'] = $info['title'];
    	$cateInfo['pid'] = $info['parent_dept_id'];
    	
    	if($child) {
    		$cmap['parent_dept_id'] = !empty($cid) ? $cid : 0;
    		$child = $this->where($cmap)->order('display_order asc')->field('department_id,title')->select();
    		$_child = [];
    		foreach ($child as $value) {
    			$_child[$value['department_id']] = $value['title'];
    		}
    		$cateInfo['_child'] = $_child;
    	}


    	return $cateInfo;
    }


    /**
     * 更具部门ID获取部门路径 = 两级路径
     * @param int $cid 部门ID
     * @param string $method ???
     * @return array 部门路径
     */
    public function getCatePathById($cid = 0) {
    	if($cid == 0) {
    		return array();
    	}
    	$data = array();
    	$cateInfo = $this->getCateInfo($cid);
    	$data[$cid] = $cateInfo['title'];
    	if($cateInfo['department_id'] != 0) {
    		$cateInfo = $this->getCateInfo($cateInfo['parent_dept_id']);
    		$data[$cateInfo['department_id']] = $cateInfo['title'];
    	}

    	return array_reverse($data, true);
    }

    public function getDepart($department_id = 0){
    	$department = $this->getCatePathAllById($department_id);
    	$sc = array_reverse($department);
    	return $sc;

    }
    // 根据部门ID获取部门上级全部路径
    public function getCatePathAllById($cid = 0) {
    	if($cid == 0) {
    		return array();
    	}
    	
    	$data = array();
    	$cateInfo = $this->getCateInfo($cid);

        $data[$cid] = $cateInfo['title'];
        
    	if($cateInfo['parent_dept_id'] != 0) {
    		$category = $this->getCatePathAllById($cateInfo['parent_dept_id']);
    		$data += $category;
    	}

    	return $data;
    }

    /**
     * 通过指定的部门ID，获取该部门ID的父级ID集合 - 全部
     * @param int $cid 部门ID
     * @return array 父级ID数组
     */
    public function getPidsAllById($cid = 0) {
    	
    	$cateInfo = $this->getCateInfo($cid);
    	$pids[] = $cateInfo['parent_dept_id'];
    	if($cateInfo['parent_dept_id'] != 0) {
    		$data = $this->getPidsAllById($cateInfo['parent_dept_id']);
    		$pids = array_merge($pids, $data);
    	}

    	return $pids;
    }

    /**
     * 通过部门ID判断该部门是否是有叶子节点
     * @param int $cid 部门ID
     * @return boolean 判断是否是叶子节点
     */
    public function isLeafNode($cid) {
    	$map['parent_dept_id'] = $cid;
    	$count = $this->where($map)->count();
    	$res = ($count > 0) ? false : true;
    	return $res;
    }

    

	/**
	 * 清理部门缓存
	 * @return void
	 */
	public function cleanCache() {
		$this->treeDo->cleanCache();
	}

	/**
	 * 添加一个新的部门
	 * @param array $data 部门的相关信息
	 * @return boolean 是否添加成功
	 */
	public function doAdd($data = []) {
		// 数据不能为空
		if(empty($data)) { return false; }
		// 部门名称不能为空
		if(empty($data['title'])) { return false; }
		// 添加部门操作
		$add['title'] = $data['title'];
		$add['parent_dept_id'] = intval($data['parent_dept_id']);
		$add['display_order']  = 255;
		$add['is_del'] = 0;
		$add['ctime']= $add['mtime'] = time();
		if($res = $this->insertGetId($add)){
			$map['department_id'] = $res;
			$save['display_order'] = empty($data['display_order']) ? $res : $data['display_order'];
			$this->where($map)->update($save);
		}
		$res !== false && $res = true;
		// 清空部门缓存
		$this->cleanCache();
		return $res;
	}

	/**
	 * 通过指定的部门ID修改部门名称
	 * @param int $cid 部门ID
	 * @param string $name 部门名称
	 * @return boolean 修改部门是否成功
	 */
	public function doEdit($data = []) {
		
		// 修改部门的名称
		$map['department_id'] = $data['department_id'];
		$save['title'] = $data['title'];
		$res = $this->where($map)->update($save);
		$this->cleanCache();
		return $res;
	}

	/**
     * 删除指定部门ID
     * @param int $cid 部门ID
     * @return boolean 部门是删除成功
     */
    public function doDel($id = 0) {
    	$map['department_id'] = $id;
    	$res = $this->where($map)->delete();
    	$this->cleanCache();
    	return $res;
    }

	/*** 私有方法 ***/
	// 获取父级ID
	private function _getParentCategory($data = 0) {
		$pid = $data['parent_dept_id'];
		if(!empty($data['_parent_id'])) {
			$level = count($data['_parent_id']);
			$_p = $data['_parent_id'][$level - 2] ? $data['_parent_id'][$level - 2] : $pid;
			$pid = $data['_parent_id'][$level - 1] > 0 ? $data['_parent_id'][$level - 1] : $_p;
		}

		return intval($pid);
	}

	// 获取两级树形结构
	public function getTreeByTwo($cid = 0) {
		$tree = $this->treeDo->getTree($cid);
		$data = array();
		foreach($tree['_child'] as $value) {
			$d = array('id'=>$value['department_id'], 'name'=>$value['title'], 'pid'=>$value['parent_dept_id'], 'child'=>array());
			if(!empty($value['_child'])) {
				foreach($value['_child'] as $val) {
					$d['child'][] = array('id'=>$val['department_id'], 'name'=>$val['title'], 'pid'=>$val['parent_dept_id']);
				}
			}
			$data[$value['department_id']] = $d;
		}

		return $data;
	}
    // 获取三级树形结构
    public function getThreeByTwo($cid) {
        $tree = $this->treeDo->getTree($cid);
        $data = array();
        foreach($tree['_child'] as $value) {
            $d = array('id'=>$value['department_id'], 'name'=>$value['title'], 'pid'=>$value['parent_dept_id'], 'child'=>array());
            if(!empty($value['_child'])) {
                foreach($value['_child'] as $val) {
                    $arr = array('id'=>$val['department_id'], 'name'=>$val['title'], 'pid'=>$val['parent_dept_id']);
                    if(!empty($val['_child'])){
                        foreach ($val['_child'] as $v) {
                            $arr_child = array('id'=>$v['department_id'], 'name'=>$v['title'], 'pid'=>$v['parent_dept_id']);
                            $arr['child'][] = $arr_child;
                        }
                    }
                    $d['child'][] = $arr;
                }
            }
            $data[$value['department_id']] = $d;
        }

        return $data;
    }


	/**
	 * 获取两级树形结构带统计数目 //暂时不用
	 * 1.如果部门不是叶子节点，将不会跳转到子集中
	 * 2.如果部门是叶子节点，将跳转到子集中
	 **/
	public function getTreeNumsByTwo($cid) {
		if(empty($cid)){
			$rootId = $cid;
		}else{
			// 判断是否是叶子节点 - 如果是将获取父节点ID
			$isLeafNode = $this->isLeafNode($cid);

			if($isLeafNode){
				$rootId = $this->where('department_id='.$cid)->field('parent_dept_id')->find();
				$rootId = $rootId['parent_dept_id'];
			}else{
				$rootId = $cid;
			}	
		}

		// 获取对应部门ID的树形结构
		$tree = $this->treeDo->getTree($rootId);

		$treeData =  $tree['_child'];

		$cids = array();

		$cids[] = $rootId;

		//只取一级 因为下级已经包含在一级内了
		foreach($treeData as $value) {
			$cids[] = $value['department_id'];
		}

		$totalcid = $cids;
        $cids = array_unique(array_filter($cids));
        $table = 'user_department b left join user a on b.uid = a.uid';
        $map['a.is_audit'] = 1;
        $map['a.is_del']   = 0;
        $map['b.department_id'] = array('in',$cids);
        $nums = D('')->table($table)->where($map)->field('count(1) as nums,b.department_id')->group('b.department_id')->getHashList('department_id','nums');
        $data = array();        
        if(empty($totalcid[0])){
            $count = intval(array_sum($nums));
        }else{
            $count = intval($nums[$rootId]);
        }
        $data[] = array('id'=>$rootId,'name'=>L('PUBLIC_ALL_STREAM'),'count'=>$count);
		foreach($treeData as $value) {
			$d = array('id'=>$value['department_id'], 'name'=>$value['title'], 'count'=> intval($nums[$value['department_id']]));
			$data[$value['department_id']] = $d;
		}

		//返回成wigdets需要的格式
		$r['data'] 	   = $data;
		$r['info'] = array('id'=>$rootId,'name'=>$this->getCateName($rootId));
		return $r;	
	}

	//转移部门，将旧部门下的用户转移到新部门下面 //暂时不用
	//一般不会进行此操作
	public function moveDept($oldId,$newId){
		if($oldId == $newId) {
			return false;
		}
		$oldId = intval($oldId);
		$newId = intval($newId);
		//判断原部门直属用户是否存在
		$oldUids = model('User')->where('department_id = '.$oldId)->getFieldAsArray('uid');
		if( count($oldUids) > 0 ){
			//删除关联表中相关用户的信息
			D('user_department')->where("uid in (select uid from ".$this->tablePrefix."user where department_id ='{$oldId}')")->delete();
			$save['department_id'] = $newId;
			model('User')->where('department_id = '.$oldId)->save($save);
			$newPids = $this->getPidsAllById($newId);
			$newPids[] = $newId;
			foreach ($newPids as $id) {
				if(empty($id)){
					continue;
				}
				$add['department_id'] = $id;
				foreach($oldUids as $uid){
					$add['uid'] = $uid;
					D('user_department')->add($add);
				}
				model('User')->cleanCache($oldUids);
			}
			$this->moveUserImByDepartmentId($oldUids,$newId);
			return true;
		}else{
			//不需要处理
			return true;
		}
	}

	/** 通过ID更新用户部门 */
	public function updateUserDepartById($uids,$department_id){
		!is_array($uids) && $uids = explode(",",$uids);
		$map['uid'] = array('in',$uids);
		$save['department_id'] = $department_id;
		
		model('User')->where($map)->save($save);

		D('user_department')->where($map)->delete();
		
		$newPids = $this->getPidsAllById($department_id);
		$newPids[] = $department_id;

		foreach($newPids as $id){
			if(empty($id)){
				continue;
			}
			$add['department_id'] = $id;
			foreach($uids as $uid){
				$add['uid'] = $uid;
				D('user_department')->add($add);
			}
		}	
        model('User')->cleanCache($uids);
		return true;
	}


	/**
     * 获取每个分类下带统计数的分类展示
     */
    public function getCategoryCount(){
    	$table = $this->tablePrefix."user_department as d join ".$this->tablePrefix."user as u on d.uid = u.uid ";
    	$map['u.is_del'] = 0;
    	$temps = D('')->table($table)->where($map)->field('d.department_id as department_id ,count(u.uid) as count')->group('department_id')->getHashList('department_id','count');
        return $temps;
    }

    public function createIm($department_id,$uid,$name='',$desc=''){
    	$info = $this->getCateInfo($department_id);
    	if(!empty($info['gid'])){
    		return false;
    	}
    	$childIds = $this->getChildIds($department_id);
    	$map['department_id'] = array('in',$childIds);
    	$uids = model('User')->where($map)->getFieldAsArray('uid');
    	if( $info = model('Webim')->createGroup($uid,$uids,$name,$desc,$department_id) ){
    		$_map['department_id'] = $department_id;
    		$_save['gid'] = $info['gid'];
    		$this->where($_map)->save($_save);
    		$this->cleanCache($department_id);
    	}else{
    		return false;
    	}
    	
    }
    public function createTeam($department_id,$uid,$name=''){
    	$info = $this->getCateInfo($department_id);
    	if(!empty($info['team_id'])){
    		return false;
    	}
    	$childIds = $this->getChildIds($department_id);
    	$map['department_id'] = array('in',$childIds);
		$user_info = model('User')->getDetail($uid);
		$data['name'] = $name;
		$data['uname'] = $user_info['uname'];
		$data['uid'] = $uid;
		$data['ctime'] = time();
		$data['status'] = 1;
		$data['department_id'] = $department_id;
		$data['description'] = '';
		$data['team_id'] = D('Team','team')->add($data);
		if ($data['team_id']) {
			$ca['category_id'] = C('DEFAULT_TEAM_CATEGORY_ID');
			$ca['team_id'] = $data['team_id'];
			D('TeamCategoryLink')->add($ca);
			D('TeamMember','team')->initOpen($data);
            D('TeamXCategory', 'team')->initOpen($data['team_id']);
			D('TeamCount','team')->initOpen($data['team_id']);
			$_map['department_id'] = $department_id;
    		$_save['team_id'] = $data['team_id'];
    		$this->where($_map)->save($_save);
			$this->cleanCache($department_id);
			return true;
		}else{
    		return false;
    	}
    	
    }

   

    public function syncDepartment($data){
        $sql = "INSERT INTO qm_department (`department_id`,`title`,`parent_dept_id`,`display_order`,`depart_manager`) VALUES ";
        foreach ($data as $key => $value) {
            if(!$value['department_id'] || !$value['title'] ){
                Log::write('syncDepartment error, department_id or title is empty, data:'.var_export($value, true), "HRBI_API");
                return false;
            }
            $data_sql[] = "(" .$value['department_id']. ",'" .$value['title']. "'," .$value['parent_id'].",".$value['display_order'].",".$value['manager']." )";
        }
        $sql .= implode(',', $data_sql).";";

        $this->query("delete from qm_department");
        $this->query($sql);

        $this->cleanCache();
        return true;
    }  

    //通过部门id获取二级部门id
    public function getSecondId($id){
    	$parent_ids = $this->getPidsAllById($id);
    	$parent_ids = array_reverse($parent_ids);
    	$count = count($parent_ids);
    	if($count > 3){
    		return $parent_ids[3];
    	}else{
    		return $id;
    	}  
    } 
    //通过部门id获取一级部门id
    public function getFirstId($id){
    	$parent_ids = $this->getPidsAllById($id);
    	$parent_ids = array_reverse($parent_ids);
    	$count = count($parent_ids);
    	if($count >= 3){
    		return $parent_ids[2];
    	}else{
    		return $id;
    	}  
    }
}