<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
    <link rel="shortcut icon" href="#" type="image/png">

    <title>新用户注册</title>
    <link rel="stylesheet" type="text/css" href="{{$common_cdn}}/js/bootstrap/css/bootstrap.min.css?v={{$webver}}">
    <link rel="stylesheet" type="text/css" href="{{$common_cdn}}/fonts/css/font-awesome.min.css?v={{$webver}}">
    <link href="{{$cdn}}/assets/backend/css/style.css?v={{$webver}}" rel="stylesheet">
    <link href="{{$cdn}}/assets/backend/css/style-responsive.css?v={{$webver}}" rel="stylesheet">
    <style>
        .container {
            float: inherit !important;
            text-align: center;
            margin-top: 40px;
        }

        .btn-group-lg > .btn {
            border: 0px none !important;
        }

        input {
            width: 100%;
            border: 0;
            outline: 0;
            -webkit-appearance: none;
            background-color: transparent !important;
            font-size: inherit;
            color: inherit;
            border: 0px none !important;
        }

        .form-signin .form-control {
            height: 40px;
            margin-bottom: 10px;
            padding: 6px;
        }

        .form-control {
            padding: 6px;
        }

        .volidatecode {
            width: 66%;
            position: relative;
        }

        .sp-pop {
            position: absolute;
            left: 238px;
            display: inline-block;
            width: 100px;
            top: -1px;
            height: 40px;
        }

        .sp-img {
            background: yellow;
            cursor: pointer;
        }

        .sp-btn {
            /* background:#2d8cf0; */
            color: #fff;
            border-radius: 4px;
            font-size: 16px;
        }

        .sentcode {
            height: 40px;
            line-height: 40px;
            background: #2d8cf0 !important;
            border-radius: 4px;
        }

        .countdown {
            background-color: #ccc !important;
        }

        /*去掉输入框黄色*/
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-transition-delay: 99999s;
            -webkit-transition: color 99999s ease-out, background-color 99999s ease-out;
        }
    </style>
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="{{$common_cdn}}/js/html5shiv.js?v={{$webver}}"></script>
    <script src="{{$common_cdn}}/js/respond.min.js?v={{$webver}}"></script>
    <![endif]-->
    <script type="text/javascript">
        var WEB_ENVIRON = '{{ $app::config('ENVIRON') }}';
        if (!(WEB_ENVIRON && WEB_ENVIRON == 'debug')) {
            window.console = console || {};
            window.console._log = window.console.log || function () {
            };
            window.console.log = function () {
            };
            window.console._warn = window.console.warn || function () {
            };
            window.console.warn = function () {
            };
        }
        window.D = {
            cdn: '{{$cdn}}',
            ver: '{{$webver}}'
        };
    </script>

</head>

<body class="login-body">

<div class="container" style="float: right;width: 420px;opacity: 0.9;">
    <a href="/">
        <img src="{{$cdn}}/assets/backend/images/logotxt.png?v={{$webver}}" width="420" alt=""/>
    </a>

    <form class="form-signin">
        <div class="form-signin-heading text-center">
            <h1 class="sign-title">用户注册</h1>
        </div>
        <div class="login-wrap">
            <div class="form-group" id="error-view" style="display:none;">
                <div>
                    <div id="js-validate-alert" class="alert alert-danger alert-validate" style="margin-bottom: 0px;">
                        <span class="text"></span>
                    </div>
                </div>
            </div>

            <div class="form-control">
                <span class="sp-input"><input type="text" name="txtPhone" id="txtPhone" placeholder="手机号"
                                              onblur="checkPhone()"></span>
            </div>

            <div class="form-control volidatecode">
           <span class="sp-input"><input type="text" name="txtCode" id="txtCode" placeholder="验证码" autocomplete="off"/>
           </span>
                <span class="sp-pop sp-img "> <img id="imgCode" src="" width="100" height="40" alt=""/></span>
            </div>

            <div class="form-control volidatecode">
           <span class="sp-input"><input type="text" name="txtPhoneCode" id="txtPhoneCode" placeholder="短信验证码"
                                         autocomplete="off"/>
           </span>
                <span class="sp-pop sp-btn"> <input type="button" class="sentcode" id="sentcode" width="100"
                                                    onclick="sendPhoneCode()" value="发送验证码" height="40" alt=""/></span>
            </div>

            <div class="form-control">
                <span class="sp-input"><input type="password" name="txtPwd" id="txtPwd" placeholder="6-18位密码" value=""
                                              onblur="checkPwd()"></span>
            </div>
            <div class="form-control">
                <span class="sp-input"><input type="password" name="txtPwdConf" id="txtPwdConf" placeholder="确认密码"
                                              value="" onblur="checkPwd2()"></span>
            </div>

            <button class="btn btn-lg btn-login btn-block" type="button" onclick="checkRegister()"> 立 即 注 册</button>
            <a class="a-gologin" href="/auth/login">去登录</a>
        </div>
    </form>

</div>

</body>
<script type="text/javascript" src="{{$cdn}}/assets/mod.js?v={{$webver}}"></script>
<script type="text/javascript" src="{{$common_cdn}}/js/jquery/jquery.min.js?v={{$webver}}"></script>
<script type="text/javascript" src="{{$cdn}}/assets/base-polyfill.js?v={{$webver}}"></script>
<script type="text/javascript" src="{{$cdn}}/static/api/AuthMgr.js?v={{$webver}}"></script>

<script type="text/javascript">
    var AuthMgr = require('static/api/AuthMgr');
    var txtPhone = $("#txtPhone");
    var txtCode = $("#txtCode");
    var txtPhoneCode = $("#txtPhoneCode");
    var txtPwd = $("#txtPwd");
    var txtPwdConf = $("#txtPwdConf");

    var smsTimer = null;
    var TotalMilliSeconds = null;

    $(function () {
        getCheckCode();
        $("#imgCode").click(function () {
            getCheckCode();
        })
    })

    //获取验证码
    function getCheckCode() {
        var dateTime = new Date().getTime();
        var captchaUrl = '/helper/checkcode.php?_t=' + dateTime;
        $("#imgCode").attr("src", captchaUrl);
    }

    function errorMsg(msg) {
        $("#error-view").show();
        $(".text").html(msg);
        return false;
    }

    function successMsg(msg) {
        $("#error-view").hide();
        $(".text").html('');
        return true;
    }

    //检查 
    function checkPhone() {
        AuthMgr.isFreeAdminPhoneNum({  //检查手机号 是否已经注册
            'phone_num': txtPhone.val()
        }, function (resp) {
            return true;
        }, function (resp) {
            errorMsg(resp.msg)
        })
    }

    function _takeCount() {
        TotalMilliSeconds--;
        if (TotalMilliSeconds == 0) {
            clearInterval(smsTimer);
            $("#sentcode").val("重发验证码");
            $('#sentcode').removeAttr("disabled"); //可以点击
            $("#sentcode").removeClass("countdown");//按钮蓝色
            return;
        }
        $("#sentcode").val("[" + TotalMilliSeconds + "]秒重发");
    }

    function _execCount() {
        $("#sentcode").val("[" + TotalMilliSeconds + "]秒重发");
        $("#sentcode").attr("disabled", "true");//无法点击
        $("#sentcode").addClass("countdown");//按钮灰色
        smsTimer = setInterval(function () {
            _takeCount()
        }, 1000);
    }

    //发送手机验证码
    function sendPhoneCode() {
        if (TotalMilliSeconds > 0) return;
        AuthMgr.sendRegisterSmsCode({
            "phone_num": txtPhone.val(),
            "checkcode": txtCode.val()  //用户输入的 图形验证码
        }, function (resp) {
            TotalMilliSeconds = resp.msg_interval;
            _execCount(); //倒计时
        }, function (resp) {
            getCheckCode();
            errorMsg(resp.msg);
            TotalMilliSeconds = 10;//10s后重发
            _execCount()
        })
    }

    //验证密码输入
    function _checkPwd() {
        var regex = new RegExp('(?=.*[0-9])(?=.*[a-zA-Z])(?=.*[^a-zA-Z0-9]).{6,18}'); //字母 数字、特殊字符 6-18位
        if (regex.test(txtPwd.val())) {
            return true;
        } else {
            errorMsg("密码必须是6-18位的字母， 数字、特殊字符！ ")
        }
    }

    function checkPwd() {
        if (txtPwd.val().length < 6) {
            errorMsg("密码必须是6-18位！")
        }
        return true;
    }

    function checkPwd2() {
        if (!txtPwdConf.val()) {
            errorMsg("请先输入密码！")
        }
        if (txtPwdConf.val() != txtPwd.val()) {
            errorMsg("两次密码不一致！")
        } else {
            return true;
        }
    }

    //注册
    function checkRegister() {
        if (!checkPwd() || !checkPwd2()) {
            alert("请先检查出错信息！");
            return false;
        }
        AuthMgr.newAdminBySmsCode({
            "phone_num": txtPhone.val(),
            "pasw": txtPwd.val(),
            "authcode": txtPhoneCode.val(), //手机验证码
            "token": ""
        }, function (resp) {
            alert("注册成功！");
            window.location.href = "/auth/login";
        }, function (resp) {
            errorMsg(resp.msg)
        })
    }

</script>

</html>
