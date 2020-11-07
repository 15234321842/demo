<?php
/**
 * Goods.php
 * shop�̳�ϵͳ - �Ŷ�ʮ����̾���㼯����!
 * =========================================================
 * Copy right 2015-2025 ɽ��ţ����Ϣ�Ƽ����޹�˾, ��������Ȩ����
 * ----------------------------------------------
 * �ٷ���ַ: http://www.easybigu.com
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�á�
 * �κ���ҵ�͸��˲�����Գ���������κ���ʽ�κ�Ŀ���ٷ�����
 * =========================================================
 * @author : hujinteam
 * @date : 2015.1.17
 * @version : v1.0.0.0
 */

namespace app\admin\controller;
//use data\model\NsSpecialModel;
//use data\model\NsPositionModel;
//use data\model\NsPositionContentModel;
use data\service\Goods as GoodsService;
use think\Db;

/**
 *
 */
class Special extends BaseController
{
    private $special;
    private $position;
    private $content;

    public function __construct()
    {
        parent::__construct();
//        $this->special = new NsSpecialModel();
//        $this->position = new NsPositionModel();
//        $this->content = new NsPositionContentModel();
    }

    public function special()
    {
        $list = Db::table('ns_special')->select();
        $this->assign('list', $list);
        return view($this->style . "Special/special");
    }

    public function edit()
    {
        $id = $this->request->param('id');
        $this->assign('id', $id);
        return view($this->style . "Special/edit");
    }

    /*
     * 添加专区
     */
    public function index()
    {
        if (request()->isPost()) {
            $title = request()->post('title');
            $id = request()->post('id');
            if ($id) {
                $res = Db::table('ns_special')->save(['title' => $title], ['id' => $id]);
                if ($res) {
                    $this->success('修改成功');
                } else {
                    $this->error('修改失败');
                };
            }
            if (!$title) {
                $this->error('标题不能为空');
            }
            $sort = request()->post('sort', 0);
            $show = request()->post('is_show', 1);
            $data = array();
            $data['title'] = $title;
            $data['sort'] = $sort;
            $data['is_show'] = $show;
            $data['create_time'] = time();
            Db::table('ns_special')->insert($data);
            $id = Db::table('ns_special')->getLastInsID();
            $data1 = [
                ['tid' => $id, 'type' => 'banner', 'create_time' => time()],
                ['tid' => $id, 'type' => 'hotsale', 'create_time' => time()],
                ['tid' => $id, 'type' => 'adv', 'create_time' => time()],
                ['tid' => $id, 'type' => 'menu', 'create_time' => time()],
                ['tid' => $id, 'type' => 'flashsale', 'create_time' => time()],
            ];
            $res = Db::table('ns_position')->insertAll($data1);
            if ($res) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            };
        } else {

            $list = Db::table('ns_special')->select();
            $this->assign('list', $list);
            return view($this->style . "Special/index");

        }
    }

    /*
   * 删除专区
   */
    public function del()
    {
        $id = input('param.id');
        $vid = Db::table('ns_position')->where(['tid' => $id])->column('id');
        if ($vid) {
            Db::table('ns_position_content')->where(['vid' => ['in', $vid]])->delete();
            Db::table('ns_position')->where(['tid' => $id])->delete();
        }
        $res = Db::table('ns_special')->delete($id);
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        };
    }

    public function delParam(){
        $id=$this->request->param('id/d',0);
        if(!$id){
            $this->error('错误参数');
        }
        $ret=Db::table('ns_position_content')->where(['id' => $id])->delete();
        if ($ret) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        };
    }

    public function show()
    {
        $id = input('param.id');
        $show = input('param.is_show');
        $res = Db::table('ns_special')->update(['is_show' => $show, 'id' => $id]);

        if ($res) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        };

    }

    public function getData()
    {
        $id = $this->request->param('id');
        $type = $this->request->param('type');
        $thisid = Db::name('ns_position')->where(['tid' => $id, 'type' => $type])->value('id');
        if (!$thisid) {
            $this->error('错误参数');
        }
        $list = Db::name('ns_position_content')->where(['vid' => $thisid])->select();

        $this->success('获取完成', '', ['id'=>$thisid,'list'=>$list]);
    }

    /*
     * 添加内容
     */
    public function addInfo()
    {
        $id = $this->request->param('id');
        $title = request()->post('title');
        $vid = request()->post('vid');
        $image = request()->post('image');
        $url = request()->post('url');
        $extra = request()->post('extra', '');
        $type = request()->post('type', '');
        $data_id = request()->post('data_id', '');

        $data['title'] = $title;
        $data['vid'] = $vid;
        $data['image'] = $image;
        $data['url'] = $url;
        $data['create_time'] = time();
        $data['extra'] = $extra;
        $data['type'] = $type;
        $data['data_id'] = $data_id;
        if ($id) {
            $ret = Db('ns_position_content')->where(['id' => $id])->update($data);
        } else {
            $ret = Db('ns_position_content')->insertGetId($data);
        }

        if ($ret) {
//            var_dump(Db('ns_position_content')->getLastSql());
            $this->success('编辑成功');
        }else{
            $this->success('编辑失败');
        }
    }

    public function contents()
    {
        $id = input('param.id');
        $vid = Db::name('ns_position')->field('id', 'type')->where(['id' => $id])->select();
//            $this->assign('content',$content_list);
//            $position_list=Db::name('ns_special')->field('id,name,type,sort')->select();
//            $this->assign('position',$position_list);
        $data = array();
        $data['data']['vid'] = json_encode($vid);
        $data['data']['id'] = json_encode($id);
        return $data;
    }

    /*
     *
     */
//    public function block(){
//
//        if(request()->isPost()){
//            $title = request()->post('title');
//            $tid = request()->post('tid');
//            if(!$title){
//                $this->error('��������Ϊ��');
//            }
//            $sort = request()->post('sort', 0);
//            $show =request()->post('is_show',1);
//        }else{
//            $special = new NsSpecialModel();
//            $position = new  NsPositionModel();
//
//            $position_list=$special->field('id,name,type,sort')->select();
//            $special_list=$special->field('id,name')->where('is_show = 1')->select();
//            $this->assign('position',$position_list);
//            $this->assign('special',$special_list);
//            return view($this->style . "Special/index");
//        }
//    }
    public function check(){
        $this->assign('index',$this->request->param('index/d',-1));
        return view($this->style . "Special/check");
    }
    public function checkGoods(){
        $this->assign('index',$this->request->param('index/d',-1));
        return view($this->style . "Special/check_goods");
    }
    public function checkFlashSale(){
        $this->assign('index',$this->request->param('index/d',-1));
        return view($this->style . "Special/check_flashsale");
    }
    public function checkArticle(){
        $this->assign('index',$this->request->param('index/d',-1));
        return view($this->style . "Special/check_article");
    }
    public function checkCategory(){
        $this->assign('index',$this->request->param('index/d',-1));
        return view($this->style . "Special/check_category");
    }

}