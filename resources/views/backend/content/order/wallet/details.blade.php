<div class="card">
  <div class="card-header mb-3">
    <h4 class="card-title mb-0"> Wallet Details. Orders Id #{{$order->order_item_number}} / <span
          class="text-success">{{$order->status}}</span></h4>
  </div>
  <div class="card-body">
    <a href="{{url($order->link)}}">{{$order->name}}</a>
    <div class="table-responsive mt-3">
      <table class="table table-bordered">
        <thead>
        <tr>
          <th class="text-center" style="width: 150px">#</th>
          <th class="text-center" colspan="2">Details</th>
          <th class="text-center" style="width:20%">Rate</th>
          <th class="text-center" style="width:20%">Quantity</th>
          <th class="text-center" style="width:20%">Total</th>
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
          @forelse ($attributes as $attribute)
            @php
              $PropertyName = $attribute->PropertyName;
              $Value = $attribute->Value;
            @endphp
            @if ($loop->first)
              <tr>
                <td class="align-middle text-center" rowspan="{{$attrLength}}">
                  @php
                    $variation_img = $variation->image ? $variation->image : $variation->product->MainPictureUrl ?? '';
                  @endphp
                  <img class="img-fluid b2bLoading" src="{{asset($variation_img)}}">
                </td>
                <td class="align-middle text-capitalize text-center">{!! $PropertyName !!}</td>
                <td class="align-middle text-center text-break" style="max-width: 120px">{{$Value}}</td>
                <td class="align-middle text-center text-break" rowspan="{{$attrLength}}" style="max-width: 120px">
                  {{$currency}} {{floating($price)}}</td>
                <td class="align-middle text-center" rowspan="{{$attrLength}}"> {{$sinQuantity}}</td>
                <td class="align-middle text-right" rowspan="{{$attrLength}}">
                  <span class="SingleTotal">{{$currency}} {{floating($subTotal)}}</span>
                </td>
              </tr>
            @else
              <tr>
                <td class="text-capitalize align-middle  text-center">{!! $PropertyName !!}</td>
                <td class=" text-center text-break" style="max-width: 120px">{{$Value}}</td>
              </tr>
            @endif
          @empty
            <tr>
              <td class="align-middle text-center">
                @php
                  $variation_img = $variation->image ? $variation->image : $variation->product->MainPictureUrl ?? '';
                @endphp
                <img src="{{asset($variation_img)}}" class="img-fluid">
              </td>
              <td colspan="2" class="align-middle text-center">No Attribites</td>
              <td class="align-middle text-center"><span class="unitPrice">{{floating($variation->price)}}</span>
              </td>
              <td class="align-middle text-center">{{$sinQuantity}}</td>
              <td class="align-middle text-right">
                <span class="SingleTotal">{{floating($subTotal)}}</span>
              </td>
            </tr>
          @endforelse

        @endforeach

        <tr>
          <td class="text-right" colspan="5">China Local Delivery (+)</td>
          <td class="text-right">{{$currency}}
            <span>{{floating($order->chinaLocalDelivery)}}</span></td>
        </tr>
        @php
          $totalItemPrice = $totalItemPrice + $order->chinaLocalDelivery;
        @endphp
        <tr>
          <td class="text-right" colspan="5">Sub Total/Products Value</td>
          <td class="text-right">{{$currency}} <span class="totalItemPrice">{{floating($totalItemPrice)}}</span>
          </td>
        </tr>

        <tr>
          <td class="text-right" colspan="5">First Payment (-)</td>
          <td class="text-right">{{$currency.' '.floating($order->first_payment)}}</td>
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
            <td class="text-right">{{$currency.' '.floating($order->out_of_stock)}}</td>
          </tr>
        @endif
        @if ($order->missing)
          <tr>
            <td class="text-right" colspan="5">Missing (-)</td>
            <td class="text-right">{{$currency.' '.floating($order->missing)}}</td>
          </tr>
        @endif
        @if ($order->refunded)
          <tr>
            <td class="text-right" colspan="5">Refunded (-)</td>
            <td class="text-right">{{$currency.' '.floating($order->refunded)}}</td>
          </tr>
        @endif
        @if ($order->adjustment)
          <tr>
            <td class="text-right" colspan="5">Adjustment (+-)</td>
            <td class="text-right">{{$currency.' '.floating($order->adjustment)}}</td>
          </tr>
        @endif
        @if ($order->courier_bill)
          <tr>
            <td class="text-right" colspan="5">Courier Bill (+)</td>
            <td class="text-right">{{$currency.' '.floating($order->courier_bill)}}</td>
          </tr>
        @endif

        @if ($order->coupon_contribution)
          <tr>
            <td class="text-right" colspan="5">Coupon (-)</td>
            <td class="text-right">{{$currency.' '.floating($order->coupon_contribution)}}</td>
          </tr>
        @endif

        @if ($order->due_payment)
          <tr>
            <td class="text-right" colspan="5">Net due</td>
            <td class="text-right">{{$currency.' '.floating($order->due_payment)}}</td>
          </tr>
        @endif

        </tbody>
      </table>
    </div> <!-- table-responsive -->

  </div> <!-- card-body-->
</div> <!-- card-->