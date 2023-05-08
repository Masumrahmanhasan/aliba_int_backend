<div class="card">
    <div class="card-header mb-2">
        <h2 class="mb-0 text-center" style="color: orange;">Orders No: #{{ $order->order_item_number }}</h2>
    </div>
    {{-- <span class="text-success">{{ $order->status }}</span> --}}

    {{-- <div class="card-body pb-0">
        <div class="row">
            <div class="col-sm-6">
                <table class="table table-bordered table-sm">
                    <tr>
                        <th colspan="2" class="text-center">Customer Details</th>
                    </tr>
                    <tr>
                        <td style="width: 50%">Name</td>
                        <td>{{ $order->user->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Phone</td>
                        <td>{{ $order->user->phone ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-sm-6">
                <table class="table table-bordered table-sm">
                    <tr>
                        <th colspan="2" class="text-center">Refund Details</th>
                    </tr>
                    <tr>
                        <td>Refund Method</td>
                        <td>{{ $order->user->refund_method ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Refund Credentials</td>
                        <td>{{ $order->user->refund_credentials ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div> --}}

    <div class="card-body">
        <div class="d-flex">
            <div>
                <img src="{{ asset($order->image) }}" style="width: 100px;">
            </div>
            <div class="ml-3">
                <h5>{{ $order->name }}</h5>
                @php
                    $product = explode('-', $order->link);
                @endphp
                <a href="https://alibainternational.com{{ $order->link }}" target="_blank" class="btn"
                    style="background-color: orange; color: white;">Alibainternational.com<i
                        class="fa fa-external-link ml-2"></i></a>
                <a href="https://detail.1688.com/offer/{{ end($product) }}.html" target="_blank" class="btn ml-2"
                    style="background-color: orange; color: white;">1688.com<i class="fa fa-external-link ml-2"></i></a>
            </div>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 100px">#</th>
                        <th class="text-center" colspan="2">Details</th>
                        <th class="text-center" style="width:20%">Quantity</th>
                        <th class="text-center" style="width:20%">Price</th>
                        <th class="text-center" style="width:20%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalItemQty = 0;
                        $totalItemPrice = 0;
                    @endphp
                    @forelse ($order->itemVariations as $variation)
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
                                    <td class="align-middle text-center" rowspan="{{ $attrLength }}">
                                        @php
                                            $variation_img = $variation->image ? $variation->image : $variation->product->MainPictureUrl ?? '';
                                        @endphp
                                        <img class="img-fluid b2bLoading" style="width: 50px;"
                                            src="{{ asset($variation_img) }}">
                                    </td>
                                    <td class="align-middle text-capitalize text-center">{!! $PropertyName !!}</td>
                                    <td class="align-middle text-center text-break" style="max-width: 120px">
                                        {{ $Value }}</td>
                                    <td class="align-middle text-center" rowspan="{{ $attrLength }}">
                                        {{ $sinQuantity }}</td>
                                    <td class="align-middle text-center text-break" rowspan="{{ $attrLength }}"
                                        style="max-width: 120px">
                                        {{ $currency }} {{ floating($price) }}</td>
                                    <td class="align-middle text-right" rowspan="{{ $attrLength }}">
                                        <span class="SingleTotal">{{ $currency }} {{ floating($subTotal) }}</span>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td class="text-capitalize align-middle  text-center">{!! $PropertyName !!}</td>
                                    <td class=" text-center text-break" style="max-width: 120px">{{ $Value }}
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td class="align-middle text-center">
                                    @php
                                        $variation_img = $variation->image ? $variation->image : $variation->product->MainPictureUrl ?? '';
                                    @endphp
                                    <img src="{{ asset($variation_img) }}" class="img-fluid">
                                </td>
                                <td colspan="2" class="align-middle text-center">No Attribites</td>
                                <td class="align-middle text-center">{{ $sinQuantity }}</td>
                                <td class="align-middle text-center"><span
                                        class="unitPrice">{{ floating($variation->price) }}</span>
                                </td>
                                <td class="align-middle text-right">
                                    <span class="SingleTotal">{{ floating($subTotal) }}</span>
                                </td>
                            </tr>
                        @endforelse
                    @empty
                        @php
                            $totalItemPrice = $order->product_value;
                        @endphp
                        <tr>
                            <td class="align-middle text-center">
                                <img class="img-fluid b2bLoading" style="width: 50px;"
                                    src="{{ asset($order->image) }}">
                            </td>
                            <td colspan="2" class="align-middle text-center">No data</td>
                            <td class="align-middle text-center">{{ $order->quantity }}</td>
                            <td class="align-middle text-center">{{ $currency }}
                                {{ $order->product_value / $order->quantity }}</td>
                            <td class="align-middle text-center">{{ $currency }} {{ $order->product_value }}</td>
                        </tr>
                    @endforelse
                    <tr>
                        <td colspan="3"></td>
                        <td class="text-center"><b>Total Quantity: </b>{{ $order->quantity }}</td>
                        <td colspan="2"></td>
                    </tr>
                    @php
                        $totalItemPrice = $totalItemPrice + $order->chinaLocalDelivery;
                        $discount = json_decode($order->order->pay_discount);
                    @endphp
                    <tr>
                        <td class="text-right" colspan="2"><b>China Local Delivery (+)</b></td>
                        <td class="text-left">{{ $currency }}
                            <span>{{ floating($order->chinaLocalDelivery) }}</span>
                        </td>
                        <td class="text-right" colspan="2"><b>Products Value</b></td>
                        <td class="text-right">{{ $currency }} <span
                                class="totalItemPrice">{{ floating($totalItemPrice) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right" colspan="2"><b>Payment Method</b></td>
                        <td class="text-left">{{ $order->order->pay_method }}</td>
                        <td class="text-right" colspan="2"><b>First Payment ({{ $order->order->pay_percent }}%)</b>
                        </td>
                        <td class="text-right">{{ $currency . ' ' . floating($order->first_payment) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right" colspan="2"><b>Coupon</b></td>
                        <td class="text-left">{{ $currency }} {{ $order->coupon_contribution }}</td>
                        <td class="text-center"><b>Discount {{ $discount->percent }}% :</b> {{ $currency }}
                            {{ $discount->amount / $discount->product_count }}</td>
                        <td class="text-right"><b>Total Discount (-)</b></td>
                        <td class="text-right">{{ $currency }}
                            {{ $order->coupon_contribution + $discount->amount / $discount->product_count }}</td>
                    </tr>
                    @if ($order->out_of_stock)
                        <tr>
                            <td class="text-right" colspan="5">Out Of Stock (-)</td>
                            <td class="text-right">{{ $currency . ' ' . floating($order->out_of_stock) }}</td>
                        </tr>
                    @endif
                    @if ($order->missing)
                        <tr>
                            <td class="text-right" colspan="5">Missing (-)</td>
                            <td class="text-right">{{ $currency . ' ' . floating($order->missing) }}</td>
                        </tr>
                    @endif
                    @if ($order->courier_bill)
                        <tr>
                            <td class="text-right" colspan="5">Courier Bill (+)</td>
                            <td class="text-right">{{ $currency . ' ' . floating($order->courier_bill) }}</td>
                        </tr>
                    @endif

                    @if ($order->coupon_contribution)
                        <tr>
                            <td class="text-right" colspan="5"> <b>Coupon (-)</b> </td>
                            <td class="text-right">{{ $currency . ' ' . floating($order->coupon_contribution) }}</td>
                        </tr>
                    @endif

                    <tr>
                        <td class="text-right align-middle text-success" colspan="2">Status</td>
                        <td class="text-left text-success">
                            <select class="col-md-12" style="border: 1px solid orange; border-radius: 2px;"
                                name="order_status" id="order_status">
                                <option value="waiting-for-payment" @if ($order->status == 'waiting-for-payment') selected @endif>
                                    Waiting for Payment</option>
                                <option value="partial-paid" @if ($order->status == 'partial-paid') selected @endif disabled>
                                    Partial Paid</option>
                                <option value="full-paid" @if ($order->status == 'full-paid') selected @endif disabled>
                                    Full Paid</option>
                                <option value="purchased" @if ($order->status == 'purchased') selected @endif>
                                    Purchased</option>
                                <option value="shipped-from-suppliers"
                                    @if ($order->status == 'shipped-from-suppliers') selected @endif>Shipped from Suppliers
                                </option>
                                <option value="received-in-china-warehouse"
                                    @if ($order->status == 'received-in-china-warehouse') selected @endif>Received in China Warehouse
                                </option>
                                <option value="shipped-from-china-warehouse"
                                    @if ($order->status == 'shipped-from-china-warehouse') selected @endif>Shipped from China Warehouse
                                </option>
                                <option value="BD-customs" @if ($order->status == 'BD-customs') selected @endif>BD
                                    Customs</option>
                                <option value="ready-to-deliver" @if ($order->status == 'ready-to-deliver') selected @endif>
                                    Ready to Deliver</option>

                                <option value="delivered" @if ($order->status == 'delivered') selected @endif>
                                    Delivered</option>
                                <option value="out-of-stock" @if ($order->status == 'out-of-stock') selected @endif>Out
                                    of Stock</option>
                                <option value="refunded" @if ($order->status == 'refunded') selected @endif>
                                    Refunded</option>
                            </select>
                        </td>
                        <td></td>
                        <td class="text-success text-right">
                            <b>Status</b>
                        </td>
                        <td class="text-danger text-right">
                            <b id="update_status">{{ $order->status }}</b>
                        </td>
                    </tr>
                    <tr id="inputs_for" data-order="{{ $order }}">
                        {{-- Addition inputs are appended here through jquery --}}
                        @if ($order->status == 'purchased')
                            <td class="text-right align-middle" colspan="2">
                                <b>Order No.</b>
                            </td>
                            <td>
                                <input class="col-md-12" style="border: 1px solid orange; border-radius: 2px;"
                                    type="text" name="order_number" id="order_number"
                                    value="{{ $order->order_number }}">
                            </td>
                            <td colspan="3"></td>
                        @endif

                        @if ($order->status == 'shipped-from-suppliers')
                            <td class="text-right align-middle" colspan="2">
                                <b>Tracking No.</b>
                            </td>
                            <td>
                                <input class="col-md-12" style="border: 1px solid orange; border-radius: 2px;"
                                    type="text" name="tracking_number" id="tracking_number"
                                    value="{{ $order->tracking_number }}">
                            </td>
                            <td colspan="3"></td>
                        @endif

                        @if ($order->status == 'ready-to-deliver')
                            <td class="text-right align-middle" colspan="2">
                                <b>Weight</b>
                            </td>
                            <td>
                                <input class="col-md-12" style="border: 1px solid orange; border-radius: 2px;"
                                    type="number" step=".01" name="actual_weight" id="actual_weight"
                                    value="{{ $order->actual_weight }}">
                            </td>
                            <td colspan="3"></td>
                        @endif

                        @if ($order->status == 'refunded')
                            <td class="text-right align-middle">
                                <b>Refund Amount</b>
                            </td>
                            <td class="align-middle">
                                <input class="col-md-12" style="border: 1px solid orange; border-radius: 2px;"
                                    type="number" step=".01" name="refunded" id="refunded"
                                    value="{{ $order->refunded }}">
                            </td>
                            <td class="text-right align-middle">
                                <b>Refund TrxId</b>
                            </td>
                            <td class="align-middle">
                                <input class="col-md-12" style="border: 1px solid orange; border-radius: 2px;"
                                    type="text" name="refund_trxId" id="refund_trxId"
                                    value="{{ $order->refund_trxId }}">
                            </td>
                            <td class="text-right align-middle">
                                <b>Refund Statement</b>
                            </td>
                            <td class="align-middle">
                                <input class="col-md-12" style="border: 1px solid orange; border-radius: 2px;"
                                    type="text" name="refund_statement" id="refund_statement"
                                    value="{{ $order->refund_statement }}">
                            </td>
                        @endif
                    </tr>

                    @if ($order->status != 'refunded')
                        <tr>
                            <td class="text-right align-middle" colspan="5"><b>PRODUCT DUE</b></td>
                            <td class="text-right align-middle">{{ $currency . ' ' . floating($order->due_payment) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right" colspan="4"><b>Shipping Per KG <span
                                        style="color: orange;">(Total
                                        Weight x Shipping Fee)</span></b></td>
                            <td class="text-right"><b class="">
                                    <span class="col-md-7">
                                        <span
                                            id="update_weight">{{ $order->actual_weight ? $order->actual_weight : 0 }}</span>
                                        KG &nbsp; x
                                    </span>
                                    <input class="col-md-5" style="border: 1px solid orange; border-radius: 2px;"
                                        type="number" name="shipping_rate" value="{{ $order->shipping_rate }}"
                                        id="shipping_rate">
                                </b></td>
                            @php
                                $shipping_charge = ($order->actual_weight ? $order->actual_weight : 0) * $order->shipping_rate;
                            @endphp
                            <td class="text-right">{{ $currency }} <span
                                    id="update_shipping_rate">{{ floating($shipping_charge) }}</span></td>
                        </tr>

                        <tr>
                            <td class="text-right" colspan="5"><b>Adjustment (+-)</b></td>
                            <td class="text-right">
                                <input class="col-md-5" style="border: 1px solid orange; border-radius: 2px;"
                                    type="number" name="adjustment" value="{{ $order->adjustment }}"
                                    id="adjustment">
                            </td>
                        </tr>

                        <tr>
                            <td colspan="3">
                                @if ($order->order->status == 'partial-paid')
                                    <h5 style="padding: 5px 10px; border-radius: 15px;"
                                        class="bg-success text-white text-center">Approved Initial Payment</h5>
                                @elseif ($order->order->status == 'full-paid')
                                    <h5 style="padding: 5px 10px; border-radius: 15px;"
                                        class="bg-success text-white text-center">Approved Full Payment</h5>
                                @else
                                    <h5 style="padding: 5px 10px; border-radius: 15px;"
                                        class="bg-danger text-white text-center">Waiting for Payment Approval</h5>
                                @endif
                            </td>
                            <td style="background-color: orange;" class="text-right" colspan="2">
                                <h4>NET DUE</h4>
                            </td>
                            @php
                                $total = $shipping_charge + $order->due_payment + $order->adjustment;
                            @endphp
                            <td style="background-color: orange;" class="text-right">
                                <h4>{{ $currency }} <span id="update_net_due">{{ floating($total) }}</span></h4>
                            </td>
                        </tr>
                    @endif
                    @if ($order->status == 'refunded')
                        <tr style="background-color: orange;">
                            <td class="text-right align-middle" colspan="2">
                                <h4>REFUNDED</h4>
                            </td>
                            <th colspan="3">{{ $order->refund_statement }}</th>
                            <td class="text-right">
                                <h4>{{ $currency . ' ' . floating($order->refunded) }}</h4>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <td colspan="5" style="background-color: orange;" class="text-white text-center">
                            <h4>For Aliba International Accounts</h4>
                            </th>
                    </tr>
                    <tr>
                        <th class="text-center align-middle">Product Value in RMB</th>
                        <th class="text-center align-middle">RMB Buying Rate</th>
                        <th class="text-center align-middle">Purchase Agent Percentage</th>
                        <th class="text-center align-middle">Company Shipping Weight</th>
                        <th class="text-center align-middle">Company Shipping Per KG Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="number" id="accounts_rmb_price_value"
                                value="{{ $order->accounts_rmb_price_value ?? 0 }}" class="col-9" step="0.01"
                                style="border: 1px solid orange; border-radius: 2px;"> &nbsp; &nbsp; <b>RMB</b>
                        </td>
                        <td>
                            <input type="number" id="accounts_rmb_buying_rate"
                                value="{{ $order->accounts_rmb_buying_rate ?? 0 }}" class="col-9" step="0.01"
                                style="border: 1px solid orange; border-radius: 2px;"> &nbsp; &nbsp; <b>BDT</b>
                        </td>
                        <td>
                            <input type="number" id="accounts_agent_percentage"
                                value="{{ $order->accounts_agent_percentage ?? 0 }}" class="col-9" step="0.01"
                                style="border: 1px solid orange; border-radius: 2px;"> &nbsp; &nbsp; <b>%</b>
                        </td>
                        <td>
                            <input type="number" id="accounts_company_shipping_weight"
                                value="{{ $order->accounts_company_shipping_weight ?? 0 }}" class="col-9"
                                step="0.01" style="border: 1px solid orange; border-radius: 2px;"> &nbsp; &nbsp;
                            <b>KG</b>
                        </td>
                        <td>
                            <input type="number" id="accounts_company_shipping_rate"
                                value="{{ $order->accounts_company_shipping_rate ?? 0 }}" class="col-9"
                                step="0.01" style="border: 1px solid orange; border-radius: 2px;"> &nbsp; &nbsp;
                            <b>BDT</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                        <td class="text-center">
                            <b>Profit / Loss (+-)</b>
                        </td>
                        <td>
                            <input type="number" id="accounts_profit_loss"
                                value="{{ $order->accounts_profit_loss ?? 0 }}" class="col-9" step="0.01"
                                style="border: 1px solid orange; border-radius: 2px;"> &nbsp; &nbsp; <b>BDT</b>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div> <!-- table-responsive -->

    </div> <!-- card-body-->
</div> <!-- card-->

<script src="https://code.jquery.com/jquery-3.6.1.min.js"
    integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
<script>
    $('#order_status').change(function() {
        let status = $('#order_status :selected').val();

        let inputs = '';
        if (status == 'purchased') {
            inputs = `
                <td class="text-right align-middle" colspan="2">
                    <b>Order No.</b>
                </td>
                <td>
                    <input class="col-md-12" style="border: 1px solid orange; border-radius: 2px;" type="text" name="order_number" id="order_number" value="{{ $order->order_number }}">
                </td>
                <td colspan="3"></td>
            `;
        }

        if (status == 'shipped-from-suppliers') {
            inputs = `
                <td class="text-right align-middle" colspan="2">
                    <b>Tracking No.</b>
                </td>
                <td>
                    <input class="col-md-12" style="border: 1px solid orange; border-radius: 2px;" type="text" name="tracking_number" id="tracking_number" value="{{ $order->tracking_number }}">
                </td>
                <td colspan="3"></td>
            `;
        }

        if (status == 'ready-to-deliver') {
            inputs = `
                <td class="text-right align-middle" colspan="2">
                    <b>Weight</b>
                </td>
                <td>
                    <input class="col-md-12" style="border: 1px solid orange; border-radius: 2px;" type="number" step=".01" name="actual_weight" id="actual_weight" value="{{ $order->actual_weight }}">
                </td>
                <td colspan="3"></td>
            `;
        }

        if (status == 'refunded') {
            inputs = `
                <td class="text-right align-middle">
                    <b>Refund Amount</b>
                </td>
                <td class="align-middle">
                    <input class="col-md-12" style="border: 1px solid orange; border-radius: 2px;" type="number" step=".01" name="refunded" id="refunded" value="{{ $order->refunded }}">
                </td>
                <td class="text-right align-middle">
                    <b>Refund TrxId</b>
                </td>
                <td class="align-middle">
                    <input class="col-md-12" style="border: 1px solid orange; border-radius: 2px;" type="text" name="refund_trxId" id="refund_trxId" value="{{ $order->refund_trxId }}">
                </td>
                <td class="text-right align-middle">
                    <b>Refund Statement</b>
                </td>
                <td class="align-middle">
                    <input class="col-md-12" style="border: 1px solid orange; border-radius: 2px;" type="text" name="refund_statement" id="refund_statement" value="{{ $order->refund_statement }}">
                </td>
            `;
        }

        $('#inputs_for').empty();
        $('#inputs_for').append(inputs);
    });
</script>
