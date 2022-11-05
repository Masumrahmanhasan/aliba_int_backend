@php
$currency = currency_icon();
@endphp

<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>@lang('Order Details') | {{$order->order_number}} | {{ $order->user->name ?? 'N/A' }} </title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <style>
    body {
      font-family: Source Sans Pro, Helvetica Neue, Helvetica, Arial, sans-serif;
      font-size: 14px;
    }
  </style>
</head>

<body>
  <main class="container-fluid">
    <div class="row">
      <div class="col-sm-12">

        <div class="card my-3">
          <div class="card-header">
            <h4 class="my-2">@lang('Order Details') #{{$order->order_number}}</h4>
          </div> <!-- card-header -->
          <div class="card-body pb-0">
            <div class="row">
              <div class="col-sm-6">
                <table class="table table-bordered table-sm">
                  <tr>
                    <th colspan="2" class="text-center">Customer Details</th>
                  </tr>
                  <tr>
                    <td style="width: 50%">Invoice Id#</td>
                    <td>{{$order->order_number}}</td>
                  </tr>
                  <tr>
                    <td>Transaction Id#</td>
                    <td>{{$order->transaction_id}}</td>
                  </tr>
                  <tr>
                    <td>Customer Name</td>
                    <td>{{ $order->user->name ?? 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td>Customer Phone</td>
                    <td>{{ $order->user->phone ?? 'N/A' }}</td>
                  </tr>
                </table>
              </div>
              <div class="col-sm-6">
                <table class="table table-bordered table-sm">
                  <tr>
                    <th colspan="2" class="text-center">Shipping Details</th>
                  </tr>
                  @php
                  $address = json_decode($order->address) ?? null;
                  @endphp
                  <tr>
                    <td style="width: 50%">Shipping Name</td>
                    <td>{{ $address ? $address->name : 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td>Phone</td>
                    <td>{{ $address->phone ?? 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td>District</td>
                    <td>{{ $address->district ?? 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td>Address</td>
                    <td>{{ $address->address ?? 'N/A' }}</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <div class="card-body pt-0">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th class="text-center" style="width: 130px;">#</th>
                    <th class="text-center" colspan="2">Details</th>
                    <th class="text-center" style="width:80px">Quantity</th>
                    <th class="text-center" style="width:100px">Total</th>
                    <th class="text-center" style="width:100px">Taobao</th>
                    <th class="text-center" style="width:100px">Order Date</th>
                    <th class="text-center" style="width:100px">Status</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $invoiceTotal = 0;
                  @endphp
                  @foreach ($order->orderItems as $item)
                  <tr>
                    <td class="text-left" colspan="9">
                      <span style="font-size: 16px;" class="text-danger">{{$item->order_item_number}}</span> / <a
                        href="{{url($item->link)}}">{{strip_tags($item->name)}}</a>
                    </td>
                  </tr>
                  @php
                  $itemTotalPrice = 0;
                  @endphp

                  @foreach($item->itemVariations as $variationKey => $variation )

                  @php
                  $attributes = json_decode($variation->attributes);
                  $attrLength = count($attributes) + 1;
                  $sinQuantity = $variation->quantity;
                  $subTotal = $variation->subTotal;
                  $itemTotalPrice += $subTotal;
                  @endphp
                  @forelse ($attributes as $attribute)
                  @php
                  $PropertyName = $attribute->PropertyName;
                  $Value = $attribute->Value;
                  @endphp
                  @if ($loop->first)
                  <tr>
                    <td class="align-middle text-center" rowspan="{{$attrLength}}">
                      @php
                      $variation_img = $variation->image ? $variation->image : $variation->product->MainPictureUrl ??
                      '';
                      @endphp
                      <img src="{{asset($variation_img)}}" class="img-fluid">
                    </td>
                    <td class="text-capitalize text-center">{!! $PropertyName !!}</td>
                    <td class="align-middle text-center">{{$Value}}</td>
                    <td class="align-middle text-center" rowspan="{{$attrLength}}"> {{$sinQuantity}}</td>
                    <td class="align-middle text-right" rowspan="{{$attrLength}}">
                      <span class="SingleTotal">{{floating($subTotal)}}</span>
                    </td>
                    @if ($variationKey === 0)
                    @php
                    $LengthTotal = (count($item->itemVariations) * $attrLength) + 4;
                    @endphp
                    <td class="align-middle text-center" rowspan="{{$LengthTotal}}">
                      @php
                      $product_id = $item->product ? $item->product->ItemId : '';
                      @endphp
                      <a href="https://item.taobao.com/item.htm?id={{$product_id}}" class="btn btn-sm btn-secondary"
                        target="_blank">Click</a>

                    </td>
                    <td class="align-middle text-center" rowspan="{{$LengthTotal}}">
                      <p class="m-0">{{date('d M, Y', strtotime($item->created_at))}}</p>
                      <p class="m-0">at {{date('h:ia', strtotime($item->created_at))}}</p>
                    </td>
                    <td class="align-middle text-center" rowspan="{{$LengthTotal}}">
                      <span class="singleStatus text-capitalize">{{ $item->status}}</span>
                    </td>
                    @endif
                  </tr>
                  @else
                  <tr>
                    <td class="text-capitalize  text-center">{!! $PropertyName !!}</td>
                    <td class=" text-center">{{$Value}}</td>
                  </tr>
                  @endif
                  @empty
                  <tr>
                    <td class="align-middle text-center" rowspan="2">
                      @php
                      $variation_img = $variation->image ? $variation->image : $variation->product->MainPictureUrl ??
                      '';
                      @endphp
                      <img src="{{asset($variation_img)}}" class="img-fluid">
                    </td>
                    <td colspan="2" class="align-middle text-center">No Attributes</td>
                    <td class="align-middle text-center" rowspan="2">{{$sinQuantity}}</td>
                    <td class="align-middle text-right" rowspan="2">
                      <span class="SingleTotal">{{floating($subTotal)}}</span>
                    </td>
                    <td class="align-middle text-center" rowspan="5">
                      @php
                      $product_id = $item->product ? $item->product->ItemId : '';
                      @endphp
                      <a href="https://item.taobao.com/item.htm?id={{$product_id}}" class="btn btn-sm btn-secondary"
                        target="_blank">Click</a>
                    </td>
                    <td class="align-middle text-center" rowspan="5">{{date('d M, Y', strtotime($item->created_at))}}
                    </td>
                    <td rowspan="5"></td>
                  </tr>
                  @endforelse
                  <tr>
                    <td class="text-right">Per unit Price</td>
                    <td class="text-right">
                      <span class="unitPrice">{{floating($variation->price)}}</span>
                    </td>
                  </tr>
                  @endforeach
                  @php
                  $chinaLocalDelivery = $item->chinaLocalDelivery;
                  @endphp
                  <tr>
                    <td class="text-right" colspan="3">China Local Delivery</td>
                    <td class="text-center">-</td>
                    <td class="text-right"><span>{{floating($chinaLocalDelivery)}}</span></td>
                  </tr>

                  @php
                  $coupon_contribution = $item->coupon_contribution;
                  @endphp
                  @if($coupon_contribution)
                  <tr>
                    <td class="text-right" colspan="3">Coupon (-)</td>
                    <td class="text-center align-middle">-</td>
                    <td class="text-right"><span class="totalItemPrice">{{floating($coupon_contribution)}}</span>
                    </td>
                  </tr>
                  @endif

                  @php
                  $shippingCharge = $item->shipping_rate * (is_numeric($item->actual_weight) ? $item->actual_weight :
                  0);
                  $shippingCharge = $shippingCharge < 100 ? 100 : $shippingCharge; @endphp <tr>
                    <td class="text-right" colspan="3"> Shipping Charge
                      <p class="m-0 text-danger">Shipping Method
                        {{$item->shipped_by .' - '.floating($item->shipping_rate)}} Per KG</p>
                      <p class="m-0 text-danger">Approx weight -
                        {{$item->actual_weight ? $item->actual_weight : 0 }} KG</p>
                    </td>
                    <td class="text-center align-middle">-</td>
                    <td class="text-right align-middle"><span>{{floating($shippingCharge)}}</span>
                    </td>
                    </tr>
                    @php
                    $itemTotalPrice = $itemTotalPrice + $chinaLocalDelivery + $shippingCharge - $coupon_contribution;
                    $invoiceTotal += $itemTotalPrice;
                    @endphp

                    <tr>
                      <td class="text-right" colspan="3">Sub Total</td>
                      <td class="text-center">{{$item->quantity}}</td>
                      <td class="text-right"><span class="totalItemPrice">{{floating($itemTotalPrice)}}</span>
                      </td>
                    </tr>

                    @endforeach
                </tbody>
              </table>

              <table class="table table-bordered table-striped">
                <tr>
                  <td class="text-right" colspan="5">Products Price</td>
                  <td class="text-right">{{floating($invoiceTotal)}}</td>
                </tr>
                @if ($order->coupon_victory)
                <tr>
                  <td class="text-right" colspan="5">Coupon</td>
                  <td class="text-right">{{floating($order->coupon_victory)}}</td>
                </tr>
                @endif
                <tr>
                  <td class="text-right" colspan="5">Need to Pay 50%</td>
                  <td class="text-right">{{floating($invoiceTotal * 0.5)}}</td>
                </tr>
                <tr>
                  <td class="text-right text-danger" colspan="5">Due (Only for products)</td>
                  <td class="text-right text-danger">{{floating($invoiceTotal * 0.5)}}</td>
                </tr>
              </table>


            </div>
          </div> <!-- card-body -->
        </div> <!-- card -->
      </div>
    </div>
  </main>


</body>

</html>