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

    $tran_id = generate_transaction_id();
    $pay_method = request('paymentMethod');

    try {
      DB::transaction(function () use ($tran_id, $pay_method, $cart, $summary, $address) {
        $status = 'waiting-for-payment';
        $order = $this->orderStore($tran_id, $pay_method, $summary, $address, $status);
        foreach ($cart as $product) {
          $itemVariations = $product['ConfiguredItems'];
          $OrderItemData = [
            'name' => $product['Title'],
            'link' => "/product/{$product['Id']}",
            'shipped_by' => 'Air',
            'shipping_rate' => $product['shippingRate'] ?? 800,
            'approxWeight' => $product['ApproxWeight'],
            'chinaLocalDelivery' => $product['DeliveryCost'],
            'status' => $status,
          ];

          $this->storeOrderItems($order, $product, $itemVariations, $OrderItemData);
        }

        $auth_id = auth()->id();
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
    } else if ($pay_method === "nagad_payment" || $pay_method === "bkash_payment") {

      return $this->success([
        'status' => 'success',
        'message' => 'Your order placed successfully',
        'redirect' => '/dashboard/orders'
      ]);
    }

    return $this->error('Your order has not placed', 417);
  }


  public function orderStore($tran_id, $pay_method, $summary, $address, $status)
  {
    //order_number
    $user = auth()->user();
    $couponCode = $summary['couponCode'] ?? null;
    $couponDiscount = $summary['couponDiscount'] ?? null;
    $cartTotal = $summary['cartTotal'] ?? null;
    $advanced = $summary['advanced'] ?? null;
    $dueAmount = $summary['dueAmount'] ?? null;

    $order = Order::create([
      'name' => $user->full_name ?? $user->name ?? $user->first_name ?? 'No Name',
      'user_id' => $user->id,
      'email' => $user->email,
      'phone' => $user->phone ?? '',
      'amount' => $cartTotal,
      'coupon_code' => '',
      'coupon_victory' => '',
      'needToPay' => $advanced,
      'dueForProducts' => $dueAmount,
      'status' => $status,
      'address' => json_encode($address),
      'transaction_id' => $tran_id,
      'currency' => 'BDT',
      'pay_method' => $pay_method,
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


  public function storeOrderItems($order, $productItem, $itemVariations, $OrderItemData)
  {
    $order_id = $order->id;
    $Id = $productItem['Id'];
    $product = Product::where('ItemId', $Id)->first();

    $product_id = $product->id ?? null;
    $mainImage = $product->MainPictureUrl ?? $productItem['MainPictureUrl'] ?? null;
    $auth_id = auth()->id();
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
    $order_item_number = generate_order_number($order_item_id);
    $approxWeight = $orderItem->approxWeight ? $itemTotalQuantity * $orderItem->approxWeight : 0;
    $coupon_victory = $order->coupon_victory ? $order->coupon_victory : 0;
    $order_amount = $order->amount;

    if ($orderItem) {
      $order_item_number = generate_order_number($orderItem->id);
      $itemTotal = $itemTotalPrice + $orderItem->chinaLocalDelivery;
      $contribution = coupon_contribution($order_amount, $itemTotal, $coupon_victory);
      $half_payment = ($itemTotal - $contribution) * 0.50;
      $orderItem->update([
        'order_item_number' => $order_item_number,
        'quantity' => $itemTotalQuantity,
        'product_value' => $itemTotalPrice,
        'first_payment' => $half_payment,
        'due_payment' => $half_payment,
        'approxWeight' => floating($approxWeight, 3),
        'coupon_contribution' => $contribution,
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
    $user_id = auth()->id();
    $orders = Order::with('orderItems')->where('user_id', $user_id)->latest()->get();
    if (!empty($orders)) {
      return $this->success([
        'orders' => $orders
      ]);
    }
    return $this->error('You have no orders', 417);
  }

  public function orderDetails($id)
  {
    $user_id = auth()->id();
    $order = Order::with('orderItems')->where('id', $id)->where('user_id', $user_id)->first();

    if ($order) {
      return $this->success([
        'order' => $order
      ]);
    }

    return $this->error('Order not found!', 417);
  }

  public function invoices()
  {
    $user_id = auth()->id();
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
    $user_id = auth()->id();
    $invoice = Invoice::with('invoiceItems')->where('id', $id)->where('user_id', $user_id)->first();

    if ($invoice) {
      return $this->success([
        'invoice' => $invoice
      ]);
    }

    return $this->error('Invoice not found!', 417);
  }
}
