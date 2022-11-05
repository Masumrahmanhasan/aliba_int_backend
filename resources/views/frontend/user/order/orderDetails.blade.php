@extends('frontend.layouts.app')

@section('title', 'Order Item Details' )

@php
$currency = get_setting('currency_icon');
$productLoader = get_setting('product_image_loader');
@endphp
@section('content')
<div class="main_content">
  <div class="section pb-5">
    <div class="container">
      <div class="justify-content-around row">
        <div class="col-md-9">
          <div class="card">
            <div class="card-header">
              <h3>Orders Id #{{$order->order_item_number}} / <span class="text-success">{{$order->status}}</span></h3>
            </div>
            <div class="card-body">
              <a href="{{url($order->link)}}">{{$order->name}}</a>
              <div class="table-responsive mt-3">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th class="text-center" style="width: 150px">#</th>
                      <th class="text-center" colspan="2">Details</th>
                      <th class="text-center" style="width:20%">Rate({{$currency}})</th>
                      <th class="text-center" style="width:20%">Quantity</th>
                      <th class="text-center" style="width:20%">Total({{$currency}})</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                    $totalItemQty = 0;
                    $totalItemPrice = 0;
                    @endphp
                    @foreach($order->itemVariations as $variation )
                    @php
                    $attributes = json_decode($variation->attributes);
                    $attrLength = count($attributes);
                    $price = $variation->price;
                    $sinQuantity = $variation->quantity;
                    $subTotal = $variation->subTotal;
                    $totalItemQty += $sinQuantity;
                    $totalItemPrice += $subTotal;
                    @endphp
                    @foreach ($attributes as $attribute)
                    @php
                    $PropertyName = $attribute->PropertyName;
                    $Value = $attribute->Value;
                    @endphp
                    @if ($loop->first)
                    <tr>
                      <td class="align-middle text-center" rowspan="{{$attrLength}}">
                        <img class="img-fluid b2bLoading" data-src="{{asset($order->image)}}"
                          src="{{asset($productLoader)}}">
                      </td>
                      <td class="align-middle text-capitalize text-center">{!! $PropertyName !!}</td>
                      <td class="align-middle text-center " style="max-width: 120px">{{$Value}}</td>
                      <td class="align-middle text-center" rowspan="{{$attrLength}}"
                        style="max-width: 120px">{{floating($price)}}</td>
                      <td class="align-middle text-center" rowspan="{{$attrLength}}"> {{$sinQuantity}}</td>
                      <td class="align-middle text-right" rowspan="{{$attrLength}}">
                        <span class="SingleTotal">{{floating($subTotal)}}</span>
                      </td>
                    </tr>
                    @else
                    <tr>
                      <td class="text-capitalize align-middle  text-center">{!! $PropertyName !!}</td>
                      <td class=" text-center" style="max-width: 120px">{{$Value}}</td>
                    </tr>
                    @endif
                    @endforeach
                    @endforeach

                    <tr>
                      <td class="text-right" colspan="5">China Local Delivery (+)</td>
                      <td class="text-right">
                        <span>{{floating($order->chinaLocalDelivery)}}</span></td>
                    </tr>
                    @php
                    $totalItemPrice = $totalItemPrice + $order->chinaLocalDelivery;
                    @endphp
                    <tr>
                      <td class="text-right" colspan="5">Sub Total/Products Value</td>
                      <td class="text-right"><span
                          class="totalItemPrice">{{floating($totalItemPrice)}}</span></td>
                    </tr>
                    <tr>
                      @php
                      $first_payment = $order->first_payment + ($order->chinaLocalDelivery / 2 );
                      @endphp
                      <td class="text-right" colspan="5">First Payment (-)</td>
                      <td class="text-right">{{floating($first_payment)}}</td>
                    </tr>
                    <tr>
                      <td class=" text-right" colspan="5">
                        Shipping Charge (+) <span class="text-danger">(Shipping Rate X Actual weight)</span> <br>
                        {{$order->shipped_by .' - '.$currency .' '.floating($order->shipping_rate )}} X
                        {{$order->actual_weight ? $order->actual_weight : '0.00'}} Kg
                      </td>
                      <td class="text-right text-danger">
                        {{$order->shipping_charge ? $order->shipping_charge : '0.00' }} </td>
                    </tr>
                    @if ($order->out_of_stock)
                    <tr>
                      <td class="text-right" colspan="5">Out Of Stock (-)</td>
                      <td class="text-right">{{floating($order->out_of_stock)}}</td>
                    </tr>
                    @endif
                    @if ($order->missing)
                    <tr>
                      <td class="text-right" colspan="5">Missing (-)</td>
                      <td class="text-right">{{floating($order->missing)}}</td>
                    </tr>
                    @endif
                    @if ($order->refunded)
                    <tr>
                      <td class="text-right" colspan="5">Refunded (-)</td>
                      <td class="text-right">{{floating($order->refunded)}}</td>
                    </tr>
                    @endif
                    @if ($order->adjustment)
                    <tr>
                      <td class="text-right" colspan="5">Adjustment (+-)</td>
                      <td class="text-right">{{floating($order->adjustment)}}</td>
                    </tr>
                    @endif
                    @if ($order->courier_bill)
                    <tr>
                      <td class="text-right" colspan="5">Courier Bill (+)</td>
                      <td class="text-right">{{floating($order->courier_bill)}}</td>
                    </tr>
                    @endif

                    @if ($order->coupon_victory)
                    <tr>
                      <td class="text-right" colspan="5">Coupon (-)</td>
                      <td class="text-right">{{floating($order->coupon_victory)}}</td>
                    </tr>
                    @endif

                    @if ($order->due_payment)
                    <tr>
                      <td class="text-right" colspan="5">Net due</td>
                      <td class="text-right">{{floating($order->due_payment)}}</td>
                    </tr>
                    @endif

                  </tbody>
                </table>
              </div> <!-- table-responsive -->
            </div> <!-- card-body -->
          </div> <!-- .card -->
        </div> <!-- .card -->
      </div> <!-- col-lg-9 -->
    </div> <!-- row-->
  </div>
</div>
</div> <!-- END MAIN CONTENT -->
@endsection