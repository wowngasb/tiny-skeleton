<?php
/** @var array $routerArr */
/** @var int $page */
/** @var array $sort_option */
/** @var array $allow_sort_field */
/** @var string $search_form */

?>
@push('styles')
<style type="text/css">
    table.table thead .sorting,
    table.table thead .sorting_asc,
    table.table thead .sorting_desc,
    table.table thead .sorting_asc_disabled,
    table.table thead .sorting_desc_disabled {
        cursor: pointer;
        *cursor: hand;
    }
    table.table thead .sorting { background: url('/assets/widget/sort_both.png') no-repeat center right; }
    table.table thead .sorting_asc { background: url('/assets/widget/sort_asc.png') no-repeat center right; }
    table.table thead .sorting_desc { background: url('/assets/widget/sort_desc.png') no-repeat center right; }
    table.table thead .sorting_asc_disabled { background: url('/assets/widget/sort_asc_disabled.png') no-repeat center right; }
    table.table thead .sorting_desc_disabled { background: url('/assets/widget/sort_desc_disabled.png') no-repeat center right; }
    table.dataTable th:active {
        outline: none;
    }
</style>
@endpush

@push('scripts')
    <script type="text/javascript">
        $(function () {
            var PAGE = parseInt('{{$page}}') || 1;
            var FIELD = '{{$sort_option['field']}}' || 'id';
            var DIRECTION = '{{$sort_option['direction']}}' || 'asc';
            var ALLOW_SORT_LIST = JSON.parse('{!! json_encode($allow_sort_field) !!}') || [];
            var SEARCH_FORM = '#{{ !empty($search_form) ? $search_form : 'search_form' }}';

            //初始化判断排序字段
            function initSort() {
                if (FIELD == "") return;
                var that = $('.' + FIELD);
                that.removeClass('sorting');
                that.addClass(FIELD);
                that.addClass('sorting_' + DIRECTION);
            }

            function addInput(FIELD, DIRECTION) {
                var Input = "<input type='hidden' name='page' value='" + PAGE + "'/>" +
                    "<input type='hidden' name='direction' value='" + DIRECTION + "'/>" +
                    "<input type='hidden' name='field' value='" + FIELD + "'/>";
                $(SEARCH_FORM).append(Input).submit();
            }

            function judgeSort(that) {
                if ($(that).hasClass('sorting')) {
                    $(this).removeClass('sorting');
                    $(this).removeClass('sorting_asc');
                    return 'asc';
                }
                if ($(that).hasClass('sorting_asc')) {
                    $(this).removeClass('sorting_asc');
                    $(this).removeClass('sorting_desc');
                    return 'desc';
                }
                if ($(that).hasClass('sorting_desc')) {
                    $(this).removeClass('sorting_desc');
                    $(this).removeClass('sorting_asc');
                    return 'asc';
                }
            }

            $('.sorting,.sorting_asc,.sorting_desc').click(function () {
                var DIRECTION = judgeSort(this);
                for (var idx in ALLOW_SORT_LIST) {
                    if ($(this).hasClass(ALLOW_SORT_LIST[idx])) {
                        addInput(ALLOW_SORT_LIST[idx], DIRECTION);
                    }
                }
            });

            //排序
            initSort();
        });
    </script>
@endpush