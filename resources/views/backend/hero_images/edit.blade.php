@extends('backend.layout.master')

@section('title', 'Edit Hero"Image')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/iCheck/square/blue.css') }}">
<link rel="stylesheet" href="{{ asset('backend/components/select2/dist/css/select2.min.css') }}">
@endpush

@section('content')
<script>
    croppieRatio = 16/7;
</script>
<section class="content-header">
    <h1>
        CẬP NHẬT
        <small><a href="{{ route('admin.hero-images.index') }}" class="btn btn-block btn-xs btn-warning btn-flat">Quay
                lại</a></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#" style="margin-right: 30px; font-size: 15px;"><i class="fa fa-dashboard"></i> Trang Chủ</a></li>
    </ol>
</section>

<section class="content">
    <div class="row">

        <form action="{{ route('admin.hero-images.update',$hero->id) }}" method="POST" enctype="multipart/form-data"
            role="form">
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="title">Nội dung hình ảnh</label>
                            <input value="{{ $hero->title }}" type="text" name="title" class="form-control"
                                id="hrImageTitle">
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group">
                            <label>Hình ảnh bài viết</label>
                            <input type="file" hidden-id="{{$uniqid = uniqid()}}" id="heroImage"
                                onchange="readURL(this);">
                            <input id="{{$uniqid}}" type="hidden" name="image">
                            <p class="help-block">(Hình ảnh được đăng dưới 2 loại .png hoặc .jpg)</p>
                        </div>
                        <div class="form-group">
                            <label>Visible / Invisible</label>
                            <input type="checkbox" name="status" class="form-check-input" id="visibleCheckbox"
                                <?php echo ($hero->status == 1 ? 'checked' : '');?>>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12" stlye="margin-top:10px;">
                <img id="postimage" height="720px" width="100%" src="<?php echo asset("images/$hero->image")?>"
                    alt="Ảnh của bạn" />
            </div>
            <div class="pull-right" style="margin-top: 10px; margin-right: 20px;">
                <button type="submit" class="btn btn-primary btn-flat">Cập nhật</button>
            </div>
        </form>
    </div>
</section>

@endsection

@push('scripts')
<!-- iCheck -->
<script src="{{ asset('backend/plugins/iCheck/icheck.min.js') }}"></script>
<script src="{{ asset('backend/components/select2/dist/js/select2.full.min.js') }}"></script>
<script>
    $(function() {

    $('.select2').select2();

    $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue'
    });

    CKEDITOR.replace('editor1');

});
</script>
<script>
    function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            $('#postimage').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush