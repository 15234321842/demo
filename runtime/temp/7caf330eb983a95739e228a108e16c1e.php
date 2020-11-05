<?php /*a:3:{s:68:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\my\my_insurance.html";i:1576676556;s:70:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\common\common_css.html";i:1576676556;s:69:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\common\common_js.html";i:1576676556;}*/ ?>
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
                    <a class="button button-link button-nav pull-left" onclick="goBack('<?php echo url('My/index'); ?>');"><span class="icon fa fa-angle-left"></span>返回</a>
                    <h1 class="title">
                        <span>我的保单</span>
                    </h1>
                    <span class="button button-link button-nav pull-right">
                        <a href="<?php echo url('insurance_my_add'); ?>" style="margin-left: 10px;margin-right: 10px;"><span class="icon fa fa-plus"></span></a>
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
                                    <a href="<?php echo letu_url('insurance_view',array('id'=>think_encrypt($vo['insurance_id']))); ?>">
                                        <p class="clue_content"><?php echo htmlentities($vo['insurance_main']); ?></p>
                                    </a>
                                    <p class="clue_time weui-flex">
                                        <span class="sell_money weui-flex__item">&yen;<?php echo htmlentities(floatval($vo['first_premium'])); ?></span>
                                        <span class="weui-flex__item" style="margin-left: 10px;"><?php echo htmlentities(date('Y-m-d',!is_numeric($vo['first_pay_date'])? strtotime($vo['first_pay_date']) : $vo['first_pay_date'])); ?></span>
                                        <span class="weui-flex__item" style="margin-left: 10px;">交费<?php echo htmlentities($vo['pay_year']); ?>年</span>
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
        </div>
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
                    <p class="clue_content">{{insurance_main}}</p>
                </a>
                <p class="clue_time weui-flex">
                    <span class="sell_money weui-flex__item">&yen;{{first_premium}}</span>
                    <span class="weui-flex__item" style="margin-left: 10px;">{{first_pay_date_format}}</span>
                    <span class="weui-flex__item" style="margin-left: 10px;">交费{{pay_year}}年</span>
                </p>
            </div>
        </div>
    </div>
    {{/each}}
</script>
<script type="text/javascript">
    var lastTime = '<?php echo htmlentities($lastTime); ?>';
    var totalPage = '<?php echo htmlentities($list->lastPage()); ?>';
    var scrollLoadingUrl = "<?php echo url('insurance_scroll_loading'); ?>";
    var pullToRefreshUrl = "<?php echo url('insurance_pull_to_refresh'); ?>";

    var param_json = {};
    param_json.last_time = lastTime;
    param_json.page = 1;
    param_json.policy_type = 0;

    function load_data(param_json){
        $.ajax({
            type:'POST',
            dataType:"json",
            data:param_json,
            url:pullToRefreshUrl,
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
                $("#scrollPage").pullToRefreshDone();
                if(totalPage > 1){
                    $(".weui-loadmore").show();
                }
            }
        });
    }

    $(document).ready(function(){
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
                load_data(param_json);
            }, 2000);
        });

    });
</script>
</body>
</html>