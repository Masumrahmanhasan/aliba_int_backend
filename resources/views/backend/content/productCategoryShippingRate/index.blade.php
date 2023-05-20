@extends('backend.layouts.app')

@section('title', ' Product Category Shipping Rate Management')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-5">
                    <h4 class="card-title mb-0"> Product Category Shipping Rate Management </h4>
                </div> <!-- col-->

                <div class="col-sm-7 pull-right">
                    @include('backend.content.productCategoryShippingRate.includes.header-buttons')
                </div> <!-- col-->
            </div> <!-- row-->

            <div class="row mt-4">
                <div class="col">
                    <div class="table-responsive">
                        <table class="table text-center">
                            <thead>
                                <tr class="vertical-middle">
                                    <th>#</th>
                                    <th>Category Name</th>
                                    <th>Shipping Rate</th>
                                    <th>@lang('labels.general.actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($productCategoryShippingRates as $productCategoryShippingRate)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $productCategoryShippingRate->category }}</td>
                                        <td>{{ $productCategoryShippingRate->shipping_rate }}</td>
                                        <td>
                                            <form action="{{ route('admin.product-category-shipping-rate.destroy', $productCategoryShippingRate) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div> <!-- col-->
            </div> <!-- row-->
        </div> <!-- card-body-->
    </div> <!-- card-->
@endsection
