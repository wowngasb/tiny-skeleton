function dlg(str, delay, title, size) {
    $('#dlg').remove();
    str = typeof(str) !== 'undefined' && str ? str : '操作完成';
    title = typeof(title) !== 'undefined' && title ? title : '提示';
    size = typeof(size) !== 'undefined' && size ? size : 'modal-lg';// '' modal-lg modal-sm
    delay = delay && delay > 0 ? delay : 1500;
    var html = '\
<div class="modal fade" id="dlg" tabindex="-1" role="dialog" aria-labelledby="dlgLabel">\
  <div class="modal-dialog ' + size + '" role="document">\
    <div class="modal-content">\
      <div class="modal-header">\
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
        <h4 class="modal-title" id="myModalLabel">' + title + '</h4>\
      </div>\
      <div class="modal-body">\
        <h4>' + str + '</h4>\
      </div>\
      <div class="modal-footer">\
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>\
      </div>\
    </div>\
  </div>\
</div>\
	';
    $('body').append(html);
    $('#dlg').modal('show');
    setTimeout(function () {
        $('#dlg').modal('hide');
    }, delay);
}

function dlgLoading(fun, str, title, size, delay) {
    $('#dlgLoading').remove();
    str = typeof(str) !== 'undefined' && str ? str : '操作中，请稍后...';
    title = typeof(title) !== 'undefined' && title ? title : '提示';
    size = typeof(size) !== 'undefined' && size ? size : 'modal-lg';// '' modal-lg modal-sm
    delay = delay && delay > 0 ? delay : 500;

    var html = '\
<div class="modal fade" id="dlgLoading" tabindex="-1" role="dialog" aria-labelledby="dlgLoadingLabel">\
  <div class="modal-dialog ' + size + '" role="document">\
    <div class="modal-content">\
      <div class="modal-header">\
        <h4 class="modal-title" id="myModalLabel">' + title + '</h4>\
      </div>\
      <div class="modal-body text-center">\
        <img src="/assets/backend/loading.gif" width="60"><br>\
        <h4>' + str + '</h4>\
      </div>\
      <div class="modal-footer">\
      </div>\
    </div>\
  </div>\
</div>\
  ';
    $('body').append(html);
    $('#dlgLoading').modal({show: true, backdrop: 'static'});
    setTimeout(function () {
        fun();
    }, delay);
}

function dlgLoadingHide() {
    $('#dlgLoading').modal('hide');
}