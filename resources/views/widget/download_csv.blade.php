<button class="btn btn-link" id="{{ !empty($csv_btn) ? $csv_btn : 'download_csv'  }}" type="button"><img
            src="{{$cdn}}/assets/backend/img/download_csv.png?v={{$webver}}"
            class="f-img1"> {{ !empty($csv_title) ? $csv_title : '导出Excel' }}
</button>

@push('scripts')
    <script type="text/javascript">
        $(function () {
            var CSV_FORM_ID = '{{ !empty($csv_form) ? $csv_form : 'search_form'  }}';
            var CSV_BTN_ID = '{{ !empty($csv_btn) ? $csv_btn : 'download_csv'  }}';
            var CSV_INPUT_ID = '{{ !empty($csv_input) ? $csv_input : 'is_csv'  }}';

            $('#' + CSV_BTN_ID).on('click', function () {
                $('#' + CSV_INPUT_ID).val('csv');
                $("#" + CSV_FORM_ID).submit();
                $('#' + CSV_INPUT_ID).val('');
            });

            if ($('#' + CSV_INPUT_ID).length <= 0) {
                $("#" + CSV_FORM_ID).append('<input type="hidden" id="' + CSV_INPUT_ID + '" name="csv" value=""/>');
            }
        });
    </script>
@endpush