@props([
    'name' => null,
    'class' => '',
    'file' => '',
    'path' => '',
    'ext' => ['jpg','jpeg','png'],
])

<div class="fileUpload">
    <div class="preview-box preview-box-{{ $class }}">
        <i class="ti ti-photo"></i>
        <p>Upload a file</p>
    </div>

    <div class="upload-box upload-box-{{ $class }}" style="display:none">
        <img>
        <div class="upload-actions">
            <i class="ti ti-pencil edit-{{ $class }}"></i>
            <i class="ti ti-trash delete-{{ $class }}"></i>
        </div>
    </div>

    <input type="file" class="file-{{ $class }}" style="display:none" name="{{ $name }}">
    <input type="hidden" name="{{ $name }}_hidden" class="hidden-{{ $class }}" value="{{ $file }}">
</div>
   

@push('scripts')
    @once
        <script>
            function fileUpload(selector, file = '', allowedExt){
                const box = $('.fileUpload');
                const input = $(`.file-${selector}`);
                const hidden = $(`.hidden-${selector}`);
                const preview = $(`.preview-box-${selector}`);
                const upload = $(`.upload-box-${selector}`);
                const img = upload.find('img');
                const edit = $(`.edit-${selector}`);
                const destroy = $(`.delete-${selector}`);
                
                let objectUrl = null;

                if (file) {
                    show(file);
                }

                preview.add(edit).on('click', () => input.click());
                destroy.on('click', () => reset());

                input.on('change', function () {
                    const f = this.files[0];
                    if (!f) return;

                    const ext = f.name.split('.').pop().toLowerCase();
                    if (!allowedExt.includes(ext)) {
                        alert(`Allowed file types: ${allowedExt.join(', ')}`);
                        input.val('');
                        return;
                    }

                    if (objectUrl) URL.revokeObjectURL(objectUrl);

                    objectUrl = URL.createObjectURL(f);
                    show(objectUrl);
                });

                function show(src) {
                    img.attr('src', src);
                    preview.hide();
                    upload.show();
                }

                function reset() {
                    input.val('');
                    hidden?.val('');
                    img.attr('src', '');
                    upload.hide();
                    preview.show();

                    if (objectUrl) {
                        URL.revokeObjectURL(objectUrl);
                        objectUrl = null;
                    }
                }
            }
        </script>
    @endonce

    <script>
        fileUpload(
            '{{ $class }}', 
            '{{ $file != '' && fileExists($path."/".$file) ? $path."/".$file : '' }}', 
            @json($ext)
        )
    </script>
@endpush

@push('styles')
    @once
        <style>
            .fileUpload {
                width: 120px;
                height: 120px;
                cursor: pointer;
            }

            .fileUpload .preview-box {
                width: 100%;
                height: 100%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                border: 1px dashed lightgray;
            }

            .fileUpload .preview-box i {
                font-size: 40px;
            }

            .fileUpload .preview-box p {
                margin: 5px 0 0;
                font-size: 13px;
            }

            .fileUpload .upload-box {
                position: relative;
                cursor: pointer;
            }

            .fileUpload .upload-box img {
                width: 120px;
                height: 120px;
            }

            .fileUpload .upload-box:hover .upload-actions{
                display: flex;
            }

            .fileUpload .upload-actions{
                display: none;
                position: absolute;
                right: 5px;
                top: 5px;
                flex-direction: column;
            }

            .fileUpload .upload-actions i{
                padding: 8px;
                border-radius: 5px;
                margin-bottom: 5px;
                box-shadow: 0 0 2px lightgrey;
                background: white;
            }
        </style>
    @endonce
@endpush