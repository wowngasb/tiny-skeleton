/*!
 * ApiHub.js
 * build at 2017-09-02 02:55:02 123 234
 */
(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
	typeof define === 'function' && define.amd ? define(factory) :
	(global.Vue = factory());
}(this, (function () { 'use strict';

/*  */

function ApiHubHelper(){
    var _this = this;
    this.DEBUG = true;
    var _log_func = (typeof console != "undefined" && typeof console.info == "function" && typeof console.warn == "function") ? {INFO: console.info.bind(console), ERROR: console.warn.bind(console)} : {};
    
    var _formatDate = function(){
        var now = new Date(new Date().getTime());
        var year = now.getFullYear();
        var month = now.getMonth()+1;
        var date = now.getDate();
        var hour = now.getHours();
        var minute = now.getMinutes();
        if(minute < 10){
            minute = '0' + minute.toString();
        } 
        var seconds = now.getSeconds();
        if(seconds < 10){
            seconds = '0' + seconds.toString();
        }
        return year+"-"+month+"-"+date+" "+hour+":"+minute+":"+seconds;
    };
    
    var _rfcApi = function(type, url, args, success, error, log){
        var start_time = new Date().getTime();
        if( typeof CSRF_TOKEN != "undefined" && CSRF_TOKEN ){
            args.csrf = CSRF_TOKEN;
        }
        $.ajax({
            type: type,
            url: url,
            data: args,
            dataType: 'json',
            success:
                function(data) {
                    var use_time = Math.round( (new Date().getTime() - start_time) );
                    if(data.errno == 0 || typeof data.error == "undefined" ){
                        log('INFO', use_time, args, data);
                        typeof(success) == 'function' && success(data);
                    } else {
                        log('ERROR', use_time, args, data);
                        typeof(error) == 'function' && error(data);
                    }
                }
        });
    };

    /**
     * api hello
     * @param string $name
     * @return array
     */
    this.hello = function(args, success, error) {
        args = args || {};
        var log = function(tag, use_time, args, data){
            var f = _log_func[tag]; typeof args.csrf != "undefined" && delete args.csrf;
            _this.DEBUG && f && f(_formatDate(), '['+tag+'] ApiHub.hello('+use_time+'ms)', 'args:', args, 'data:', data);
        };
        return _rfcApi('POST', '/api/ApiHub/hello' ,args, success, error, log);
    };
    this.hello_args = {"name":"world"};

    /**
     * test sum
     * @param int $a
     * @param int $b
     * @return array
     */
    this.testSum = function(args, success, error) {
        args = args || {};
        var log = function(tag, use_time, args, data){
            var f = _log_func[tag]; typeof args.csrf != "undefined" && delete args.csrf;
            _this.DEBUG && f && f(_formatDate(), '['+tag+'] ApiHub.testSum('+use_time+'ms)', 'args:', args, 'data:', data);
        };
        return _rfcApi('POST', '/api/ApiHub/testSum' ,args, success, error, log);
    };
    this.testSum_args = {"a":"?","b":"?"};
}

/*  */

return new ApiHubHelper();
})));