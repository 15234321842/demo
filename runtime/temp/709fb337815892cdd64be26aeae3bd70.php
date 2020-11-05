<?php /*a:4:{s:61:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\my\index.html";i:1576676556;s:70:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\common\common_css.html";i:1576676556;s:70:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\common\bottom_nav.html";i:1576676556;s:69:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\common\common_js.html";i:1576676556;}*/ ?>
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
<div class="page-group letu-my">
    <div class="content">
        <div class="my-header">
            <div class="photo">
                <img src="/static/houtai/images/default_photo.jpg" alt="my">
            </div>
            <div class="text">
                <label for="zhanghao">账号：</label><span id="zhanghao"><?php echo htmlentities($userName); ?>,昵称：<?php echo htmlentities($nickName); ?></span>
            </div>
        </div>
        <div class="weui-cells letu-cells_not_top_line">
            <a class="weui-cell weui-cell_access" href="<?php echo url('My/customer_insurance'); ?>">
                <div class="weui-cell__bd">
                    <p>客户保单</p>
                </div>
                <div class="weui-cell__ft"></div>
            </a>
            <a class="weui-cell weui-cell_access" href="<?php echo url('My/my_insurance'); ?>">
                <div class="weui-cell__bd">
                    <p>我的保单</p>
                </div>
                <div class="weui-cell__ft"></div>
            </a>
        </div>
        <div class="weui-cells">
            <a class="weui-cell weui-cell_access" href="<?php echo url('My/talk_script'); ?>">
                <div class="weui-cell__bd">
                    <p>我的话术</p>
                </div>
                <div class="weui-cell__ft"></div>
            </a>
        </div>
        <div class="weui-cells">
            <?php if(app('request')->session('userInfo.vip_level') == 33): ?>
            <a class="weui-cell weui-cell_access"href="<?php echo url('My/member'); ?>">
                <div class="weui-cell__bd">
                    <p>会员管理</p>
                </div>
                <div class="weui-cell__ft"></div>
            </a>
            <?php endif; ?>
            <a class="weui-cell weui-cell_access"href="<?php echo url('My/set_password'); ?>">
                <div class="weui-cell__bd">
                    <p>设置密码</p>
                </div>
                <div class="weui-cell__ft"></div>
            </a>
            <a class="weui-cell weui-cell_access" data-href="<?php echo url('Login/logout'); ?>" onclick="logout(this);">
                <div class="weui-cell__bd">
                    <p>退出登录</p>
                </div>
                <div class="weui-cell__ft"></div>
            </a>
            <a class="weui-cell weui-cell_access">
                <div class="weui-cell__bd">
                    <p style="color:red;font-weight: bold;">联系作者QQ:282130106</p>
                </div>
                <div class="weui-cell__ft"></div>
            </a>
        </div>
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
<script type="text/javascript">
    function logout(obj){
        var $this = $(obj);

        $.confirm("您确定要退出登录？", "确认退出?", function() {
            $.ajax({
                type:'get',
                url:$this.attr("data-href"),
                dataType:'json',
                success:function(response){
                    if(response.success){
                        if(response.url != ""){
                            setTimeout(function(){
                                window.location.href = response.url;
                            },2000);
                        }
                        $.toptip(response.msg, 'success');
                    }else{
                        $.toptip(response.msg, 'error');
                    }
                },
                error:function(XMLHttpRequest, textStatus, errorThrown){
                }
            });
        }, function() {
            //取消操作
        });

        return false;
    }
</script>
</body>
</html>