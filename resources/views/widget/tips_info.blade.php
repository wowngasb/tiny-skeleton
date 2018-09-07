@if (!empty($msg))
<div class="row" style="padding: 3px;">
    <img src="{{$cdn}}/assets/backend/img/tips_info.png?v={{$webver}}" style="width:24px;height:18px;margin-left:20px;"/>
    <span style="border:none;color:#EA7A01;font-size:14px;">
            {{ $msg }}
        </span>
</div>
@endif

