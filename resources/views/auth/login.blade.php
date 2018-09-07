<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
    <link rel="Shortcut Icon" href="{{ !empty($siteConfig['site_ico']) ? $siteConfig['site_ico'] : "{$cdn}/favicon.ico?v={$webver}" }}">

    <title>管理后台登陆</title>
    <link rel="stylesheet" type="text/css" href="{{$common_cdn}}/js/bootstrap/css/bootstrap.min.css?v={{$webver}}">
    <link rel="stylesheet" type="text/css" href="{{$common_cdn}}/fonts/css/font-awesome.min.css?v={{$webver}}">
    <link href="{{$cdn}}/assets/backend/css/style.css?v={{$webver}}" rel="stylesheet">
    <link href="{{$cdn}}/assets/backend/css/style-responsive.css?v={{$webver}}" rel="stylesheet">

    <style>
        .container {
            float: inherit !important;
            text-align: center;
            margin-top: 8%;
        }

        .btn-group-lg > .btn {
            border: 0px none !important;
        }

        .sp-img {
            color: #000;
            display: inline-block;
            float: left;
            width: 30px;
            border-right: 1px solid #ccc
        }

        .sp-input {
            display: inline-block;
            float: right;
            width: 85%;
        }

        input {
            width: 100%;
            border: 0;
            outline: 0;
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

        /*去掉输入框黄色*/
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-transition-delay: 99999s;
            -webkit-transition: color 99999s ease-out, background-color 99999s ease-out;
        }

        .input-checkbox {
            clear:all;
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #ccc !important;
            float: left;
            margin-left:2px;
        }
        .for-remember{
            float: left;
            text-align: left;
            font-weight: inherit;
            line-height: 20px;
            font-size: 12px;
            margin-left: 2px;
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
        <img src="{{ !empty($siteConfig['login_logo']) ? $siteConfig['login_logo'] : "{$cdn}/assets/backend/images/logotxt.png?v={$webver}" }}"
             width="420" alt=""/>
    </a>
    <form class="form-signin" action="/auth/login" method='POST'>
        <div class="form-signin-heading text-center">
            <h1 class="sign-title">后台登录</h1>
        <!-- <img height="45px" src="{{$cdn}}/assets/backend/images/logo2.png?v={{$webver}}" alt=""/> -->
        </div>
        <div class="login-wrap">
            {{$ctrl->csrf_field()}}
            @if($ctrl->errors_has('login'))
                <div class="form-group">
                    <div>
                        <div id="js-validate-alert" class="alert alert-danger alert-validate"
                             style="margin-bottom: 0px;">
                            <span class="text">{{$ctrl->errors_first('login', ':message')}}</span>
                        </div>
                    </div>
                </div>
            @endif
            <input type="hidden" name="back" value="{{$back}}">

            <div class="form-control">
                <span class="sp-img"><img src="{{$cdn}}/assets/backend/images/user.png?v={{$webver}}" width="20"
                                          alt=""/></span>
                <span class="sp-input"><input type="text" name="login" placeholder="账号" value="{{$ctrl->old('login')}}"
                                              autofocus></span>
            </div>

            <div class="form-control">
                <span class="sp-img"><img src="{{$cdn}}/assets/backend/images/lock.png?v={{$webver}}" width="20"
                                          alt=""/></span>
                <span class="sp-input"><input type="password" name="password" placeholder="密码"></span>
            </div>
            <input type="checkbox" id="remember" name="remember"  class="input-checkbox"> 
            <label  class="for-remember" for="remember">记住密码</label>
            <button class="btn btn-lg btn-login btn-block" type="submit">登 录</button>
            @if(empty($siteAdmin))
                <a class="a-forgotpwd" href="/auth/getpwd">忘记密码</a> <a class="a-gologin" href="/register">去注册</a>
            @endif
        </div>
    </form>

</div>


</body>
<script type="text/javascript" src="{{$cdn}}/assets/mod.js?v={{$webver}}"></script>
<script type="text/javascript" src="{{$common_cdn}}/js/jquery/jquery.min.js?v={{$webver}}"></script>
<script type="text/javascript" src="{{$cdn}}/assets/base-polyfill.js?v={{$webver}}"></script>
<script type="text/javascript">

</script>
</html>
