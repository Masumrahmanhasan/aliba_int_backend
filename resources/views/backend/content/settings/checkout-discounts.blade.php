@extends('backend.layouts.app')

@section('title', ' Checkout Discount Settings ')

@php
    $required = html()->span('*')->class('text-danger');
@endphp

@section('content')

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header with-border">
                    <h3 class="card-title">Checkout Discount Settings</h3>
                </div>
                <div class="card-body">
                    {{ html()->form('POST', route('admin.front-setting.checkout-discounts.store'))->class('form-horizontal')->attribute('enctype', 'multipart/form-data')->open() }}

                    <div class="form-group row mb-4">
                        {{ html()->label('First Payment %')->class('col-md-4 form-control-label text-right')->for('checkout_payment_first') }}
                        <div class="col-md-8 mb-2">
                            {{ html()->text('checkout_payment_first', get_setting('checkout_payment_first'))->placeholder('Payment %')->class('form-control') }}
                        </div> <!-- col-->
                        {{ html()->label('First Discount %')->class('col-md-4 form-control-label text-right')->for('checkout_discount_first') }}
                        <div class="col-md-8">
                            {{ html()->text('checkout_discount_first', get_setting('checkout_discount_first'))->placeholder('Discount %')->class('form-control') }}
                        </div> <!-- col-->
                    </div> <!-- form-group-->

                    <div class="form-group row mb-4">
                        {{ html()->label('Second Payment %')->class('col-md-4 form-control-label text-right')->for('checkout_payment_second') }}
                        <div class="col-md-8 mb-2">
                            {{ html()->text('checkout_payment_second', get_setting('checkout_payment_second'))->placeholder('Payment %')->class('form-control') }}
                        </div> <!-- col-->
                        {{ html()->label('Second Discount %')->class('col-md-4 form-control-label text-right')->for('checkout_discount_second') }}
                        <div class="col-md-8">
                            {{ html()->text('checkout_discount_second', get_setting('checkout_discount_second'))->placeholder('Discount %')->class('form-control') }}
                        </div> <!-- col-->
                    </div> <!-- form-group-->

                    <div class="form-group row mb-4">
                        {{ html()->label('Third Payment %')->class('col-md-4 form-control-label text-right')->for('checkout_payment_third') }}
                        <div class="col-md-8 mb-2">
                            {{ html()->text('checkout_payment_third', get_setting('checkout_payment_third'))->placeholder('Payment %')->class('form-control') }}
                        </div> <!-- col-->
                        {{ html()->label('Third Discount %')->class('col-md-4 form-control-label text-right')->for('checkout_discount_third') }}
                        <div class="col-md-8">
                            {{ html()->text('checkout_discount_third', get_setting('checkout_discount_third'))->placeholder('Discount %')->class('form-control') }}
                        </div> <!-- col-->
                    </div> <!-- form-group-->


                    <div class="form-group row mb-4">
                        <div class="col-md-8 offset-md-4">
                            {{ html()->button('Update')->class('btn btn-sm btn-success') }}
                        </div> <!-- col-->
                    </div> <!-- form-group-->


                    {{ html()->form()->close() }}

                </div> <!--  .card-body -->
            </div> <!--  .card -->
        </div> <!-- .col-md-9 -->
    </div> <!-- .row -->

@endsection
