<?php /*a:4:{s:64:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\index\index.html";i:1576676556;s:70:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\common\common_css.html";i:1576676556;s:70:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\common\bottom_nav.html";i:1576676556;s:69:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\common\common_js.html";i:1576676556;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo config('site_title'); ?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="description" content="">
    <link rel="shortcut icon" href="/favicon.ico"/>
<link rel="bookmark" href="/favicon.ico"/>
<link rel="stylesheet" href="/static/houtai/css/weui.min.css">
<link rel="stylesheet" href="/static/houtai/css/jquery-weui.min.css">
<link rel="stylesheet" href="/static/houtai/css/font-awesome.min.css">
<link rel="stylesheet" href="/static/houtai/css/houtai.css">
</head>
<body ontouchstart>
    <div class="weui-tab">
        <div class="weui-tab__bd">
            <div class="page-group">
                <header class="bar bar-nav">
                    <a class="button button-link button-nav pull-left"><span class="icon fa fa-search"></span></a>
                    <h1 class="title">客户</h1>
                    <a class="button button-link button-nav pull-right" href="<?php echo url('add'); ?>"><span class="icon fa  fa-user-plus"></span></a>
                </header>
                <div id="scrollPage" class="content">
                    <div class="weui-pull-to-refresh__layer">
                        <div class='weui-pull-to-refresh__arrow'></div>
                        <div class='weui-pull-to-refresh__preloader'></div>
                        <div class="down">下拉刷新</div>
                        <div class="up">释放刷新</div>
                        <div class="refresh">正在刷新</div>
                    </div>
                    <div id="list" class="weui-cells_notbefore letu-customer">
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <div class="weui-cell weui-cell_access">
                            <div class="weui-cell__bd">
                                <div class="weui-flex">
                                    <div class="weui-flex__item user"><?php echo htmlentities($vo['customer_name']); ?></div>
                                    <?php $mobile=explode(',',$vo['mobile']); ?>
                                    <div class="weui-flex__item phone"><a href="tel:<?php echo htmlentities($mobile[0]); ?>"><?php echo htmlentities($mobile[0]); ?></a></div>
                                    <div class="weui-flex__item weui-cell__ft relations"><a href="<?php echo letu_url('view',array('id'=>think_encrypt($vo['customer_id']))); ?>"><?php echo config('app.customer_relations.'.$vo['customer_relations']); ?></a></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </div>
                    <?php if($list->lastPage() > 1): ?>
                    <div class="weui-loadmore">
                        <span class="weui-loadmore__tips">点击加载更多</span>
                    </div>
                    <?php endif; if($list->total() == 0): ?>
                    <div class="weui-loadmore">
                        <span class="weui-loadmore__tips">暂无数据</span>
                    </div>
                    <?php endif; ?>
            </div>
        </div>
        <div class="weui-tabbar letu-tabbar">
    <a href="<?php echo url('Message/index'); ?>" class="weui-tabbar__item <?php if(in_array(app('request')->controller(),array('Message'))): ?>weui-bar__item--on<?php endif; ?>">
        <?php if($notReadMessageCount > 0): ?>
        <span class="weui-badge" style="position: absolute;top: -0.4em;right: 1.8em;"><?php if($notReadMessageCount > 99): ?>99+<?php else: ?><?php echo htmlentities($notReadMessageCount); ?><?php endif; ?></span>
        <?php endif; ?>
        <span class="weui-tabbar__icon fa fa-commenting-o"></span>
        <p class="weui-tabbar__label">消息</p>
    </a>
    <a href="<?php echo url('Index/index'); ?>" class="weui-tabbar__item <?php if(in_array(app('request')->controller(),array('Index'))): ?>weui-bar__item--on<?php endif; ?>">
        <div class="weui-tabbar__icon fa fa-users"></div>
        <p class="weui-tabbar__label">客户</p>
    </a>
    <a href="<?php echo url('App/index'); ?>" class="weui-tabbar__item <?php if(in_array(app('request')->controller(),array('App','SellFlow','CrmClue','CrmFollow','SellRecord','WorkAim','WorkSchedule'))): ?>weui-bar__item--on<?php endif; ?>">
    <span class="weui-tabbar__icon fa fa-navicon"></span>
    <p class="weui-tabbar__label">应用</p>
    </a>
    <a href="<?php echo url('My/index'); ?>" class="weui-tabbar__item <?php if(in_array(app('request')->controller(),array('My'))): ?>weui-bar__item--on<?php endif; ?>">
        <div class="weui-tabbar__icon fa fa-user"></div>
        <p class="weui-tabbar__label">我的</p>
    </a>
</div>
        </div>
    </div>
    <div class="weui-mask"></div>
    <div class="search_panel">
        <form id="frmSearch" method="post">
        <div class="weui-cells weui-cells_form letu-cells_not_top_line">
            <div class="weui-cell">
                <div class="weui-cell__hd"><label class="weui-label">客户名称：</label></div>
                <div class="weui-cell__bd">
                    <input type="text" id="customer_name" name="customer_name" class="weui-input" placeholder="请输入名称" maxlength="30" />
                </div>
            </div>
            <div class="weui-cell">
                <div class="weui-cell__hd"><label class="weui-label">手机号：</label></div>
                <div class="weui-cell__bd">
                    <input type="text" id="mobile" name="mobile" class="weui-input" placeholder="请输入手机号" maxlength="20" />
                </div>
            </div>
            <div class="weui-cell">
                <div class="weui-cell__hd"><label class="weui-label">性别：</label></div>
                <div class="weui-cell__bd weui-cells_checkbox">
                    <?php if(is_array($sexList) || $sexList instanceof \think\Collection || $sexList instanceof \think\Paginator): $i = 0; $__LIST__ = $sexList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <label class="letu-radio-inline weui-check__label">
                        <div class="weui-cell__hd">
                            <input type="radio" name="sex" class="weui-check" value="<?php echo htmlentities($key); ?>"/>
                            <i class="weui-icon-checked"></i>
                        </div>
                        <div class="weui-cell__bd"><p><?php echo htmlentities($vo); ?></p></div>
                    </label>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
            <div class="weui-cell">
                <div class="weui-cell__bd">
                    <div class="letu-box">
                        <div class="letu-box-title">客户关系：</div>
                        <div class="letu-box-body weui-cells_checkbox">
                            <?php if(is_array($customerRelationsList) || $customerRelationsList instanceof \think\Collection || $customerRelationsList instanceof \think\Paginator): $i = 0; $__LIST__ = $customerRelationsList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <label class="letu-radio-inline weui-check__label">
                                <div class="weui-cell__hd">
                                    <input type="radio" name="customer_relations" class="weui-check" value="<?php echo htmlentities($key); ?>"/>
                                    <i class="weui-icon-checked"></i>
                                </div>
                                <div class="weui-cell__bd"><p><?php echo htmlentities($vo); ?></p></div>
                            </label>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="weui-cell">
                <div class="weui-cell__hd"><label class="weui-label">客户分类：</label></div>
                <div class="weui-cell__bd weui-cells_checkbox">
                    <?php if(is_array($customerClassList) || $customerClassList instanceof \think\Collection || $customerClassList instanceof \think\Paginator): $i = 0; $__LIST__ = $customerClassList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <label class="letu-radio-inline weui-check__label">
                        <div class="weui-cell__hd">
                            <input type="radio" name="customer_class" class="weui-check" value="<?php echo htmlentities($key); ?>"/>
                            <i class="weui-icon-checked"></i>
                        </div>
                        <div class="weui-cell__bd"><p><?php echo htmlentities($vo); ?></p></div>
                    </label>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
        </div>
        <div class="weui-btn-area letu-btn-search-bar">
            <button id="btnSearch" type="button" class="weui-btn weui-btn_warn">确定搜索</button>
            <button id="btnReset" type="button" class="weui-btn weui-btn_default" style="margin-left: 10px;">重置条件</button>
        </div>
        </form>
    </div>
<script src="/static/houtai/js/jquery-2.1.4.min.js"></script>
<script src="/static/houtai/js/fastclick.js"></script>
<script src="/static/houtai/js/jquery-weui.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        FastClick.attach(document.body);

        //触发生成消息方法
        $.ajax({
            type:'GET',
            dataType:"json",
            url:"<?php echo url('Message/build_message'); ?>",
            success:function(result){
            }
        });
    });
    function goBack(url){
        if(url != ""){
            window.location.href = url;
        }else{
            if(history.length > 1){
                history.back();
            }
        }
    }
</script>
<script type="text/template7" id="listTemplate">
    {{#each data}}
    <div class="weui-cell weui-cell_access">
        <div class="weui-cell__bd">
            <div class="weui-flex">
                <div class="weui-flex__item user">{{customer_name}}</div>
                <div class="weui-flex__item phone"><a href="tel:{{first_mobile}}">{{first_mobile}}</a></div>
                <div class="weui-flex__item weui-cell__ft relations"><a href="{{view_url}}">{{customer_relations}}</a></div>
            </div>
        </div>
    </div>
    {{/each}}
</script>
<script type="text/javascript">
    var lastTime = '<?php echo htmlentities($lastTime); ?>';
    var totalPage = '<?php echo htmlentities($list->lastPage()); ?>';
    var scrollLoadingUrl = "<?php echo url('scroll_loading'); ?>";
    var pullToRefreshUrl = "<?php echo url('pull_to_refresh'); ?>";

    var param_json = {};
    param_json.last_time = lastTime;
    param_json.page = 1;

    function load_data(param_json){
        $.ajax({
            type:'POST',
            dataType:"json",
            data:param_json,
            url:param_json.url,
            success:function(result){
                totalPage = result.totalPages;
                param_json.last_time = result.time;

                if(result.data.length > 0){
                    var template = $("#listTemplate").html();
                    var compiledTemplate = $.Template7.compile(template);
                    var html = compiledTemplate(result);

                    $("#list").html(html);
                }else{
                    $("#list").empty();
                }

                if(totalPage > 1){
                    $(".weui-loadmore").show();
                }else{
                    $(".weui-loadmore").hide();
                }
            }
        });
    }

    function update_search_params(){
        param_json.customer_name = $("#customer_name").val();
        param_json.mobile = $("#mobile").val();

        var $sex = $("input[name='sex']:checked");
        if($sex.prop("checked")){
            param_json.sex = $sex.val();
        }else{
            delete param_json['sex'];
        }

        var $customer_relations = $("input[name='customer_relations']:checked");
        if($customer_relations.prop("checked")){
            param_json.customer_relations = $customer_relations.val();
        }else{
            delete param_json['customer_relations'];
        }

        var $customer_class = $("input[name='customer_class']:checked");
        if($customer_class.prop("checked")){
            param_json.customer_class = $customer_class.val();
        }else{
            delete param_json['customer_class'];
        }
    }

    $(document).ready(function(){
        $(".weui-mask").click(function(){
            $(this).removeClass("weui-mask--visible");
            $(".search_panel").hide();
        });
        $(".fa-search").click(function(){
            $(".weui-mask").addClass("weui-mask--visible");
            $(".search_panel").show();
        });
        $("#btnSearch").click(function(){
            $(".weui-mask").removeClass("weui-mask--visible");
            $(".search_panel").hide();

            update_search_params();
            param_json.page = 1;
            param_json.url = pullToRefreshUrl;
            load_data(param_json);
        });

        $("#btnReset").click(function(){
            $("#frmSearch")[0].reset();
        });

        //滚动点击加载
        $(".weui-loadmore").click(function(){
            param_json.page += 1;
            if(param_json.page > totalPage){
                return false;
            }
            $.ajax({
                type:'POST',
                dataType:"json",
                data:param_json,
                url:scrollLoadingUrl,
                success:function(result){
                    if(result.data.length > 0){
                        var template = $("#listTemplate").html();
                        var compiledTemplate = $.Template7.compile(template);
                        var html = compiledTemplate(result);

                        $("#list").append(html);

                        if(param_json.page == totalPage){
                            $(".weui-loadmore").hide();
                        }
                    }
                }
            });
        });

        //下拉刷新
        $("#scrollPage").pullToRefresh();
        $("#scrollPage").on("pull-to-refresh", function() {
            setTimeout(function() {
                param_json.page = 1;
                param_json.url = pullToRefreshUrl;
                load_data(param_json);
                $("#scrollPage").pullToRefreshDone();
            }, 2000);
        });
    });
</script>
</body>
</html>