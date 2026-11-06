@push('styles')
    @once
        <link rel="stylesheet" href="{{ url('assets/backend/plugins/sweetalert2/css/sweetalert2.min.css') }}" />
    @endonce
@endpush

@push('scripts')
    @once
        <script src="{{ url('assets/backend/plugins/sweetalert2/js/sweetalert2.min.js') }}"></script>
        <script>
            function sweetalert2(type, title = '', text = ''){
                if (type == 1) {
                    return Swal.fire({
                        title: title != '' ? title : "Are you sure?",
                        text: text != '' ? text : "You want to proceed",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes",
                    });
                }
            }
        </script>
    @endonce
@endpush