<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SManagerPaymentController;
use App\Models\Content\Coupon;
use App\Models\Content\CouponUser;
use App\Models\Content\Frontend\CustomerCart;
use App\Models\Content\Invoice;
use App\Models\Content\Order;
use App\Models\Content\OrderItem;
use App\Models\Content\OrderItemVariation;
use App\Models\Content\Product;
use App\Models\Auth\User;
use App\Models\Content\Cart;
use App\Traits\ApiResponser;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{

    use ApiResponser;

    public function confirmOrders()
    {
        $cart = json_decode(request('cart'), true);
        $address = json_decode(request('address'), true);
        $summary = json_decode(request('summary'), true);
        $pwgDiscount = json_decode(request('pwgDiscount'), true);

        $tran_id = generate_transaction_id();
        $pay_method = request('paymentMethod');
        $percent = request('advancePercent');

        try {
            DB::transaction(function () use ($tran_id, $pay_method, $cart, $summary, $address, $percent, $pwgDiscount) {
                $status = 'waiting-for-payment';

                if (request('shopAsCustomer') == true) {
                    $auth_id = request('id');
                } else {
                    $auth_id = auth()->id();
                }

                $order = $this->orderStore($tran_id, $pay_method, $summary, $address, $status, $percent, $pwgDiscount, $auth_id);
                foreach ($cart as $product) {
                    $itemVariations = $product['ConfiguredItems'];
                    $OrderItemData = [
                        'name' => $product['Title'],
                        'link' => "/product/{$product['Id']}",
                        'shipped_by' => 'Air',
                        'shipping_rate' => $product['shippingRate'] ?? 0,
                        'approxWeight' => $product['ApproxWeight'],
                        'status' => $status,
                    ];

                    $this->storeOrderItems($order, $product, $itemVariations, $OrderItemData, $percent, $pwgDiscount, $auth_id);
                }

                CustomerCart::where('user_id', $auth_id)->whereNull('buy_status')->update([
                    'buy_status' => Carbon::now()->toDateTimeString()
                ]);
            }, 3);
        } catch (\Exception $ex) {
            return $this->error($ex, 417);
        }

        if ($pay_method === "sManager") {
            $sManager =  new  SManagerPaymentController();
            $feedback = $sManager->initial_payment($tran_id);
            if (is_array($feedback)) {
                return $this->success($feedback);
            }
        } else if ($pay_method === "nagad_payment" || $pay_method === "bkash_payment" || $pay_method === "bank_payment") {
            $user = User::where('email', 'rayhan@lskit.com')->first();
            $subject = "Order placed | 1688cart.com";
            $generateText = "An order on your site 1688cart.com has been placed. Please visit https://admin.1688cart.com to check in detail.";

            send_status_email($generateText, $subject, $user);

            $order = Order::where('user_id', auth()->id())->latest()->first();
            return $this->success([
                'status' => 'success',
                'message' => 'Your order placed successfully',
                'redirect' => '/payment/' . $order->id
            ]);
        }

        return $this->error('Your order has not placed', 417);
    }

    public function cancelOrders($order_id)
    {
        if (request('shopAsCustomer') == true) {
            $user_id = request('id');
        } else {
            $user_id = auth()->id();
        }

        $order = Order::where('user_id', $user_id)->where('id', $order_id)->first();

        if (!empty($order)) {

            $order->update([
                'status' => 'cancelled',
                'cancellation_reason' => request('reason', 'No reason')
            ]);

            return $this->success([
                'status' => 'success',
                'message' => 'Your order has been canceled successfully',
                'redirect' => '/dashboard/orders'
            ]);
        }

        return $this->error('Order not found!', 417);
    }

    public function updateOrders($order_id)
    {
        if (request('shopAsCustomer') == true) {
            $user_id = request('id');
        } else {
            $user_id = auth()->id();
        }

        $order = Order::where('user_id', $user_id)->where('id', $order_id)->first();

        $summary = json_decode(request('summary'), true);

        $data = json_decode($order->trxId);
        if ($data->payment_1st == '') {
            $trxId = [
                'payment_1st' => $summary['trxId'],
                'payment_2nd' => ''
            ];
        } else {
            $trxId = [
                'payment_1st' => $data->payment_1st,
                'payment_2nd' => $summary['trxId']
            ];

            $orderItems = OrderItem::where('order_id', $order->id)->get();

            foreach ($orderItems as $orderItem) {
                if ($orderItem->full_payment == NULL) {
                    $orderItem->update([
                        'full_payment' => $summary['trxId']
                    ]);
                }
            }
        }

        if (!empty($order)) {
            $order->update([
                'trxId' => json_encode($trxId),
            ]);

            return $this->success([
                'status' => 'success',
                'message' => 'Your payment has been updated successfully',
                'redirect' => '/dashboard/orders'
            ]);
        }

        return $this->error('Order not found!', 417);
    }

    public function updateOrderItems($item_id)
    {
        if (request('shopAsCustomer') == true) {
            $user_id = request('id');
        } else {
            $user_id = auth()->id();
        }

        $orderItem = OrderItem::where('user_id', $user_id)->where('id', $item_id)->first();

        $summary = json_decode(request('summary'), true);

        if (!empty($orderItem)) {
            $orderItem->update([
                'full_payment' => $summary['trxId'],
            ]);

            return $this->success([
                'status' => 'success',
                'message' => 'Your payment has been updated successfully',
                'redirect' => '/details/' . $orderItem->order_id
            ]);
        }

        return $this->error('Order item not found!', 417);
    }

    public function refundOrders($id)
    {
        if (request('shopAsCustomer') == true) {
            $user_id = request('id');
        } else {
            $user_id = auth()->id();
        }

        $order = Order::where('user_id', $user_id)->where('id', $id)->first();

        if (!empty($order)) {
            $order->update([
                'status' => 'requested-refund'
            ]);

            return $this->success([
                'status' => 'success',
                'message' => 'Refund on your order has been requested successfully',
                'redirect' => '/dashboard/orders'
            ]);
        }

        return $this->error('Order not found!', 417);
    }

    public function orderStore($tran_id, $pay_method, $summary, $address, $status, $percent, $pwgDiscount, $auth_id)
    {
        //order_number
        $user = User::where('id', $auth_id)->first();
        $couponCode = $summary['couponCode'] ?? null;
        $couponDiscount = $summary['couponDiscount'] ?? null;
        $cartTotal = $summary['cartTotal'] ?? null;
        $advanced = $summary['advanced'] ?? null;
        $dueAmount = $summary['dueAmount'] ?? null;
        $refNumber = $summary['refNumber'] ?? null;

        $discount = [
            'percent' => $pwgDiscount['discountPercent'],
            'amount' => $pwgDiscount['discountAmount'],
            'product_count' => $pwgDiscount['totalProduct'],
        ];
        // $trxId = $summary['trxId'] ?? null;
        $trxId = [
            'payment_1st' => '',
            'payment_2nd' => ''
        ];

        $order = Order::create([
            'name' => $user->name ?? $user->full_name ?? $user->first_name ?? 'No Name',
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'amount' => $cartTotal,
            'coupon_code' => $couponCode,
            'coupon_victory' => $couponDiscount,
            'needToPay' => $advanced - $pwgDiscount['discountAmount'] - $couponDiscount,
            'dueForProducts' => $dueAmount,
            'status' => $status,
            'address' => json_encode($address),
            'transaction_id' => $tran_id,
            'refNumber' => $refNumber,
            'trxId' => json_encode($trxId),
            'currency' => 'BDT',
            'pay_method' => $pay_method,
            'pay_percent' => $percent,
            'pay_discount' => json_encode($discount),
        ]);
        $order_number = generate_order_number($order->id);
        $order->update(['order_number' => $order_number]);

        if ($couponCode) {
            $findCoupon = Coupon::where('coupon_code', $couponCode)->first();
            CouponUser::create([
                'coupon_id' => $findCoupon->id,
                'coupon_code' => $couponCode,
                'coupon_details' => '',
                'win_amount' => $couponDiscount,
                'order_id' => $order->id,
                'user_id' => $user->id,
            ]);
        }

        return $order;
    }


    public function storeOrderItems($order, $productItem, $itemVariations, $OrderItemData, $percent, $pwgDiscount, $auth_id)
    {
        $order_id = $order->id;
        $Id = $productItem['Id'];
        $product = Product::where('ItemId', $Id)->first();

        $product_id = $product->id ?? null;
        $mainImage = $product->MainPictureUrl ?? $productItem['MainPictureUrl'] ?? null;
        $auth_id = $auth_id;
        $OrderItemData['image'] = $mainImage;
        $OrderItemData['order_id'] = $order_id;
        $OrderItemData['product_id'] = $product_id;
        $OrderItemData['user_id'] = $auth_id;

        $orderItem = OrderItem::create($OrderItemData);
        $order_item_id = $orderItem->id;
        $itemTotalQuantity = 0;
        $itemTotalPrice = 0;
        $itemImage = '';
        foreach ($itemVariations as $item) {
            $itemTotalQuantity += $item['Quantity'];
            $itemTotalPrice += $item['Price'] * $item['Quantity'];
            $Attributes = $item['Attributes'] ?? [];
            $itemImage = check_attribute_image($Attributes, $mainImage);
            $variations = [
                'itemCode' => $item['Id'],
                'order_item_id' => $order_item_id,
                'product_id' => $product_id,
                'attributes' => json_encode($Attributes),
                'image' => $itemImage,
                'price' => $item['Price'],
                'quantity' => $item['Quantity'],
                'subTotal' => $item['Price'] * $item['Quantity'],
                'user_id' => $auth_id,
            ];
            OrderItemVariation::create($variations);
        }

        if ($itemTotalQuantity == 0) {
            $itemTotalQuantity = $productItem['Quantity'];
        }

        if ($itemTotalPrice == 0) {
            $itemTotalPrice = $productItem['Price'] * $productItem['Quantity'];
        }

        $order_item_number = generate_order_number($order_item_id);
        $approxWeight = $orderItem->approxWeight ? $itemTotalQuantity * $orderItem->approxWeight : 0;
        $coupon_victory = $order->coupon_victory ? $order->coupon_victory : 0;
        $order_amount = $order->amount;

        if ($orderItem) {
            $order_item_number = generate_order_number($orderItem->id);
            $itemTotal = $itemTotalPrice + (($itemTotalPrice < 3000) ? get_setting('china_local_delivery_charge') : 0);

            // APPLICABLE FOR FIRST PAYMENT ONLY
            $discount = $pwgDiscount['discountAmount'] / $pwgDiscount['totalProduct'];
            $contribution = ($coupon_victory != 0) ? $coupon_victory / $pwgDiscount['totalProduct'] : 0;
            // APPLICABLE FOR FIRST PAYMENT ONLY

            $first_payment = $itemTotal * ($percent / 100);
            $orderItem->update([
                'order_item_number' => $order_item_number,
                'quantity' => $itemTotalQuantity,
                'product_value' => $itemTotal,
                'first_payment' => $first_payment - $discount - $contribution,
                'due_payment' => $itemTotal - $first_payment,
                'approxWeight' => floating($approxWeight, 3),
                'coupon_contribution' => $contribution,
                'chinaLocalDelivery' => ($itemTotalPrice < 3000) ? get_setting('china_local_delivery_charge') : 0,
                'discount' => $discount
            ]);
        } // end condition

    }

    public function confirmOrderPayment()
    {
        $status = request('status');
        $tran_id = request('tran_id');

        if ($status == 'success') {
            $sManager =  new  SManagerPaymentController();
            $feedback = $sManager->success($tran_id);
            return $this->success([], 'Payment status mark as success');
        }

        if ($status == 'failed') {
            $sManager =  new  SManagerPaymentController();
            $feedback = $sManager->fail($tran_id);
            return $this->success([], 'Payment is invalid');
        }

        return $this->error('You have no orders', 417);
    }

    public function orders()
    {
        if (request('shopAsCustomer') == true) {
            $user_id = request('id');
        } else {
            $user_id = auth()->id();
        }

        $orders = Order::with('orderItems.itemVariations')->where('user_id', $user_id)->where('status', '!=', 'cancelled')->latest()->get();
        if (!empty($orders)) {
            return $this->success([
                'orders' => $orders
            ]);
        }
        return $this->error('You have no orders', 417);
    }

    public function orderItems()
    {
        if (request('shopAsCustomer') == true) {
            $user_id = request('id');
        } else {
            $user_id = auth()->id();
        }

        $items = OrderItem::with('itemVariations', 'order')->where('user_id', $user_id)->where('status', '!=', 'waiting-for-payment')->latest()->get();
        if (!empty($items)) {
            return $this->success([
                'items' => $items
            ]);
        }

        return $this->error('You have no order items', 417);
    }

    public function orderDetails($id)
    {
        $params = request('params');

        if ($params['shopAsCustomer'] == true) {
            $user_id = $params['id'];
        } else {
            $user_id = auth()->id();
        }

        $order = Order::with('orderItems.itemVariations')->where('id', $id)->where('user_id', $user_id)->first();

        if ($order) {
            return $this->success([
                'order' => $order
            ]);
        }

        return $this->error('Order not found!', 417);
    }

    public function invoices()
    {
        if (request('shopAsCustomer') == true) {
            $user_id = request('id');
        } else {
            $user_id = auth()->id();
        }

        $invoices = Invoice::with('invoiceItems')->where('user_id', $user_id)->latest()->get();
        if (!empty($invoices)) {
            return $this->success([
                'invoices' => $invoices
            ]);
        }
        return $this->error('You have no orders', 417);
    }

    public function invoiceDetails($id)
    {
        if (request('shopAsCustomer') == true) {
            $user_id = request('id');
        } else {
            $user_id = auth()->id();
        }

        $invoice = Invoice::with('invoiceItems')->where('id', $id)->where('user_id', $user_id)->first();

        if ($invoice) {
            return $this->success([
                'invoice' => $invoice
            ]);
        }

        return $this->error('Invoice not found!', 417);
    }

    public function invoicePay($id)
    {
        if (request('shopAsCustomer') == true) {
            $user_id = request('id');
        } else {
            $user_id = auth()->id();
        }

        $invoice = Invoice::where('id', $id)->where('user_id', $user_id)->first();

        if ($invoice->update([
            'trxId' => request('trxId'),
            'refNumber' => request('refNumber'),
            'payment_method' => request('payMethod'),
        ])) {
            return $this->success([
                'status' => 'success',
                'message' => 'Your payment has been updated successfully',
                'redirect' => '/invoice/' . $invoice->id
            ]);
        }
    }

    public function validateCoupon($code)
    {
        $coupon = Coupon::where('coupon_code', $code)->first();

        if (!empty($coupon)) {
            $redeemed = CouponUser::where('user_id', auth()->id())->where('coupon_code', $code)->first();

            if (!empty($redeemed)) {
                // return $this->error('Coupon has already been redeemed!', 417);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Coupon has already been redeemed!'
                ]);
            }

            return $this->success([
                'coupon' => $coupon
            ]);
        }

        // return $this->error('Not found! Invalid Coupon!', 417);
        return response()->json([
            'status' => 'success',
            'message' => 'Not found! Invalid Coupon!'
        ]);
    }

    public function updateCart()
    {
        $params = request('params');

        if ($params['shopAsCustomer'] == true) {
            $auth_id = $params['id'];
        } else {
            $auth_id = auth()->id();
        }

        $cart = Cart::updateOrCreate(
            ['user_id' => $auth_id],
            ['cart' => $params['cart']]
        );

        if ($cart) {
            return $this->success([
                'status' => 'success',
                'cart' => $cart->cart,
            ]);
        }
    }

    public function getCart()
    {
        if (request('shopAsCustomer') == true) {
            $auth_id = request('id');
        } else {
            $auth_id = auth()->id();
        }

        $cart = Cart::where('user_id', $auth_id)->first();

        if ($cart) {
            return $this->success([
                'status' => 'success',
                'cart' => $cart->cart,
            ]);
        }

        return $this->success([
            'status' => 'error',
            'message' => 'Cart not found!'
        ]);
    }
}
