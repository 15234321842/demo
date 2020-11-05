<?php /*a:4:{s:62:"D:\phpstudy_pro\WWW\letucrm\app\houtai\view\message\index.html";i:1576676556;s:66:"D:\phpstudy_pro\WWW\letucrm\app\houtai\view\common\common_css.html";i:1576676556;s:66:"D:\phpstudy_pro\WWW\letucrm\app\houtai\view\common\bottom_nav.html";i:1576676556;s:65:"D:\phpstudy_pro\WWW\letucrm\app\houtai\view\common\common_js.html";i:1576676556;}*/ ?>
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
    <style type="text/css">
        .weui-picker-container, .weui-picker-overlay{
            z-index: 10002;
        }
    </style>
</head>
<body ontouchstart>
<div class="weui-tab">
    <div class="weui-tab__bd">
        <div class="page-group">
            <header class="bar bar-nav">
                <h1 class="title">消息</h1>
                <span class="button button-link button-nav pull-right">
                    <span class="icon fa fa-search"></span>
                </span>
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
                            <div class="weui-cell__ft">
                                <a href="<?php echo letu_url('view',array('id'=>think_encrypt($vo['msg_id']))); ?>">
                                    <p class="clue_content"><?php echo htmlentities($vo['msg_title']); ?></p>
                                </a>
                                <p class="clue_time">
                                    <span><?php echo htmlentities(date('Y-m-d H:i',!is_numeric($vo['msg_time'])? strtotime($vo['msg_time']) : $vo['msg_time'])); ?></span>
                                    <span style="margin-left: 10px;" class="weui-badge <?php if($vo['is_read'] == 1): ?>badge-old<?php endif; ?>"><?php echo config('app.is_read.'.$vo['is_read']); ?></span>
                                    <span style="margin-left: 10px;"><?php echo config('app.msg_type.'.$vo['msg_type']); ?></span>
                                </p>
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
                <div class="weui-cell__hd"><label class="weui-label">消息日期：</label></div>
                <div class="weui-cell__bd">
                    <input type="text" id="msg_time" name="msg_time" class="weui-input" placeholder="请选择日期" maxlength="20" />
                </div>
            </div>
            <div class="weui-cell">
                <div class="weui-cell__hd"><label class="weui-label">是否阅读：</label></div>
                <div class="weui-cell__bd weui-cells_checkbox">
                    <?php if(is_array($isReadList) || $isReadList instanceof \think\Collection || $isReadList instanceof \think\Paginator): $i = 0; $__LIST__ = $isReadList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <label class="letu-radio-inline weui-check__label">
                        <div class="weui-cell__hd">
                            <input type="radio" name="is_read" class="weui-check" value="<?php echo htmlentities($key); ?>"/>
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
                        <div class="letu-box-title">消息类型：</div>
                        <div class="letu-box-body weui-cells_checkbox">
                            <?php if(is_array($msgTypeList) || $msgTypeList instanceof \think\Collection || $msgTypeList instanceof \think\Paginator): $i = 0; $__LIST__ = $msgTypeList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <label class="letu-radio-inline weui-check__label">
                                <div class="weui-cell__hd">
                                    <input type="radio" name="msg_type" class="weui-check" value="<?php echo htmlentities($key); ?>"/>
                                    <i class="weui-icon-checked"></i>
                                </div>
                                <div class="weui-cell__bd"><p><?php echo htmlentities($vo); ?></p></div>
                            </label>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </div>
                    </div>
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
            <div class="weui-cell__ft">
                <a href="{{view_url}}">
                    <p class="clue_content">{{msg_title}}</p>
                </a>
                <p class="clue_time">
                    <span>{{msg_time_format}}</span>
                    <span style="margin-left: 10px;" class="weui-badge {{#js_compare "this.is_read == 1"}}badge-old{{/js_compare}}">{{is_read_text}}</span>
                    <span style="margin-left: 10px;">{{msg_type_text}}</span>
                </p>
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
        param_json.msg_time = $("#msg_time").val();

        var $msg_type = $("input[name='msg_type']:checked");
        if($msg_type.prop("checked")){
            param_json.msg_type = $msg_type.val();
        }else{
            delete param_json['msg_type'];
        }

        var $is_read = $("input[name='is_read']:checked");
        if($is_read.prop("checked")){
            param_json.is_read = $is_read.val();
        }else{
            delete param_json['is_read'];
        }
    }

    $(document).ready(function(){
        $("#msg_time").calendar({'dateFormat': 'yyyy-mm-dd'});
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