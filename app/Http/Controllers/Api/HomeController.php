<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Content\Frontend\CustomerCart;
use App\Models\Content\Frontend\Wishlist;
use App\Models\Content\OrderItem;
use App\Models\Content\Post;
use App\Models\Content\Product;
use App\Models\Content\Taxonomy;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class HomeController extends Controller
{
  use ApiResponser;

  public function verify(Request $request)
  {
    $userID = $request['id'];
    $user = User::findOrFail($userID);
    $user->email_verified_at = now(); // to enable the “email_verified_at field of that user be a current time stamp by mimicing the must verify email feature
    $user->confirmed = 1; // to enable the “email_verified_at field of that user be a current time stamp by mimicing the must verify email feature
    $user->save();
    return response()->json("Email verified!");
  }


  public function resend(Request $request)
  {
    if ($request->user()->hasVerifiedEmail()) {
      return response()->json("User already have verified email!", 422);
      // return redirect($this->redirectPath());
    }
    $request->user()->sendEmailVerificationNotification();
    return response()->json("The notification has been resubmitted");
    // return back()->with(‘resent’, true);
  }


  public function banners()
  {
    $banners = Post::where('post_type', 'banner')->where('post_status', 'publish')->limit(5)->latest()->get();

    return $this->success([
      'banners' => $banners
    ]);
  }


  public function getSectionProducts($section)
  {
    if ($section) {
      $_query_type = $section . '_query_type';
      $_query_url = $section . '_query_url';
      $_query_limit = $section . '_query_limit';
      $type = get_setting($_query_type);
      $url = get_setting($_query_url);
      $limit = get_setting($_query_limit, 50);
      $products = [];
      if ($type == 'cat_query') {
        $products = sectionGetCategoryProducts($url, $limit);
      } elseif ($type == 'search_query') {
        $products = sectionGetSearchProducts($url, $limit);
      }
      return $this->success([
        'products' => json_encode($products)
      ]);
    }

    return $this->success([
      'products' => json_encode([])
    ]);
  }

  public function lovingProducts()
  {
    $lists = Wishlist::with('product')
      ->latest()
      ->limit(40)
      ->get();
    return $this->success([
      'lovingProducts' => json_encode($lists)
    ]);
  }

  public function buyingProducts()
  {
    $buyingProducts = CustomerCart::withTrashed()->with('product')->select('ItemId')->groupBy('ItemId')->latest()->get();

    return $this->success([
      'buyingProducts' => $buyingProducts
    ]);
  }

  public function recentProducts()
  {
    $products  = Product::whereNotNull('active')
      ->select('ItemId', 'ProviderType', 'Title', 'BrandName', 'MainPictureUrl', 'Price', 'Pictures', 'Features', 'MasterQuantity')
      ->latest()
      ->limit(15)
      ->get();
    return $this->success([
      'recentProducts' => json_encode($products)
    ]);
  }


  public function relatedProducts($item_id)
  {
    $product  = Product::where('ItemId', $item_id)->first();
    $products = [];
    if ($product) {
      $CategoryId = $product->CategoryId;
      $products  = Product::where('CategoryId', $CategoryId)
        ->where('ItemId', '!=', $item_id)
        ->select('ItemId', 'ProviderType', 'Title', 'BrandName', 'MainPictureUrl', 'Price', 'Pictures', 'Features', 'MasterQuantity')
        ->latest()
        ->limit(20)
        ->get();
    }

    if (!empty($products)) {
      $products  = Product::where('ItemId', '!=', $item_id)
        ->select('ItemId', 'ProviderType', 'Title', 'BrandName', 'MainPictureUrl', 'Price', 'Pictures', 'Features', 'MasterQuantity')
        ->latest()
        ->limit(20)
        ->get();
    }


    return $this->success([
      'relatedProducts' => json_encode($products)
    ]);
  }


  public function generalSettings()
  {
    return $this->success([
      'general' => json_encode(general_settings())
    ]);
  }

  public function faqPages()
  {
    $faqs = Post::where('post_status', 'publish')
      ->where('post_type', 'faq')
      ->get();
    return $this->success([
      'faqs' => $faqs
    ]);
  }

  public function contactUs()
  {
    $contact = Post::where('post_status', 'publish')
      ->where('post_type', 'page')
      ->where('id', 1)
      ->first();
    return $this->success([
      'contact' => $contact
    ]);
  }

  public function singlePages($slug)
  {
    $singles = Post::where('post_status', 'publish')
      ->where('post_type', 'page')
      ->where('post_slug', $slug)
      ->first();
    return $this->success([
      'singles' => $singles
    ]);
  }

  public function getProductPageCards($card)
  {
    if ($card) {
        $_content = $card . '_content';
        $_image = $card . '_image';
        $content = get_setting($_content);
        $image = get_setting($_image);

        if ($card != 'card_one') {
            $_delivery = $card . '_delivery';
            $_weight_price = $card . '_weight_price';
            $delivery = get_setting($_delivery);
            $weight_price = get_setting($_weight_price);

            return response()->json([
                'status' => 'Success',
                'data' => [
                    'delivery' => $delivery,
                    'weight_price' => $weight_price,
                    'content' => $content,
                    'image' => $image
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'Success',
                'data' => [
                    'content' => $content,
                    'image' => $image
                ]
            ]);
        }
    }

    return response()->json([
        'status' => 'Failed',
        'data' => null
    ]);
  }

  public function getHomepageCards()
  {
    $data = [];

    if (get_setting('hp_card_one_active') == 'enable') {
        $card = [
            'title' => get_setting('hp_card_one_title'),
            'image' => get_setting('hp_card_one_image'),
            'btn_name' => get_setting('hp_card_one_btn_name'),
            'btn_url' => get_setting('hp_card_one_url')
        ];

        array_push($data, $card);
    }

    if (get_setting('hp_card_two_active') == 'enable') {
        $card = [
            'title' => get_setting('hp_card_two_title'),
            'image' => get_setting('hp_card_two_image'),
            'btn_name' => get_setting('hp_card_two_btn_name'),
            'btn_url' => get_setting('hp_card_two_url')
        ];

        array_push($data, $card);
    }

    if (get_setting('hp_card_three_active') == 'enable') {
        $card = [
            'title' => get_setting('hp_card_three_title'),
            'image' => get_setting('hp_card_three_image'),
            'btn_name' => get_setting('hp_card_three_btn_name'),
            'btn_url' => get_setting('hp_card_three_url')
        ];

        array_push($data, $card);
    }

    if (get_setting('hp_card_four_active') == 'enable') {
        $card = [
            'title' => get_setting('hp_card_four_title'),
            'image' => get_setting('hp_card_four_image'),
            'btn_name' => get_setting('hp_card_four_btn_name'),
            'btn_url' => get_setting('hp_card_four_url')
        ];

        array_push($data, $card);
    }

    return response()->json([
        'status' => 'Success',
        'data' => $data
    ]);
  }

  public function footerBanners()
  {
    $one = get_setting('footer_image_one');
    $two = get_setting('footer_image_two');
    $three = get_setting('footer_image_three');
    $four = get_setting('footer_image_four');
    $five = get_setting('footer_image_five');

    $url_one = get_setting('footer_image_one_url');
    $url_two = get_setting('footer_image_two_url');
    $url_three = get_setting('footer_image_three_url');
    $url_four = get_setting('footer_image_four_url');
    $url_five = get_setting('footer_image_five_url');

    return response()->json([
        'status' => 'Success',
        'data' => [
            'brand_one' => [
              'image' => $one,
              'url' => $url_one
            ],
            'brand_two' => [
              'image' => $two,
              'url' => $url_two
            ],
            'brand_three' => [
              'image' => $three,
              'url' => $url_three
            ],
            'brand_four' => [
              'image' => $four,
              'url' => $url_four
            ],
            'brand_five' => [
              'image' => $five,
              'url' => $url_five
            ],
        ]
    ]);
  }

  public function paymentQrCodes()
  {
    $bkash = get_setting('qr_code_bkash');
    $nagad = get_setting('qr_code_nagad');

    return response()->json([
        'status' => 'Success',
        'data' => [
            'method_one' => [
              'name' => 'bKash',
              'qr_code' => $bkash
            ],
            'method_two' => [
              'name' => 'Nagad',
              'qr_code' => $nagad
            ],
        ]
    ]);
  }
}
