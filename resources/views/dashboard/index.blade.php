<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <title>{{ $title }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0">
    <link rel="Shortcut Icon" href="{{ !empty($adminConfig['site_ico']) ? $adminConfig['site_ico'] : "{$cdn}/favicon.ico?v={$webver}" }}">

    <link rel="stylesheet" href="{{$cdn}}/assets/dist/main.css?v={{$webver}}">
    <link rel="stylesheet" name="theme" href="">
        <script type="text/javascript">

            var WEB_ENVIRON = '{{ $app::config('ENVIRON') }}';
            if(!(WEB_ENVIRON && WEB_ENVIRON == 'debug')){
                window.console = console || {};
                window.console._log = window.console.log || function () {};
                window.console.log = function () {};
                window.console._warn = window.console.warn || function () {};
                window.console.warn = function () {};
                window.console._trace = window.console.trace || function () {};
                window.console.trace = function () {};
            }
            window.D = {
                firstBaseAgentId: parseInt('{{ !empty($firstBaseAgentId) ? $firstBaseAgentId : 0 }}'),
                agentBrowser: {!! !empty($agentBrowser) ? json_encode($agentBrowser) : '[]' !!},
                codeHost: '{{ !empty($codeHost) ? $codeHost : $baseHost }}',
                cdn: '{{$cdn}}',
                ver: '{{$webver}}',
                token: '{{$token}}',
                adminConfig: {!! !empty($adminConfig) ? json_encode($adminConfig) : '{}' !!},
                dmsConfig: {!! !empty($dmsConfig) ? json_encode($dmsConfig) : '{}' !!}
            };
            window.user = {!! !empty($user) ? json_encode($user) : '{}' !!};
            window.pageInfo = {
                appRouterAllACL: {!! !empty($appRouterAllACL) ? json_encode($appRouterAllACL) : '{}' !!},
                appRouterACL: {!! !empty($appRouterACL) ? json_encode($appRouterACL) : '[]' !!},
                appRouter: {!! !empty($appRouter) ? json_encode($appRouter) : '{}' !!}
            };

            if(window.D.agentBrowser[0] == 'ie' && window.D.agentBrowser[1] <= 9){
                alert('agentBrowser error');
            }
        </script>
</head>

<body>
    <div id="app"></div>
    <div class="lock-screen-back" id="lock_screen_back"></div>
    <script type="text/javascript" src="{{$cdn}}/assets/mod.js?v={{$webver}}"></script>
    <script type="text/javascript" src="{{$cdn}}/assets/{{ $app::dev() ? 'jquery.js' : 'jquery.min.js' }}?v={{$webver}}"></script>
    <script type="text/javascript" src="{{$cdn}}/assets/base-polyfill.js?v={{$webver}}"></script>
    <script type="text/javascript" src="{{$cdn}}/assets/fetch.js?v={{$webver}}"></script>
    <script type="text/javascript" src="{{$cdn}}/assets/{{ $app::dev() ? 'bluebird.js' : 'bluebird.min.js' }}?v={{$webver}}"></script>

    <script type="text/javascript" src="{{$cdn}}/assets/pinyin/pinyin_dict_firstletter.js?v={{$webver}}"></script>
    <script type="text/javascript" src="{{$cdn}}/assets/pinyin/pinyin_dict_notone.js?v={{$webver}}"></script>
    <script type="text/javascript" src="{{$cdn}}/assets/pinyin/pinyin_dict_polyphone.js?v={{$webver}}"></script>
    <script type="text/javascript" src="{{$cdn}}/assets/pinyin/pinyinUtil.js?v={{$webver}}"></script>

    <script type="text/javascript" src="{{$common_cdn}}/js/black_clientv3.js?v={{$webver}}"></script>
    @if($app::config('services.black.dms_host', ''))
        <script type="text/javascript">
            if (typeof ROP != 'undefined') {
                ROP.ICS_ADDR = "{{ $app::config('services.black.dms_host', '') }}";
                ROP.CDN_ADDR = "{{ $app::config('services.black.dms_cdn_host', '') }}";
            }
        </script>
    @endif
    <script type="text/javascript" src="{{$cdn}}/assets/dmshub.js?v={{$webver}}"></script>
    <script type="text/javascript">
        !(function(){
            var apiList = [
                'AdminMgr', 'ApiHub', 'AuthMgr', 'DataAnalysis', 'GraphQLApi', 'HotFix', 'RoomAssist', 'RoomMgr', 'StreamMgr', 'RoomRecord', 'SiteMgr', 'XdyIsvMgr', 'ContentMgr', 'PlayerMgr'
            ];
            var cdn = window.D && window.D.cdn || '';
            var ver = window.D && window.D.ver || '0.0';
            var resMap = {};
            for(var i=0;i<apiList.length;i++){
                var key = "static/api/" + apiList[i] + ".js";
                var item = {"url": cdn + "/static/api/" + apiList[i] + ".js?v=" + ver};
                resMap[key] = item;
            }
            require.resourceMap({
                "res": resMap
            });
        })();

        window.dms = new DmsHub({
            debug: WEB_ENVIRON && WEB_ENVIRON == 'debug',
            admin_id: window.user && window.user.admin_id || 0,
            cdn: window.D && window.D.cdn || '',
            ver: window.D && window.D.ver || '0.0',
            useDmsData: {{ \app\App::config('services.xdy_cash_from') == 'dms' ? 1 : 0 }},
            apitoken: window.D && window.D.token || ''
        });
        window.dms.apiRegister('mqtt', function(args, success, failure, logHandler, logLevelHandler, fixArgs) {
            return window.dms.api_ajax(window.dms.hostname, '/helper/mqtt.php', {
                clientId: args.clientId,
                pubKey: args.pubKey,
            }, success, failure, logHandler, logLevelHandler, fixArgs);
        });

        var reconnectDmsTimer = null;
        function reconnectDms() {
            if(reconnectDmsTimer) {
                return ;
            }
            ROP.Leave();
            reconnectDmsTimer = setTimeout(function() {
                reconnectDmsTimer = null;
                window.dms.apiCall('mqtt', {
                    clientId: window.dms.dmsConfig.clientId,
                    pubKey: window.dms.dmsConfig.pubKey
                }, function(res) {
                    if (res.code !== 0) {
                        return ;
                    }
                    if(res.code === 0 && res.dms_host && res.dms_host !== ROP.ICS_ADDR ){
                        ROP.ICS_ADDR = data.dms_host;
                    }
                    window.dms.ReEnter({
                        subKey: res.subKey,
                        pubKey: res.pubKey,
                        clientId: res.clientId
                    });
                });
            }, 3000);
        }

        window.D.dmsConfig.losed = window.D.dmsConfig.enter_fail = window.D.dmsConfig.reconnect = reconnectDms;
        window.dms.initDmsConfig(window.D.dmsConfig);
        window.app = {};
    </script>

    <script type="text/javascript" src="{{$cdn}}/assets/dist/vender-base.5679b6110ef7a57b.js?v={{$webver}}"></script>
    <script type="text/javascript" src="{{$cdn}}/assets/dist/vender-exten.7ad9f59c7cfc64f5.js?v={{$webver}}"></script>
    <script type="text/javascript" src="{{$cdn}}/assets/dist/main.85af6cc60953156a.js?v={{$webver}}"></script>
</body>

</html>