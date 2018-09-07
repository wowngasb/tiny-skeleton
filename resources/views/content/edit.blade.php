@extends('layouts.mgrbase')

@section('head')

@endsection

@push('styles')
    <style type="text/css">
        .tooltip-inner {
            max-width: 400px !important;
        }
    </style>
@endpush

@section('content')
    @if($ctrl->errors_has('modify'))
        <div>
            <h2 style="color: green;">{{$ctrl->errors_first('modify', ':message')}}</h2>
        </div>
    @endif


    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">{{ $render_title }}</header>
                <div class="panel-body">

                    <form class="form-horizontal" method="post" action="{{$action}}">
                        {{ $ctrl->csrf_field() }}
                        <input type="hidden" name="admin_id" value="{{ $admin_id }}">
                        <input type="hidden" name="room_id" value="{{ $room_id }}">
                        <input type="hidden" name="back" value="{{ $back }}">
                        <input type="hidden" name="content_slug" value="{{ $content_slug }}">

                        @foreach($lists as $key => $list)
                            @include('widget.content.edit', [
                                'list' => $list,
                                'key'=> $key
                            ])
                        @endforeach

                        <div class="form-group">
                            <div class="controls text-center">
                                <button type="submit" class="btn btn-primary">确定</button>
                            </div>
                        </div>
                    </form>

                </div>

            </section>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(function () {
            $("[data-toggle='tooltip']").tooltip();
        });
    </script>
@endsection