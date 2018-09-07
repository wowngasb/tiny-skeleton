<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="keywords"
          content="admin, dashboard, bootstrap, template, flat, modern, theme, responsive, fluid, retina, backend, html5, css, css3">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
    <link rel="shortcut icon" href="#" type="image/png">

    <title>{{ $mgr_title }}</title>

    <link rel="stylesheet" type="text/css" href="{{$common_cdn}}/js/bootstrap/css/bootstrap.min.css?v={{$webver}}">
    <link rel="stylesheet" type="text/css" href="{{$common_cdn}}/fonts/css/font-awesome.min.css?v={{$webver}}">
    <link href="{{$common_cdn}}/js/webuploader/webuploader.css?v={{$webver}}" rel="stylesheet" type="text/css">
    <link href="{{$cdn}}/assets/backend/css/style.css?v={{$webver}}" rel="stylesheet">
    <link href="{{$cdn}}/assets/backend/css/style-responsive.css?v={{$webver}}" rel="stylesheet">

    <link href="{{$cdn}}/assets/backend/css/crash-common.css?v={{$webver}}" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="{{$common_cdn}}/js/html5shiv.js?v={{$webver}}"></script>
    <script src="{{$common_cdn}}/js/respond.min.js?v={{$webver}}"></script>
    <![endif]-->

    <script type="text/javascript">

        var WEB_ENVIRON = '{{ $app::config('ENVIRON') }}';
        if (!(WEB_ENVIRON && WEB_ENVIRON === 'debug')) {
            window.console = console || {};
            window.console._log = window.console.log || function () {
            };
            window.console.log = function () {
            };
            window.console._warn = window.console.warn || function () {
            };
            window.console.warn = function () {
            };
            window.console._trace = window.console.trace || function () {
            };
            window.console.trace = function () {
            };
        }
        window.D = {
            agentBrowser: {!! !empty($agentBrowser) ? json_encode($agentBrowser) : '[]' !!},
            cdn: '{{$cdn}}',
            ver: '{{$webver}}'
        };
    </script>

    @yield('head')
    @stack('styles')

</head>

<body class="sticky-header">

<section>
    <!-- left side start-->
    <div class="left-side sticky-left-side">

        <!--logo and iconic logo start-->
        <div class="logo">
            <a href="{{ $base_uri }}">
                <img style="width: 200px;height: 40px;"  src="{{$cdn}}/assets/backend/images/logo.png?v={{$webver}}" alt=""></a>
        </div>

        <div class="logo-icon text-center">
            <a href="{{ $base_uri }}">
                <img style="width: 40px;height: 40px;" src="{{$cdn}}/assets/backend/images/logo_icon.png?v={{$webver}}" alt=""></a>
        </div>
        <!--logo and iconic logo end-->

        <div class="left-side-inner">

            <!--sidebar nav start-->
            <ul class="nav nav-pills nav-stacked custom-nav">
                @foreach($menus as $category => $childs)
                    @if(isset($childs['childs']))
                        <li data-path="{{$ctrl->path('/')}}"
                            data-key="{{$category}}"
                            class="menu-list {{ \app\Util::v($childs['active_map'], $ctrl->path('/')) ? 'nav-active' : 'none-active' }}"
                        >
                            <a href="">
                                <i class="fa {{$childs['icon']}}"></i>
                                <span>{{$childs['name']}}</span>
                            </a>
                            <ul class="sub-menu-list">
                                @foreach($childs['childs'] as $key => $child)
                                    <li data-path="{{$ctrl->path('/')}}"
                                        data-key="{{$key}}"
                                        class="{{ \app\Util::v($child['active_map'], $ctrl->path('/')) ? 'active' : 'none-active'  }}">
                                        <a href="{{$key}}">{{$child['name']}}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li data-path="{{$ctrl->path('/')}}"
                            data-key="{{$category}}"
                            class="{{ \app\Util::v($childs['active_map'], $ctrl->path('/')) ? 'active' : 'none-active'  }}"
                        >
                            <a href="{{$category}}">
                                <i class="fa {{$childs['icon']}}"></i>
                                <span>{{$childs['name']}}</span>
                            </a>
                        </li>
                    @endif
                @endforeach

            </ul>
            <!--sidebar nav end-->
        </div>
    </div>
    <!-- left side end-->

    <!-- main content start-->
    <div class="main-content">

        <!-- header section start-->
        <div class="header-section">

            <!--toggle button start-->
            <a class="toggle-btn">
                <i class="fa fa-bars"></i>
            </a>

            <!--notification menu start -->
            <div class="menu-right">
                <ul class="notification-menu">
                    <li>
                        <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            {{ $base_title }}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-usermenu pull-right">
                            <li>
                                <a id="btn-edit-password" href="#">
                                    <i class="fa fa-shield"></i>
                                    修改密码
                                </a>
                            </li>
                            <li>
                                <a href="/mgr/auth/logout">
                                    <i class="fa fa-sign-out"></i>
                                    登出
                                </a>
                            </li>
                        </ul>
                    </li>

                </ul>
            </div>
            <!--notification menu end -->

        </div>
        <!-- header section end-->

        <!--body wrapper start-->
        <div class="wrapper">
            @yield('content')
        </div>
        <!--body wrapper end-->

        <!--footer section start-->
        <footer>2018 &copy;管理后台</footer>
        <!--footer section end-->

    </div>
    <!-- main content end-->
</section>

<!-- Placed js at the end of the document so the pages load faster -->
<script type="text/javascript" src="{{$cdn}}/assets/mod.js?v={{$webver}}"></script>
<script type="text/javascript"
        src="{{$cdn}}/assets/{{ $app::dev() ? 'jquery.js' : 'jquery.min.js' }}?v={{$webver}}"></script>
<script type="text/javascript" src="{{$cdn}}/assets/base-polyfill.js?v={{$webver}}"></script>
<script type="text/javascript" src="{{$cdn}}/assets/fetch.js?v={{$webver}}"></script>
<script type="text/javascript"
        src="{{$cdn}}/assets/{{ $app::dev() ? 'bluebird.js' : 'bluebird.min.js' }}?v={{$webver}}"></script>

<script type="text/javascript" src="{{$common_cdn}}/js/black_clientv3.js?v={{$webver}}"></script>
@if($app::config('services.black.dms_host', ''))
    <script type="text/javascript">
        if (typeof ROP !== 'undefined') {
            ROP.ICS_ADDR = "{{ $app::config('services.black.dms_host', '') }}";
            ROP.CDN_ADDR = "{{ $app::config('services.black.dms_cdn_host', '') }}";
        }
    </script>
@endif
<script type="text/javascript" src="{{$cdn}}/assets/dmshub.js?v={{$webver}}"></script>

<script src="{{$common_cdn}}/js/jquery-ui-1.9.2.custom.min.js?v={{$webver}}"></script>
<script src="{{$common_cdn}}/js/jquery-migrate-1.2.1.min.js?v={{$webver}}"></script>
<script src="{{$common_cdn}}/js/bootstrap/js/bootstrap.min.js?v={{$webver}}"></script>
<script src="{{$common_cdn}}/js/modernizr.min.js?v={{$webver}}"></script>
<script src="{{$common_cdn}}/js/jquery.nicescroll-3.6.0/jquery.nicescroll.min.js?v={{$webver}}"></script>
<script src="{{$common_cdn}}/js/webuploader/webuploader.js?v={{$webver}}"></script>

<!--common scripts for all pages-->
<script src="{{$cdn}}/assets/backend/js/scripts.js?v={{$webver}}"></script>
<script src="{{$cdn}}/assets/backend/dlg.js?v={{$webver}}"></script>
<script src="{{$cdn}}/assets/backend/formvail.js?v={{$webver}}"></script>
<script src="{{$cdn}}/assets/backend/My97DatePicker/WdatePicker.js?v={{$webver}}"></script>

<script type="text/javascript" src="{{$cdn}}/static/api/XdyIsvMgr.js?v={{$webver}}"></script>
<script type="text/javascript" src="{{$cdn}}/static/api/AdminMgr.js?v={{$webver}}"></script>
<script type="text/javascript" src="{{$cdn}}/static/api/SiteAuthMgr.js?v={{$webver}}"></script>

<script type="text/javascript">
    !(function () {
        var apiList = [
            'AdminMgr', 'ApiHub', 'AuthMgr', 'DataAnalysis', 'GraphQLApi', 'HotFix', 'RoomAssist', 'RoomMgr', 'StreamMgr', 'RoomRecord', 'XdyIsvMgr', 'SiteAuthMgr','PlayerMgr'
        ];
        var cdn = window.D && window.D.cdn || '';
        var ver = window.D && window.D.ver || '0.0';
        var resMap = {};
        for (var i = 0; i < apiList.length; i++) {
            var key = "static/api/" + apiList[i] + ".js";
            resMap[key] = {"url": cdn + "/static/api/" + apiList[i] + ".js?v=" + ver};
        }
        require.resourceMap({
            "res": resMap
        });
    })();

    var XdyIsvMgr = require('static/api/XdyIsvMgr');
    var AdminMgr = require('static/api/AdminMgr');
    var SiteAuthMgr = require('static/api/SiteAuthMgr');
    var dms = new DmsHub();

    $(function () {
        //高级查询操作
        $('.common-find').click(function () {
            if ($('.common-search').hasClass('common-search-show')) {
                $('.common-search').removeClass('common-search-show');
                $('input[name="common_search"]').val('off');
            } else {
                $('.common-search').addClass('common-search-show');
                $('input[name="common_search"]').val('on');
            }
        });
    });

    //日期检查
    function dataCheck() {
        var map = {
            create_time: '创建',
            start_time: '开始',
            end_time: '结束',
            in_time: '登入',
            out_time: '登出',
            login_time: '最后登录'
        };
        for (var name in map) {
            if ($('input[name=' + name + '_e]').val() && $('input[name=' + name + '_s]').val() && $('input[name=' + name + '_e]').val() <= $('input[name=' + name + '_s]').val()) {
                dlg(map[name] + '时间输入错误：结束时间必须大于开始时间！');
                return false;
            }
        }
        if (parseInt($('input[name=interval_time_e]').val()) <= parseInt($('input[name=interval_time_s]').val())) {
            dlg('时长输入错误：结束时长必须大于开始时长！');
            return false;
        }
        if ($('input[name=endTime]').val() && $('input[name=startTime]').val() && $('input[name=endTime]').val() <= $('input[name=startTime]').val()) {
            dlg('创建时间输入错误：结束时间必须大于开始时间！');
            return false;
        }
    }

    //清除操作
    $('.common-clear').click(function (e) {
        e.preventDefault();
        $('.common-search').find('form input[type=text]').each(function () {
            $(this).val('');
        });
        $('.common-search').find('form input[type=hidden]').each(function () {
            if ($(this).attr('name') != 'hook_admin_id' && $(this).attr('name') != 'is_vod') {
                $(this).val('');
            }
        });
        $('.common-search').find('form input[type=checkbox]').each(function () {
            $(this).attr('checked', false);
        });
        $('.common-search').find('form select').each(function () {
            $(this).val('');
        });
        $('input[name=common_search]').val('on');
    });

    $(function () {
        $('.d_clip_button').each(function () {
            clipboard(this, '复制成功');
        });
        //菜单变换
        $('.navbor-list').click(function () {
            if ($('.left-nav').hasClass('left-nav-fold')) {
                $('.left-nav').removeClass('left-nav-fold');
                $(this).css('left', '185px');
                $('#page-wrapper').css('left', '158px');
            } else {
                $('.left-nav').addClass('left-nav-fold');
                $(this).css('left', '66px');
                $('#page-wrapper').css('left', '41px');
            }
        });
    });

    function clipboard(btn, msg) {
        if (window.clipboardData) {        //for ie
            $(btn).on('click', function () {
                var target = $(btn).attr('data-clipboard-target');
                var text = $('#' + target).val() || $('#' + target).text();
                window.clipboardData.setData('text', text);
                alert(msg);
            });
        } else {
            var clip = new ZeroClipboard($(btn));
            clip.on("ready", function () {
                this.on("aftercopy", function (event) {
                    alert(msg);
                });
            });
            clip.on("error", function (event) {
                ZeroClipboard.destroy();
            });
        }
    }
</script>


<div class="modal fade" id="set_pwd_model">
    <div class="modal-dialog" style="width: 380px;">
        <div class="modal-content message_align">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                <h4 class="modal-title">修改密码</h4>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <label for="childrenCompany" class="col-sm-4 control-label">原密码：</label>
                    <div class="col-sm-12">
                        <input type="password" class="form-control" id="old_pasw" value="">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="loginName" class="col-sm-4 control-label">新密码：</label>
                    <div class="col-sm-12">
                        <input type="password" class="form-control" id="new_pasw" value="">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="loginName" class="col-sm-4 control-label">重复新密码：</label>
                    <div class="col-sm-12">
                        <input type="password" class="form-control" id="repeat_pasw" value="">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <a id="set-admin-pwd" class="btn btn-success" data-dismiss="modal">确定</a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<script type="text/javascript">
    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ $ctrl->csrf_token() }}'
            }
        });

        function show_pwd_cfm() {
            $('#old_pasw').val('');
            $('#new_pasw').val('');
            $('#repeat_pasw').val('');
            $('#set_pwd_model').modal('toggle');
        }

        function set_admin_pwd() {
            var old_pasw = $('#old_pasw').val(),
                new_pasw = $('#new_pasw').val(),
                repeat_pasw = $('#repeat_pasw').val();

            if (old_pasw.length == 0 || new_pasw.length == 0 || repeat_pasw.length == 0) {
                dlg('请输入原密码和新密码！', 5000);
                return;
            }
            if (new_pasw != repeat_pasw) {
                dlg('两次输入的密码不一致！', 5000);
                return;
            }
            var load = function () {
                SiteAuthMgr.setSelfPaswAdmin({old_pasw: old_pasw, new_pasw: new_pasw}).then(function (res) {
                    dlg(res.msg);
                }).catch(function (res) {
                    dlg(res.msg, 5000);
                }).finally(function () {
                    dlgLoadingHide();
                });
            };
            dlgLoading(load);
        }

        $('#btn-edit-password').on('click', function () {
            show_pwd_cfm();
        });
        $('#set-admin-pwd').on('click', function () {
            set_admin_pwd();
        });
    });
</script>

</body>
@yield('script')
@stack('scripts')
</html>