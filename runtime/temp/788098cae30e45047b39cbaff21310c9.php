<?php /*a:3:{s:65:"E:\phpstudy_pro\WWW\letucrm2\app_www\houtai\view\login\index.html";i:1576676556;s:70:"E:\phpstudy_pro\WWW\letucrm2\app_www\houtai\view\common\login_css.html";i:1576676556;s:69:"E:\phpstudy_pro\WWW\letucrm2\app_www\houtai\view\common\login_js.html";i:1576676556;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>登录-<?php echo config('site_title'); ?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="description" content="">
    <link rel="shortcut icon" href="/favicon.ico"/>
<link rel="bookmark" href="/favicon.ico"/>
<link rel="stylesheet" href="/static/houtai/css/weui.min.css">
<link rel="stylesheet" href="/static/houtai/css/jquery-weui.min.css">
<link rel="stylesheet" href="/static/houtai/css/font-awesome.min.css">
<link rel="stylesheet" href="/static/houtai/css/login.css">
</head>
<body ontouchstart>
<div class="login_site_title">
    <img class="letu_logo" src="/static/houtai/images/letu/letu_logo.png" />
</div>
<div class="login_form">
    <form id="frmSubmit" method="post" class="m-t form-horizontal" action="<?php echo url('login'); ?>">
        <div class="login_msg"><span id="login_tips"></span></div>
        <div class="weui-cells" style="margin-top: 0px;">
            <div class="weui-cell">
                <span class="letu_icon fa fa-user"></span>
                <div class="weui-cell__bd">
                    <input type="text" id="username" name="username" class="weui-input my-input" placeholder="手机号/邮箱/账号" maxlength="50" />
                </div>
            </div>
            <div class="weui-cell">
                <span class="letu_icon fa fa-unlock-alt"></span>
                <div class="weui-cell__bd">
                    <input type="password" id="userpass" name="userpass" class="weui-input my-input" placeholder="登录密码" maxlength="50" />
                </div>
            </div>
            <div class="weui-cell">
                <span class="letu_icon fa fa-picture-o"></span>
                <div class="weui-cell__bd">
                    <input type="text" id="verify" name="verify" style="width: 58%;" class="weui-input login_verify_input" placeholder="验证码" maxlength="4" /><img id="img_verify" src="<?php echo url('verify'); ?>" data-src="<?php echo url('verify'); ?>" class="login_verify_img" />
                </div>
            </div>
        </div>
        <div class="weui-btn-area">
            <button type="submit" class="weui-btn weui-btn_primary">确定登录</button>
        </div>
    </form>
</div>
<script src="/static/houtai/js/jquery-2.1.4.min.js"></script>
<script src="/static/houtai/js/fastclick.js"></script>
<script src="/static/houtai/js/jquery-weui.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        FastClick.attach(document.body);
    });
    function goBack(url){
        if(history.length > 1){
            history.back();
        }else{
            if(url != ""){
                window.location.href = url;
            }
        }
    }
</script>
<script src="/static/houtai/js/jquery.form.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#img_verify").click(function(){
            var $this = $(this);
            var new_img_url = $this.attr("data-src") + "?t=" + (new Date()).getTime();
            $this.attr("src",new_img_url);
        });

        var timeout;

        $("#frmSubmit").ajaxForm({
            dataType:'json',
            success:function(data){
                var login_tips = $('#login_tips');
                if(data.success){
                    login_tips.attr("class","login_success").html(data.msg);
                    if(data.dataType == "url"){
                        clearTimeout(timeout);

                        timeout = setTimeout(function(){
                            window.location.href = data.url;
                        },2000);
                    }
                }else{
                    login_tips.attr("class","login_error").html(data.msg);
                }
            }
        });
    });
</script>
</body>
</html>