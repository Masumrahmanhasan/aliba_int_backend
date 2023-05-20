@extends('backend.layouts.app')

@section('title', 'Manage Product Category Shipping Rate')

@section('content')
    {{ html()->form('POST', route('admin.product-category-shipping-rate.store'))->class('form-horizontal')->open() }}

    <div class="row d-flex justify-content-center mt-5">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header with-border">
                    <h3 class="card-title">Product Category Shipping Rate Management <small class="ml-2">Create Product Category Shipping Rate</small></h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <input type="text" placeholder="Category Name" class="form-control cash" name="category">
                        <p class="text-danger margin-bottom-none" id="category_error">@error('category') {{$message}}
                            @enderror</p>
                    </div> <!-- form-group -->

                    <div class="form-group">
                        <input type="number" placeholder="Shipping Rate" class="form-control cash" name="shipping_rate">
                        <p class="text-danger margin-bottom-none" id="shipping_rate_error">@error('shipping_rate') {{$message}}
                            @enderror</p>
                    </div> <!-- form-group -->
                </div> <!--  .card-body -->
                <div class="card-footer">
                    {{ form_submit(__('buttons.general.crud.create')) }}
                    {{ form_cancel(route('admin.product-category-shipping-rate.index'), __('buttons.general.cancel')) }}
                </div> <!--  .card-body -->
            </div> <!--  .card -->
        </div> <!-- .col-md-9 -->
    </div> <!-- .row -->

    {{ html()->form()->close() }}
@endsection
