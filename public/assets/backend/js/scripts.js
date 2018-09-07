
(function() {
    "use strict";

    // custom scrollbar

    //$("html").niceScroll({styler:"fb",cursorcolor:"#65cea7", cursorwidth: '6', cursorborderradius: '0px', background: '#424f63', spacebarenabled:false, cursorborder: '0',  zindex: '1000'});

    $(".left-side").niceScroll({styler:"fb",cursorcolor:"#65cea7", cursorwidth: '3', cursorborderradius: '0px', background: '#424f63', spacebarenabled:false, cursorborder: '0'});


    $(".left-side").getNiceScroll();
    if ($('body').hasClass('left-side-collapsed')) {
        $(".left-side").getNiceScroll().hide();
    }



    // Toggle Left Menu
   jQuery('.menu-list > a').click(function() {
      
      var parent = jQuery(this).parent();
      var sub = parent.find('> ul');
      
      if(!jQuery('body').hasClass('left-side-collapsed')) {
         if(sub.is(':visible')) {
            sub.slideUp(200, function(){
               parent.removeClass('nav-active');
               jQuery('.main-content').css({height: ''});
               mainContentHeightAdjust();
            });
         } else {
            visibleSubMenuClose();
            parent.addClass('nav-active');
            sub.slideDown(200, function(){
                mainContentHeightAdjust();
            });
         }
      }
      return false;
   });

   function visibleSubMenuClose() {
      jQuery('.menu-list').each(function() {
         var t = jQuery(this);
         if(t.hasClass('nav-active')) {
            t.find('> ul').slideUp(200, function(){
               t.removeClass('nav-active');
            });
         }
      });
   }

   function mainContentHeightAdjust() {
      // Adjust main content height
      var docHeight = jQuery(document).height();
      if(docHeight > jQuery('.main-content').height())
         jQuery('.main-content').height(docHeight);
   }

   //  class add mouse hover
   jQuery('.custom-nav > li').hover(function(){
      jQuery(this).addClass('nav-hover');
   }, function(){
      jQuery(this).removeClass('nav-hover');
   });


   // Menu Toggle
   jQuery('.toggle-btn').click(function(){
       $(".left-side").getNiceScroll().hide();
       
       if ($('body').hasClass('left-side-collapsed')) {
           $(".left-side").getNiceScroll().hide();
       }
      var body = jQuery('body');
      var bodyposition = body.css('position');

      if(bodyposition != 'relative') {

         if(!body.hasClass('left-side-collapsed')) {
            body.addClass('left-side-collapsed');
            jQuery('.custom-nav ul').attr('style','');

            jQuery(this).addClass('menu-collapsed');

         } else {
            body.removeClass('left-side-collapsed chat-view');
            jQuery('.custom-nav li.active ul').css({display: 'block'});

            jQuery(this).removeClass('menu-collapsed');

         }
      } else {

         if(body.hasClass('left-side-show'))
            body.removeClass('left-side-show');
         else
            body.addClass('left-side-show');

         mainContentHeightAdjust();
      }

   });
   

   searchform_reposition();

   jQuery(window).resize(function(){

      if(jQuery('body').css('position') == 'relative') {

         jQuery('body').removeClass('left-side-collapsed');

      } else {

         jQuery('body').css({left: '', marginRight: ''});
      }

      searchform_reposition();

   });

   function searchform_reposition() {
      if(jQuery('.searchform').css('position') == 'relative') {
         jQuery('.searchform').insertBefore('.left-side-inner .logged-user');
      } else {
         jQuery('.searchform').insertBefore('.menu-right');
      }
   }

    // panel collapsible
    $('.panel .tools .fa').click(function () {
        var el = $(this).parents(".panel").children(".panel-body");
        if ($(this).hasClass("fa-chevron-down")) {
            $(this).removeClass("fa-chevron-down").addClass("fa-chevron-up");
            el.slideUp(200);
        } else {
            $(this).removeClass("fa-chevron-up").addClass("fa-chevron-down");
            el.slideDown(200); }
    });

    $('.todo-check label').click(function () {
        $(this).parents('li').children('.todo-title').toggleClass('line-through');
    });

    $(document).on('click', '.todo-remove', function () {
        $(this).closest("li").remove();
        return false;
    });

    $("#sortable-todo").sortable();


    // panel close
    $('.panel .tools .fa-times').click(function () {
        $(this).parents(".panel").parent().remove();
    });



    // tool tips

    $('.tooltips').tooltip();

    // popovers

    $('.popovers').popover();








})(jQuery);



var mainTopMargin = 0;
function getQueryString(name){
  var uri = window.location.search.substr(1).split('&');
  var param = [];
  for(var i in uri){
    var r = uri[i].split('=');
    param[r[0]]? param[r[0]].push(r[1]) : param[r[0]] = [r[1]];
  }
  if(param[name]) return param[name].length>1? param[name] : param[name][0];
  return '';
}

function status(type,msg,time,callback){
  if(typeof(time) == 'undefined') time = 4000;
  if($('.act-msg').length > 0){
    $('.act-msg').remove();
  }
  if(mainTopMargin > 0){
    $('body').prepend('<div class="act-msg text-'+type+'" style="top:'+mainTopMargin+'px">'+msg+'</div>');
  }else{
    $('body').prepend('<div class="act-msg text-'+type+'">'+msg+'</div>');
  }
  $('.act-msg').fadeIn('slow',function(){
    setTimeout(function(){
      $('.act-msg').fadeOut('slow',function(){
        $('.act-msg').remove();
        if(typeof(callback) != 'undefined'){
          callback();
        }
      });
    },time);
  });
}

function reqbtn(obj){
  this.timer;
  var that = this;
  this.submitStatus = function(){
    if(typeof($(obj).html()) != 'undefined'){
      if(typeof($(obj).attr('btnval')) == 'undefined'){
        $(obj).attr('btnval',$(obj).html());
        $(obj).attr('disabled','disabled');
        $(obj).addClass('disabled');
        $(obj).html('请求中 请稍候');
        $(obj).css('width','140px');
        $(obj).css('text-align','left');
        $(obj).css('font-weight','bold');
        that.timer = setInterval(function(){
          var btnval = $(obj).html();
          if((btnval.split('.')).length-1 >= 3){
            $(obj).html('请求中 请稍候');
          }else{
            $(obj).html($(obj).html()+'.');
          }
        },250);
      }else{
        clearInterval(that.timer);
        $(obj).html($(obj).attr('btnval'));
        $(obj).removeAttr('btnval');
        $(obj).css('width','auto');
        $(obj).css('text-align','center');
        $(obj).css('font-weight','normal');
        $(obj).removeAttr('disabled');
        $(obj).removeClass('disabled');
      }
    }else{
      if(typeof($(obj).attr('btnval')) == 'undefined'){
        $(obj).attr('btnval',$(obj).val());
        $(obj).attr('disabled','disabled');
        $(obj).addClass('disabled');
        $(obj).val('请求中 请稍候');
        $(obj).css('width','140px');
        $(obj).css('text-align','left');
        $(obj).css('font-weight','bold');
        that.timer = setInterval(function(){
          var btnval = $(obj).val();
          if((btnval.split('.')).length-1 >= 3){
            $(obj).val('请求中 请稍候');
          }else{
            $(obj).val($(obj).html()+'.');
          }
        },250);
      }else{
        clearInterval(that.timer);
        $(obj).val($(obj).attr('btnval'));
        $(obj).removeAttr('btnval');
        $(obj).css('width','auto');
        $(obj).css('text-align','center');
        $(obj).css('font-weight','normal');
        $(obj).removeAttr('disabled');
        $(obj).removeClass('disabled');
      }
    }
  }
}
function confirmRequest(obj,desc,back,cb){
  if(confirm(desc)){
    return request(obj,back,cb);
  }
  return false;
}

function request(obj,back,cb){
  if(typeof(back) == 'undefined') back = window.location.href;//false;
  var url = $(obj).attr('action')? $(obj).attr('action') : window.location.href;
  var post = $(obj).serialize()? $(obj).serialize() : null;
  $.post(url,post,function (data){
    var btn = new reqbtn($(obj).find(':submit'));
    btn.submitStatus();
    status('info','请求中，请稍候...',36000);
  
    if(back && data.code==0){
      status(data.code,data.msg,5,function(){
        if(back){
          window.location.href = back;
          return ;
        }
        btn.submitStatus();
      });
    }else{
      status(data.code,data.msg,2000,function(){
        btn.submitStatus();
      });
      if(typeof(cb) == 'undefined' &&　data.code!=0){
        alert(data.msg)
      }
    }
    if(typeof(cb) != 'undefined'){
      cb(data);
    }
  });
  return false;
}

//添加debug调试信息
function debugshow(data){
    var json = eval('('+data+')');
    if($("#debug_mode").is(":checked")){
    $("#debug_info").text("debug: "+json.debug); 
    } 
}

function doUpload(option){
  if(!option.fileSize){
    option.fileSize = 2*1024*1024;
  }
  if(!option.fileName){
    option.fileName = "";
  }
  if(!option.useOriginFileName){
    option.useOriginFileName = 0;
  }
  var idcardUploader = WebUploader.create({
        auto: true,
        server: '/ajaxUpload?dir=admin&fileName='+option.fileName+"&useOriginFileName="+option.useOriginFileName + "&typeName=Filedata",
        headers: {
              'X-CSRF-TOKEN': option.csrf_token,
            },
        pick: {id:'#'+option.id,label:option.label?option.label:'上传图片'},
        fileVal: 'Filedata',
        accept: {
            title: 'Images',
            extensions: option.resType?option.resType:"jpg,jpeg,png,ico",
            mimeTypes: option.mimeTypes?option.mimeTypes:'image/jpg,image/jpeg,image/png,image/ico'
        },
        compress:false,
        fileSingleSizeLimit:option.fileSize?option.fileSize:2 * 1024 * 1024    // 2 M
    });

    idcardUploader.on( 'uploadSuccess', function( file,response ) {
        
        if(response.code == 0){
          if(option.callback){
              option.callback(response);
              return;
          }
          
          $("#"+option.intputId).val(response.img);
          $("#"+option.showId).attr('src', response.img);
          $('#'+option.descId).text('');

      }else{
          $('#'+option.descId).text(response.msg);
      }
    });
    idcardUploader.on( 'uploadError', function( file ) {
        $('#'+option.descId).text('上传失败，请重试！');
    });
    idcardUploader.on( 'error', function( type ) {
        var errstr='';
        if (type=="Q_TYPE_DENIED"){
            errstr = "请上传"+option.resType+"格式文件";
        }else if(type=="F_EXCEED_SIZE"){
            errstr = "文件不能超过"+option.fileSize/1024+"KB";
        }
        $('#'+option.descId).text(errstr);
    });
    idcardUploader.on( 'uploadProgress', function( file, percentage ) {
        $('#'+option.descId).text('正在上传，请等待...'+percentage*100+"%");
    });
}