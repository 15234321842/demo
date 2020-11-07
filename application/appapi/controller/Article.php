<?php
/**
 * Created by PhpStorm.
 * User: 比谷网络
 * Date: 2018/3/12
 * Time: 11:46
 */
namespace app\appapi\controller;

use data\service\Platform;
use think\Request;
use data\service\Article as ArticleService;

Class Article extends BaseController{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    /*
     * 获取文章列表
     * */
    public function getArticle(){
        $request=request();
        $class_id=$request->post('class_id','');
        $article_id=$request->post('article_id');
        $article=new ArticleService();
        $field='nca.article_id, nca.title, nca.class_id, nca.short_title, nca.author, nca.image, nca.keyword, nca.click, nca.sort,nca.status,  nca.tag, nca.comment_count, nca.last_comment_time, nca.share_count, nca.publisher_name, nca.uid, nca.public_time, nca.create_time, nca.modify_time, ncac.name';
        if($class_id){
            $data= $article->getArticleList(1,0,['nca.class_id'=>$class_id],'nca.sort desc',$field);
        }else if($article_id){
            if(!trim($article_id)){
                $this->error('没有这篇文章！');
            }
            $data= $article->getArticleDetail($article_id);
            $data['class']='文章详情';
            $domain = \think\Request::instance()->domain();
            $data['image']=$domain.'/'.$data['image'];
            $data['content']=str_replace('/upload/ueditor',$domain.'/upload/ueditor',$data['content']);
            $data['create_time']=date("Y.m.d",$data['create_time']);
            $this->success('查询成功',$data);
        } else{
            $data=$article->getArticleList(1,0,['nca.class_id'=>['neq',1]],'nca.sort desc',$field);
        }
        foreach ($data['data'] as &$item){
            $item['image']=\think\Request::instance()->domain().'/'.$item['image'];
        }
        $this->success('查询成功',$data);
    }

    /*
     *
     * */
    public function getAdminArticle(){
        $title=$this->request->post('title','');
        if(!empty(($title))){
            $article=new ArticleService();
            $info=$article->getArticleInfo($title);
            $domain = \think\Request::instance()->domain();
            $info['class']=$title;
            $info['create_time']=date("Y-m-d");
            $info['content']=str_replace('/upload/ueditor',$domain.'/upload/ueditor',$info['content']);
            $this->success('获取完成',$info);
        }else{
            $this->error('标题不能为空！');
        }
    }



    /*
     * 获取文章分类
     * */
    public function getArticleClass(){
        $article=new ArticleService();
        $data=$article->getArticleClassQuery();
        $this->success('查询成功',$data);
    }


    //广告列表
   public function getAdvertisingList(){

        $type = request()->post("type" ,2);//1手机端，2移动端
        $ap_keyword = request()->get('ap_keyword','');

        if(empty($type)||empty($ap_keyword)){
            $this->error('参数错误');
        }
        $platform = new Platform();
        $condition['type']=$type;
        $condition['ap_keyword'] = $ap_keyword;
        $condition['is_use']=1;
        //广告位
        $result['advertising'] = $platform->getPlatformAdvPositionList(1, 0, $condition);
        //广告列表
        $result['list'] = $platform->adminGetAdvList(1, 0, $condition, 'slide_sort desc');

       $this->success('获取成功',$result);
   }
}