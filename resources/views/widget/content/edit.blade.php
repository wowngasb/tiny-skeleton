@if($list['type'] == 'text')
    <div class="form-group">
        <input type="hidden" name="{{ "_{$content_slug}[{$key}]" }}" value="content_text">
        <span class="col-sm-4 col-sm-4 control-label">{{ $list['title'] }}
            @if(!empty($list['img']))
                (<a data-toggle="tooltip"
                   data-placement="top" data-html="true" data-original-title='{{ '<img src="'.trim($list['img']).'" />'  }}'>?</a>)
            @endif
            :*</span>
        <div class="col-sm-4">
            <input class="form-control" type="text" name="{{ "{$content_slug}[{$key}]" }}"
                   value="{{ $list['content_text'] }}">
            <p><span style="color: #ff9900;">备注：{{ !empty($list['doc']) ? $list['doc'] : $list['title'] }}
                </span>
            </p>
        </div>
    </div>
@elseif($list['type'] == 'image')
    <div class="form-group">
        <input type="hidden" name="{{ "_{$content_slug}[{$key}]" }}" value="content_text">
        <span class="col-sm-4 col-sm-4 control-label">{{ $list['title'] }}
            @if(!empty($list['img']))
                (<a data-toggle="tooltip"
                    data-placement="top" data-html="true" data-original-title='{{ '<img src="'.trim($list['img']).'" />'  }}'>?</a>)
            @endif
            :*</span>

            <div class="col-sm-4">
                <div class="img-upload">
                    <div id="{{ "{$content_slug}-{$key}-image" }}"></div>
                    <p id="{{ "{$content_slug}-{$key}-image-tip" }}" class="webuploader-tip">
                    <p>
                        <input class="form-control" type="hidden" name="{{ "{$content_slug}[{$key}]" }}"
                               id="{{ "{$content_slug}-{$key}-image-input" }}" value="{{$list['content_text']}}"/>
                        <img id="{{ "{$content_slug}-{$key}-image-show" }}" src="{{$list['content_text']}}" alt="{{ !empty($list['ext']['alt']) ? strval($list['ext']['alt']) : ""  }}"
                             height="{{ !empty($list['ext']['height']) ? intval($list['ext']['height']) : 200  }}"/></p>
                    </p>
                </div>
                <p><span style="color: #ff9900;">备注：{{ !empty($list['doc']) ? $list['doc'] : $list['title'] }}
                </span>
                </p>
            </div>
            <button class="btn btn-danger js-clear" id="{{ "{$content_slug}-{$key}-image-clear" }}" type="button">清除</button>

    </div>
    @push('scripts')
    <script type="text/javascript">
        $(function () {
            doUpload({
                id: '{{ "{$content_slug}-{$key}-image" }}',
                intputId: '{{ "{$content_slug}-{$key}-image-input" }}',
                showId: '{{ "{$content_slug}-{$key}-image-show" }}',
                descId: '{{ "{$content_slug}-{$key}-image-tip" }}',
                csrf_token: '{{ $ctrl->csrf_token() }}',
                fileSize: parseInt('{{ !empty($list['ext']['size']) ? intval($list['ext']['size']) : 1024 * 1024  }}')
            });

            $('#{{ "{$content_slug}-{$key}-image-clear" }}').click(function () {
                $(this).parent().find("input").val("");
                $(this).parent().find("img").attr("src", "");
            });
        });
    </script>
    @endpush
@elseif($list['type'] == 'textarea')
    <div class="form-group">
        <input type="hidden" name="{{ "_{$content_slug}[{$key}]" }}" value="content_text">
        <span class="col-sm-4 col-sm-4 control-label">{{ $list['title'] }}
            @if(!empty($list['img']))
                (<a data-toggle="tooltip"
                    data-placement="top" data-html="true" data-original-title='{{ '<img src="'.trim($list['img']).'" />'  }}'>?</a>)
            @endif
            :*</span>
        <div class="col-sm-4">
            <textarea  name="{{ "{$content_slug}[{$key}]" }}" class="form-control" rows="10" cols="50">{!! $list['content_text'] !!}</textarea>
            <p><span style="color: #ff9900;">备注：{{ !empty($list['doc']) ? $list['doc'] : $list['title'] }}
                </span>
            </p>
        </div>
    </div>
@elseif($list['type'] == 'page_seo')
    <div class="form-group">
        <input type="hidden" name="{{ "_{$content_slug}[{$key}]" }}" value="content_config">
        <span class="col-sm-4 col-sm-4 control-label">{{ $list['title'] }}-标题
            @if(!empty($list['img']))
                (<a data-toggle="tooltip"
                    data-placement="top" data-html="true" data-original-title='{{ '<img src="'.trim($list['img']).'" />'  }}'>?</a>)
            @endif
            :*</span>
        <div class="col-sm-6">
            <input class="form-control" type="text" name="{{ "{$content_slug}[{$key}][title]" }}"
                   value="{{ $list['content_config']['title'] }}"></div>
        <div class="col-sm-2">
            <a target="_blank" href="{{ $key=='index' ? '/' : "/front/{$key}" }}">打开</a>
        </div>
    </div>
    <div class="form-group">
        <span class="col-sm-4 col-sm-4 control-label">关键词:*</span>
        <div class="col-sm-6">
            <textarea rows=3 class="form-control"
                      name="{{ "{$content_slug}[{$key}][keywords]" }}">{{ $list['content_config']['keywords'] }}</textarea>
        </div>
    </div>
    <div class="form-group">
        <span class="col-sm-4 col-sm-4 control-label">描述:*</span>
        <div class="col-sm-6">
            <textarea rows=5 class="form-control"
                      name="{{ "{$content_slug}[{$key}][description]" }}">{{ $list['content_config']['description'] }}</textarea>
            <p><span style="color: #ff9900;">备注：{{ !empty($list['doc']) ? $list['doc'] : $list['title'] }}</span></p>
        </div>
    </div>

@else
    <span style="display: none">

    </span>
@endif

