function formVail(obj, str, w){
    var el = $('#'+obj);
    var controls = el.parents('.controls'), controlGroup = el.parents('.form-group'), errorEl = controls.siblings('.help-label');

    if (!controlGroup.hasClass('has-error')) {
        if (errorEl.length > 0) {
            var help = errorEl.text();
            controls.data('help-message', help);
            errorEl.text(str);
        } else {
            controls.after('<label class="col-sm-'+w+' control-label help-label"><span class="help-inline">'+str+'</span></label>');
        }
        controlGroup.addClass('has-error');
    }

    el.focus(function() { // 获取焦点时
        var controls = el.parents('.controls'), controlGroup = el.parents('.form-group'), errorEl = controls.siblings('.help-label');
        if (errorEl.length > 0) {
            var help = controls.data('help-message');
            if (help == undefined) {
                errorEl.remove();
            } else {
                errorEl.text(help);
            }
        }
        controlGroup.attr('class','form-group g-form-group');
    });
    
}

function formCheck(){

    this.checkNumber = function(str){
        return /^[0-9]\d*$/.test(str);
    }

    this.checkEmail = function(str){
        return /^([a-zA-Z0-9]+[_|\_|\.\-]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.\-]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,4}$/.test(str);
    }

    this.checkChar = function(str){
        return /^[a-z\_\-A-Z0-9]*$/.test(str);
    }

    this.checkChinese = function(str){
        return /^[\u4e00-\u9fff]$/.test(str);
    }

    this.checkPhone = function(str){
        return /^(13|14|15|17|18)\d{9}/.test(str);
    }

    this.checkQq = function(str){
        return /^[1-9][0-9]{4,}/.test(str);
    }

    this.checkIdCard = function(str){
        return /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(str);
    }

    this.checkNotSpace = function(str){
        return /^\s+$/.test(str);
    }

    this.checkInt = function(str){
        return /^-?[1-9]\d*$/.test(str);
    }

    this.checkGreaterThanZeroInt = function(str){
        return /^[1-9]\d*$/.test(str);
    }

    this.checkGreaterEqThanZeroInt = function(str){
        return /^[1-9]\d*|0$/.test(str);
    }

    this.checkLessThanZeroInt = function(str){
        return /^-[1-9]\d*$/.test(str);
    }

    this.checkLessEqThanZeroInt = function(str){
        return /^-[1-9]\d*|0$/.test(str);
    }

    this.checkFloat = function(str){
        return /^-?([1-9]\d*\.\d*|0\.\d*[1-9]\d*|0?\.0+|0)$/.test(str) || this.checkInt(str);
    }

    this.checkGreaterThanZeroFloat = function(str){
        return /^\d+(\.\d+)?$/.test(str);
    }

    this.checkGreaterEqThanZeroFloat = function(str){
        return /^[1-9]\d*\.\d*|0\.\d*[1-9]\d*|0?\.0+|0$/.test(str);
    }

    this.checkLessThanZeroFloat = function(str){
        return /^-([1-9]\d*\.\d*|0\.\d*[1-9]\d*)$/.test(str);
    }

    this.checkLessEqThanZeroFloat = function(str){
        return /^(-([1-9]\d*\.\d*|0\.\d*[1-9]\d*))|0?\.0+|0$/.test(str);
    }

    this.checkLessEqThanZeroFloat = function(str){
        return /^(-([1-9]\d*\.\d*|0\.\d*[1-9]\d*))|0?\.0+|0$/.test(str);
    }

    this.checkNoSpecial = function(str){
        return !/[\~\!\@\#\$\%\^\&\*\(\)\_\+\|\`\\\{\}\:\"\<\>\?\[\]\;\'\,\.\/]/.test(str);
    }

    this.checkCharNum = function(str){
        return /^[a-zA-Z0-9]*$/.test(str);
    }

    this.checkDomain = function(str){
        if(str == ''){
            return false;
        }
        var arr = str.split('.');
        if(arr.length <= 1){
            return false;
        }
        var mode = /^[a-zA-Z0-9-]+$/;
        for(var i in arr){
            if(arr[i] == ''){
                return false;
            }
            if(!mode.test(arr[i])){
                return false;
            }
        }
        return true;
    }

}