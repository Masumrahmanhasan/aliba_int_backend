@extends('backend.layouts.app')

@section('title', 'Footer Brand Image Settings')

@php
    $required = html()
        ->span('*')
        ->class('text-danger');
    $demoImg = 'img/backend/front-logo.png';
@endphp

@section('content')

    {{ html()->form('POST', route('admin.front-setting.footer-brand-settings-store'))->class('form-horizontal')->attribute('enctype', 'multipart/form-data')->open() }}

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header with-border">
                    <h3 class="card-title">Footer Brand Image Settings<small class="ml-2">(update information anytime)</small></h3>
                </div>
                <div class="card-body">

                    <p class="mb-4 text-center text-danger">Click the Image for updating Process</p>

                    <div class="form-group row mb-4">
                        {{ html()->label('Image One')->class('col-md-4 form-control-label text-right')->for('footer_image_one') }}
                        <div class="col-md-8">
                            @php($aLogo = get_setting('footer_image_one') ?? $demoImg)
                            <label for="footer_image_one">
                                <img src="{{ asset($aLogo) }}" class="border img-fluid rounded holder"
                                    style="max-width: 200px" alt="Image upload">
                            </label>
                            {{ html()->file('footer_image_one')->class('form-control-file image d-none')->id('footer_image_one')->acceptImage() }}
                        </div> <!-- col-->
                    </div> <!-- form-group-->

                    <div class="form-group row mb-4">
                        {{ html()->label('Image Two')->class('col-md-4 form-control-label text-right')->for('footer_image_two') }}
                        <div class="col-md-8">
                            @php($aLogo = get_setting('footer_image_two') ?? $demoImg)
                            <label for="footer_image_two">
                                <img src="{{ asset($aLogo) }}" class="border img-fluid rounded holder"
                                    style="max-width: 200px" alt="Image upload">
                            </label>
                            {{ html()->file('footer_image_two')->class('form-control-file image d-none')->id('footer_image_two')->acceptImage() }}
                        </div> <!-- col-->
                    </div> <!-- form-group-->

                    <div class="form-group row mb-4">
                        {{ html()->label('Image Three')->class('col-md-4 form-control-label text-right')->for('footer_image_three') }}
                        <div class="col-md-8">
                            @php($aLogo = get_setting('footer_image_three') ?? $demoImg)
                            <label for="footer_image_three">
                                <img src="{{ asset($aLogo) }}" class="border img-fluid rounded holder"
                                    style="max-width: 200px" alt="Image upload">
                            </label>
                            {{ html()->file('footer_image_three')->class('form-control-file image d-none')->id('footer_image_three')->acceptImage() }}
                        </div> <!-- col-->
                    </div> <!-- form-group-->

                    <div class="form-group row mb-4">
                        {{ html()->label('Image Four')->class('col-md-4 form-control-label text-right')->for('footer_image_four') }}
                        <div class="col-md-8">
                            @php($aLogo = get_setting('footer_image_four') ?? $demoImg)
                            <label for="footer_image_four">
                                <img src="{{ asset($aLogo) }}" class="border img-fluid rounded holder"
                                    style="max-width: 200px" alt="Image upload">
                            </label>
                            {{ html()->file('footer_image_four')->class('form-control-file image d-none')->id('footer_image_four')->acceptImage() }}
                        </div> <!-- col-->
                    </div> <!-- form-group-->

                    <div class="form-group row mb-4">
                        {{ html()->label('Image Five')->class('col-md-4 form-control-label text-right')->for('footer_image_five') }}
                        <div class="col-md-8">
                            @php($aLogo = get_setting('footer_image_five') ?? $demoImg)
                            <label for="footer_image_five">
                                <img src="{{ asset($aLogo) }}" class="border img-fluid rounded holder"
                                    style="max-width: 200px" alt="Image upload">
                            </label>
                            {{ html()->file('footer_image_five')->class('form-control-file image d-none')->id('footer_image_five')->acceptImage() }}
                        </div> <!-- col-->
                    </div> <!-- form-group-->



                    <div class="form-group row mb-4">
                        <div class="col-md-8 offset-md-4">
                            {{ html()->button('Update')->class('btn btn-sm btn-success') }}
                        </div> <!-- col-->
                    </div> <!-- form-group-->
                </div> <!--  .card-body -->
            </div> <!--  .card -->
        </div> <!-- .col-md-9 -->
    </div> <!-- .row -->

    {{ html()->form()->close() }}
@endsection




@push('after-scripts')
    {{ script('assets/js/jscolor.js') }}

    <script>
        function readImageURL(input, preview) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]); // convert to base64 string
            }
        }

        $(document).ready(function() {
            $(".image").change(function() {
                holder = $(this).closest('.form-group').find('.holder');
                readImageURL(this, holder);
            });
        });
    </script>
@endpush
