<!DOCTYPE html>
<html lang="en">
<head>
    <title>提示信息 - {:config('site_title')}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="description" content="">
    {include file="common:common_css" /}
</head>
<body ontouchstart>
<div class="page-group">
    <div class="weui-msg">
        <div class="weui-msg__icon-area">
            <?php switch($code): ?>
            <?php case 1:?>
            <i class="weui-icon-success weui-icon_msg"></i>
            <?php break;?>
            <?php case 0:?>
            <i class="weui-icon-warn weui-icon_msg"></i>
            <?php break;?>
            <?php endswitch;?>
        </div>
        <div class="weui-msg__text-area">
            <h2 class="weui-msg__title">{$msg}</h2>
        </div>
        <div class="weui-msg__opr-area">
            <p class="weui-btn-area">
                <a onclick="goBack('');" class="weui-btn weui-btn_primary">返回上一步</a>
            </p>
        </div>
        <div class="weui-msg__extra-area">
            <div class="weui-footer">
                <p class="weui-footer__links">
                    <a href="javascript:void(0);" class="weui-footer__link">{:config('site_title')}</a>
                </p>
                <p class="weui-footer__text">Copyright © 2018-2118 letu33.mcom</p>
            </div>
        </div>
    </div>
</div>
{include file="common:common_js" /}
</body>
</html>