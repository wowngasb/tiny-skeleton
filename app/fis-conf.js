//模块化方案，本项目选中CommonJS方案(同样支持异步加载哈)
var http = require('http');
var nodekl = require('nodekl');
var path = require("path");
var exec = require('child_process').exec;

var basedir = __dirname;
var PHP_CONFIG = {};

!(function () {
    config_dir = path.join(path.resolve(__dirname, '..'), 'config')
    dump = path.join(config_dir, 'dumpjson.php')
    config = path.join(config_dir, 'app-config.ignore.php')
    var last = exec(`php ${dump} ${config}`);

    last.stdout.on('data', function (output) {
        PHP_CONFIG = output ? JSON.parse(output) : {};
    });
})();


fis.hook('module', {
    mode: 'commonjs'
});

fis.hook('commonjs', {
    extList: ['.js', '.jsx', '.es', '.ts', '.tsx']
});

fis.unhook('components'); // fis3 自带的不是 npm 所以，先禁用它
fis.hook('node_modules'); // 启用 node_modules 组件支持。

fis.match('/{node_modules}/**.js', {
    isMod: true,
    useSameNameRequire: true
});

fis.match('**', {
    release: false
});

fis.match('/index.php', {
    release: '/index.php'
});
fis.match("/static/**", {
    release: '/$0'
});
fis.match('/static/favicon.ico', {
    release: '/favicon.ico'
});
fis.match('/static/htaccess', {
    release: '/.htaccess'
});
fis.match('/static/lighttpd', {
    release: '/.lighttpd'
});
fis.match('/static/nginx', {
    release: '/.nginx'
});
fis.match('/static/robots.txt', {
    release: '/robots.txt'
});
fis.match('/static/map.json', {  //资源配置表
    release: '/tpl/map.json'
});
fis.match("/static/api/**", {
    isMod: true
});

fis.match("/{widget,view}/**", {
    isMod: true,
    release: '/assets/$0'
});

fis.match("/{view,widget}/**.php", {
    isMod: true,
    isHtmlLike: true,
    url: '$&', //此处注意，php加载的时候已带tpl目录，所以路径得修正
    release: '/tpl/$&'
});


//开启组件同名依赖
fis.match('*.{html,js,php}', {
    useSameNameRequire: true
});

fis.match('*', {
    deploy: [
        function(options, modified, total, next) {
            var try_build_api = true;
            for(var i=0; i< modified.length; i++){
                if(modified[i].fullname.match(/\/api\/\w+\.js/)){
                    try_build_api = false;
                    break;
                }
            }
            var dev_token = nodekl.encode(PHP_CONFIG['ENV_DEVELOP_KEY'], PHP_CONFIG['ENV_CRYPT_KEY']);
            try_build_api && dev_token && !(function(){
                console.log('\nbuild api js');
                setTimeout(function (){
                    http.get("http://tiny.app/develop/deploy/buildapimodjs?dev_debug=1&dev_token=" + dev_token, function(res) {
                        console.log("Build API js response: " + res.statusCode);
                    }).on('error', function(e) {
                        console.log("Build API js error: " + e.message);
                    });
                }, 1000);
            })();
            next && next(); //由于是异步的如果后续还需要执行必须调用 next
        },
        fis.plugin('local-deliver', {
            to: './../app-public'
        })
    ]
});

/*
 fis.media('product').match('*', {
 deploy: fis.plugin('http-push', {
 receiver: 'http://product.org/fis-receiver.php?media=product&token=product_key)',
 to: '/usr/local/tengine/html' // 注意这个是指的是测试机器的路径，而非本地机器
 })
 });

 fis.media('product').match('*.{js, css, png, jpg, gif, svg}', {
 domain: 'http://static.product.com',
 });
 */

fis.media('product').match('*.js', {
    // fis-optimizer-uglify-js 插件进行压缩，已内置
    optimizer: fis.plugin('uglify-js')
});

fis.media('product').match('*.css', {
    // fis-optimizer-clean-css 插件进行压缩，已内置
    optimizer: fis.plugin('clean-css')
});

fis.media('product').match('*.png', {
    // fis-optimizer-png-compressor 插件进行压缩，已内置
    optimizer: fis.plugin('png-compressor')
});

fis.media('product').match('*.{js, css, png, jpg, gif, svg}', {
    useHash: true
});
