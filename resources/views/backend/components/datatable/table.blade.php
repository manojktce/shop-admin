{{ $dataTable->table(['class' => 'table table-striped table-bordered text-nowrap align-middle']) }}

@push('styles')
    @once
        <link rel="stylesheet" href="{{ url('assets/backend/plugins/datatables/css/dataTables.bootstrap5.min.css') }}" />
    @endonce
@endpush

@push('scripts')
    @once
        <script src="{{ url('assets/backend/plugins/datatables/js/jquery.dataTables.min.js') }}"></script>
        
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

        <x-backend::sweetalert.sweetalert />
        
        <script>
            $(document).on('click', '.destroy', function() {
                var url = $(this).data('url');
                
                sweetalert2(1).then((result) => {
                    if (result.isConfirmed) {
                        var form = $('{{ html()->form("delete", "")->open() }}{{ html()->form()->close() }}').appendTo('body');
                        form.attr('action', url).submit();
                    }
                })
            })
        </script>
    @endonce
@endpush