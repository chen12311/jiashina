<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:76:"/www/wwwroot/www.jiajiazxgg.com/public/../webapp/admin/view/index/login.html";i:1591931571;}*/ ?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
    <head>
        <meta charset="utf-8" />
        <title><?php echo getData("basic",['id'=>1],"title"); ?></title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1" name="viewport" />
        <meta content="" name="description" />
        <meta content="" name="author" />
        <link href="__CSS__/components.min.css" rel="stylesheet" id="style_components" type="text/css" />
        <link href="__CSS__/plugins.min.css" rel="stylesheet" type="text/css" />
        <link href="__CSS__/login-2.min.css" rel="stylesheet" type="text/css" />
        <link href="__JS__/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="__JS__/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
        <script src="__JS__/jquery.min.js" type="text/javascript"></script>
        <script src="__JS__/formvalidator.js"></script>
        <script src="__JS__/formvalidatorregex.js"></script>
        <link rel="shortcut icon" href="__ROOT__/favicon.ico" />
        <style>
            .form-group div{
                color: #ffffff;
            }
            #code{
                float: left;  width: 65%;
            }
            .captcha{
                float: right;  height: 43px;
            }
            .form-actions{
                margin-top: 80px;
            }
        </style>
    </head>

    <body class=" login">
        <div class="logo">
            <img src="__IMG__/logo-big-white.png" style="height: 36px;" alt="" />
        </div>
        <div class="content">
            <form method="post" action="" class="am-form" id="myform" >
                <div class="form-title">
                    <span class="form-title">欢迎您.</span>
                    <span class="form-subtitle">请登录.</span>
                </div>
                <div class="alert alert-danger display-hide">
                    <button class="close" data-close="alert"></button>
                    <span> 请输入用户名密码. </span>
                </div>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">管理员账户</label>
                    <input class="form-control form-control-solid placeholder-no-fix" type="text" name="username" id="username" autocomplete="off" placeholder="管理员账户"/>
                </div>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">密码</label>
                    <input class="form-control form-control-solid placeholder-no-fix" type="password" name="password" id="password" autocomplete="off" placeholder="密码"  />
                </div>
                <?php if(session('admin_logo_fail') >= 2): ?>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">验证码</label>
                    <input class="form-control form-control-solid placeholder-no-fix" type="text" name="code" id="code" autocomplete="off" placeholder="验证码" />
                    <img onclick="this.src=this.src" src="<?php echo captcha_src(1); ?>" class="captcha">
                </div>
                <?php endif; ?>
                <div class="form-actions" style="margin-top: 0px;">
                    <button type="submit" class="btn red btn-block uppercase">登 录</button>
                </div>
            </form>
        </div>
        <div class="copyright text-center">
            <a href="<?php echo getData('basic',['id'=>1],'icpurl'); ?>" target="_blank" style="color: #fff;"><?php echo getData("basic",['id'=>1],"icp"); ?></a>
        </div>

        <script language="JavaScript">
            $(function(){
                $.formValidator.initConfig({autotip:true,formid:"myform",onerror:function(msg){}});
                $("#username").formValidator({onshow:"请输入用户名",onfocus:"请输入用户名"}).inputValidator({min:2,max:20,onerror:"用户名应该为2-20位之间"}).regexValidator({regexp:"ps_username",datatype:"enum",onerror:"用户名格式错误"});
                $("#password").formValidator({onshow:"请输入密码",onfocus:"请输入密码"}).inputValidator({min:6,max:20,onerror:"密码应该为6-20位之间"});
            })
        </script>

    </body>
</html>