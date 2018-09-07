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

    <title>财务后台登陆</title>
    <link rel="stylesheet" type="text/css" href="{{$common_cdn}}/js/bootstrap/css/bootstrap.min.css?v={{$webver}}">
    <link rel="stylesheet" type="text/css" href="{{$common_cdn}}/fonts/css/font-awesome.min.css?v={{$webver}}">
    <link href="{{$cdn}}/assets/backend/css/style.css?v={{$webver}}" rel="stylesheet">
    <link href="{{$cdn}}/assets/backend/css/style-responsive.css?v={{$webver}}" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="{{$common_cdn}}/js/html5shiv.js?v={{$webver}}"></script>
    <script src="{{$common_cdn}}/js/respond.min.js?v={{$webver}}"></script>
    <![endif]-->
</head>

<body class="login-body">

<div class="container">

    <form class="form-signin" action="/mgr/auth/login" method='POST'>
        <div class="form-signin-heading text-center">
            <img height="45px" src="{{$cdn}}/assets/backend/images/logo2.png" alt=""/>
        </div>
        <div class="login-wrap">
            {{$ctrl->csrf_field()}}
            @if($ctrl->errors_has('login'))
                <div class="form-group">
                    <div>
                        <div id="js-validate-alert" class="alert alert-danger alert-validate" style="margin-bottom: 0px;">
                            <span class="text">{{$ctrl->errors_first('login', ':message')}}</span>
                        </div>
                    </div>
                </div>
            @endif
            <input type="hidden" name="back" value="{{$back}}" >
            <input type="text" name="login" class="form-control" placeholder="用户名" value="{{$ctrl->old('login')}}" autofocus>
            <input type="password" name="password" class="form-control" placeholder="密码">

            <button class="btn btn-lg btn-login btn-block" type="submit">
                <i class="fa fa-check"></i>
            </button>
        </div>
    </form>

</div>

</body>
</html>
