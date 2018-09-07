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

    <title>管理后台登陆</title>
    <link rel="stylesheet" type="text/css" href="{{$common_cdn}}/js/bootstrap/css/bootstrap.min.css?v={{$webver}}">
    <link rel="stylesheet" type="text/css" href="{{$common_cdn}}/fonts/css/font-awesome.min.css?v={{$webver}}">
    <link href="{{$cdn}}/assets/backend/css/style.css?v={{$webver}}" rel="stylesheet">
    <link href="{{$cdn}}/assets/backend/css/style-responsive.css?v={{$webver}}" rel="stylesheet">

    <style>
        .container {
            float: inherit !important;
            text-align: center;
            margin-top: 6%;
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
    </style>
    <style>
        .weui-slider-box {
            display: -webkit-box;
            display: -webkit-flex;
            display: flex;
            -webkit-box-align: center;
            -webkit-align-items: center;
            align-items: center;
            margin-bottom: 10px;

        }

        .weui-slider-box .weui-slider {
            -webkit-box-flex: 1;
            -webkit-flex: 1;
            flex: 1;
        }

        .weui-slider {
            padding: 15px 18px;
            -webkit-user-select: none;
            user-select: none;
        }

        .weui-slider__inner {
            position: relative;
            height: 2px;
            background-color: #E9E9E9;
        }

        .weui-slider__track {
            width: 100%;
            height: 2px;
            /* background-color: #1AAD19; */
            width: 0;
        }

        .weui-slider__handler {
            position: absolute;
            left: 0;
            bottom: -5px;
            width: 14px;
            height: 14px;
            margin-left: -14px;
            margin-top: -14px;
            border-radius: 50%;
            background-color: #FFFFFF;
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.2);
        }

        .weui-slider-box__value {
            margin-left: .5em;
            min-width: 24px;
            color: #808080;
            text-align: center;
            font-size: 14px;
        }

        .stepbox {
            width: 100%;
        }

        .countdown {
            background-color: #ccc !important;
        }

        .step {
            display: inline-block;
            float: left;
            width: 33%;
        }

        .onactive {
            color: #1AAD19;
            background-color: #1AAD19;
        }

        .onactive .weui-slider__handler {
            box-shadow: 0 0 4px #1AAD19;
            color: #1AAD19;
        }

        .form-signin .form-signin-heading {
            width: 340px !important;
            border-bottom: 1px solid #2d8cf0;
            padding-bottom: 37px;
            margin: 0 auto;
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

<body class="login-body ">

<div class="container" style="float: right;width: 420px;opacity: 0.9;">
    <a href="/">
        <img src="{{$cdn}}/assets/backend/images/logotxt.png?v={{$webver}}" width="420" alt=""/>
    </a>
    <form class="form-signin" action="/auth/login" method='POST'>
        <div class="form-signin-heading text-center">
            <h1 class="sign-title">密码找回</h1>
        </div>
        <div class="login-wrap">

            <div>
                <!-- 进度条展示块 -->
                <div class="page__bd page__bd_spacing">

                    <br>
                    <div class="weui-slider-box">
                        <div class="weui-slider stepbox">
                            <div id="sliderInner" class="weui-slider__inner step onactive">
                                <div id="sliderTrack" style="width:100%;" class="weui-slider__track"></div>
                                <div id="sliderHandler" style="left: 100%;" class="weui-slider__handler"></div>
                            </div>
                            <div id="sliderInner" class="weui-slider__inner step">
                                <div id="sliderTrack" style="width:100%;" class="weui-slider__track"></div>
                                <div id="sliderHandler" style="left: 100%;" class="weui-slider__handler"></div>
                            </div>
                            <div id="sliderInner" class="weui-slider__inner step">
                                <div id="sliderTrack" style="width:100%;" class="weui-slider__track"></div>
                                <div id="sliderHandler" style="left: 100%;" class="weui-slider__handler"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- 错误信息提示块 -->
            <div class="form-group" style="display:none;">
                <div>
                    <div id="js-validate-alert" class="alert alert-danger alert-validate" style="margin-bottom: 0px;">
                        <span class="text"></span>
                    </div>
                </div>
            </div>
            <!-- step1 -->
            <div id="view-step1">
                <div class="form-control">
                    <span class="sp-input"><input type="text" name="txtAccount" id="txtAccount" placeholder="账号"
                                                  autocomplete="off"/></span>
                </div>
                <div class="form-control volidatecode">
               <span class="sp-input"><input type="text" name="txtCode" id="txtCode" placeholder="验证码">
            </span>
                    <span class="sp-pop sp-img "> <img id="imgCode" src="" width="100" height="40" alt=""/></span>
                </div>

                <button class="btn btn-lg btn-login btn-block" type="button" onclick="getValidateType()">确 定</button>
            </div>


            <!-- step2 发送手机验证码-->
            <div id="view-step2" style=" display:none;">
                <div class="form-control">
                    <span class="sp-input"><input type="text" name="txtPhone" id="txtPhone" placeholder="手机号" disabled></span>
                </div>

                <div class="form-control volidatecode">
               <span class="sp-input"><input type="text" name="txtPhoneCode" id="txtPhoneCode" placeholder="短信验证码"
                                             autocomplete="off">
            </span>
                    <span class="sp-pop sp-btn"> <input type="button" class="sentcode" id="sentcode" width="100"
                                                        onclick="sendPhoneCode()" value="发送验证码" height="40"
                                                        alt=""/></span>
                </div>
                <button class="btn btn-lg btn-login btn-block" type="button" onclick="checkAuthCode()">确 定</button>
            </div>

            <!-- step3 -->
            <!-- 设置密码提示框 -->
            <div id="view-step3" class="setPwd" style="display:none">
                <div class="form-control">
                    <span class="sp-input"><input type="password" name="txtPwd" id="txtPwd" placeholder="6-18位密码"
                                                  value="" onblur="checkPwd()"></span>
                </div>
                <div class="form-control">
                    <span class="sp-input"><input type="password" name="txtPwdConf" id="txtPwdConf" placeholder="确认密码"
                                                  value="" onblur="checkPwd2()"></span>
                </div>
                <button class="btn btn-lg btn-login btn-block" type="button" onclick="resetPwd()">确 定</button>
            </div>

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

    var txtAccount = $("#txtAccount");//账号
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

    //验证密码输入
    function checkPwd() {
        if (txtPwd.val().length < 6) {
            errorMsg("密码必须是6-18位！")
        } else {
            return true;
        }
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

    function _takeCount() {
        TotalMilliSeconds--;
        if (TotalMilliSeconds == 0) {
            clearInterval(smsTimer);
            $("#sentcode").val("重发验证码");
            $("#sentcode").attr("disabled", ""); //可以点击
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

    //根据账号 获取验证方式  第一步验证
    function getValidateType() {
        AuthMgr.getAuthByName({
            "name": txtAccount.val(),  //登陆账号
            "checkcode": txtCode.val() //图形验证码 必选
        }, function (resp) {
            var type_tel = resp.data.telephone;
            var type_email = resp.data.email;
            $("#view-step1").hide();
            $("#view-step2").show();
            $("#txtPhone").val(type_tel);
            $(".stepbox .step").eq(1).addClass("onactive")
        }, function (resp) {
            errorMsg(resp.msg);
        })
    }

    //发送短信
    function sendPhoneCode() {
        if (TotalMilliSeconds > 0) return;
        AuthMgr.sendAuthCode({
            "name": txtAccount.val(),
            "type": "telephone",
            "checkcode": txtCode.val()  //图形验证码
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

    //检查短信是否正确  第二步验证
    function checkAuthCode() {
        AuthMgr.checkAuthCode({
            "name": txtAccount.val(),
            "type": "telephone",
            "authcode": txtPhoneCode.val(),//短信 或者 邮件验证码 必选
        }, function (resp) {
            $("#view-step2").hide();
            $("#view-step3").show();
            $(".stepbox .step").eq(2).addClass("onactive")
            return true;
        }, function (resp) {
            errorMsg(resp.msg);
        })
    }

    //第三步 设置密码提交  
    function resetPwd() {
        if (!checkPwd() || !checkPwd2()) {
            alert("请先检查出错信息！");
            return
        }
        AuthMgr.setPwdByAuthCode({
            "name": txtAccount.val(),
            "type": "telephone",
            "authcode": txtPhoneCode.val(),   //验证码
            "new_pasw": txtPwd.val()
        }, function (resp) {
            alert("设置成功！");
            window.location.href = "/auth/login"
        }, function (resp) {
            errorMsg(resp.msg);
        })
    }

</script>
</html>
