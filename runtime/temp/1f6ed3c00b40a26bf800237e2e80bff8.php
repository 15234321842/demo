<?php /*a:3:{s:62:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\app\index.html";i:1576676556;s:70:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\common\common_css.html";i:1576676556;s:70:"D:\phpstudy_pro\WWW\letucrm\app_www\houtai\view\common\bottom_nav.html";i:1576676556;}*/ ?>
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
<div class="page-group letu-app">
    <div class="weui-cells">
        <a class="weui-cell weui-cell_access" href="<?php echo url('CrmClue/index'); ?>">
            <div class="weui-cell__bd">
                <p>客户线索</p>
            </div>
            <div class="weui-cell__ft"></div>
        </a>
        <a class="weui-cell weui-cell_access" href="<?php echo url('CrmFollow/index'); ?>">
            <div class="weui-cell__bd">
                <p>客户跟进</p>
            </div>
            <div class="weui-cell__ft"></div>
        </a>
    </div>
    <div class="weui-cells">
        <!--<a class="weui-cell weui-cell_access" href="<?php echo url('Customer/analyze'); ?>">-->
            <!--<div class="weui-cell__bd">-->
                <!--<p>客户分析</p>-->
            <!--</div>-->
            <!--<div class="weui-cell__ft"></div>-->
        <!--</a>-->
        <a class="weui-cell weui-cell_access" href="<?php echo url('SellFlow/index'); ?>">
            <div class="weui-cell__bd">
                <p>目标名单</p>
            </div>
            <div class="weui-cell__ft"></div>
        </a>
        <a class="weui-cell weui-cell_access" href="<?php echo url('WorkAim/index'); ?>">
            <div class="weui-cell__bd">
                <p>目标制定</p>
            </div>
            <div class="weui-cell__ft"></div>
        </a>
        <a class="weui-cell weui-cell_access" href="<?php echo url('SellRecord/index'); ?>">
            <div class="weui-cell__bd">
                <p>销售记录</p>
            </div>
            <div class="weui-cell__ft"></div>
        </a>
    </div>
    <div class="weui-cells">
        <a class="weui-cell weui-cell_access" href="<?php echo url('WorkSchedule/index'); ?>">
            <div class="weui-cell__bd">
                <p>日程安排</p>
            </div>
            <div class="weui-cell__ft"></div>
        </a>
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
</body>
</html>