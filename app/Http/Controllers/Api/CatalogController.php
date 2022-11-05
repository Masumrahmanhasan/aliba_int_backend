<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content\Frontend\Wishlist;
use App\Models\Content\Post;
use App\Models\Content\Product;
use App\Models\Content\SearchLog;
use App\Models\Content\Taxonomy;
use App\Traits\ApiResponser;
use Illuminate\Support\Str;
use Validator;

class CatalogController extends Controller
{
  use ApiResponser;

  public function categories()
  {
    $categories = Taxonomy::whereNotNull('active')
      ->select('name', 'slug', 'description', 'ParentId', 'icon', 'picture', 'otc_id',  'IconImageUrl', 'ApproxWeight', 'is_top')
      ->withCount('children')
      ->get();

    return $this->success([
      'categories' => $categories
    ]);
  }

  public function banners()
  {
    $banners = Post::where('post_type', 'banner')
      ->where('post_status', 'publish')
      ->limit(5)
      ->latest()
      ->select('id', 'post_title', 'post_slug', 'post_content', 'post_excerpt', 'post_thumb', 'thumb_directory', 'thumb_status')
      ->get();

    return $this->success([
      'banners' => $banners
    ]);
  }

  public function categoryProducts($cat_slug)
  {
    $offset = request('offset', 0);
    $limit = request('limit', 36);
    $taxonomy = Taxonomy::where('slug', $cat_slug)->whereNotNull('active')->first();

    if (!$taxonomy) {
      return $this->error('Category not found!', 417);
    }

    if ($taxonomy->ProviderType === 'Taobao') {
      $otc_id = $taxonomy->otc_id;
      $products = get_category_browsing_items($otc_id, 'category',  $offset, $limit);
    } else {
      $keyword = $taxonomy->keyword ? $taxonomy->keyword : $taxonomy->name;
      $products = get_category_browsing_items($keyword, 'text',  $offset, $limit);
    }

    return $this->success([
      'products' => json_encode($products),
    ]);
  }

  public function searchProcess()
  {
    $text = request('search');
    if (!$text) {
      return $this->success([], 'Search text must not empty', 417);
    }
    $search_type = 'text';
    if (request()->hasFile('search')) {
      $search_type = 'picture';
    }
    $log = SearchLog::create([
      'search_id' => Str::random(30),
      'search_type' => $search_type,
      'query_data' => $text,
      'user_id' => auth()->check() ? auth()->id() : null
    ]);

    return $this->success([
      'search_id' => $log->search_id ?? ''
    ]);
  }

  public function getSearchResult($searchKey)
  {
    $offset = request('offset', 0);
    $limit = request('limit', 36);
    $products = get_category_browsing_items($searchKey, 'text',  $offset, $limit);

    return $this->success([
      'products' => json_encode($products)
    ]);
  }

  public function getPictureSearchResult($search_id)
  {
    $offset = request('offset', 0);
    $limit = request('limit', 36);

    $SearchLog = SearchLog::where('search_id', $search_id)->where('search_type', 'picture')->first();
    if ($SearchLog) {
      $products = get_category_browsing_items($SearchLog->query_data, 'picture',  $offset, $limit);

      return $this->success([
        'products' => json_encode($products)
      ]);
    }

    return $this->error('Picture search no more valid', 417);
  }

  public function searchPicture()
  {
    $validator = Validator::make(request()->all(), [
      'picture' => 'required|max:8000|mimes:jpeg,jpg,png,webp,gif',
    ]);
    if ($validator->fails()) {
      return $this->error('Validation fail', 422);
    }
    $search = '';
    if (request()->hasFile('picture')) {
      $file = request()->file('picture');
      $saveDirectory = 'search/' . date('Y-m');
      $search = store_search_picture($file, $saveDirectory, time());
      $search_id = Str::random(30);
      $log = SearchLog::create([
        'search_id' => $search_id,
        'search_type' => 'picture',
        'query_data' => asset($search),
        'user_id' => auth()->check() ? auth()->id() : null,
      ]);

      return $this->success([
        'picture' => asset($search),
        'search_id' => $search_id,
      ]);
    }

    return $this->error('Picture upload fails! Try again', 417);
  }

  public function productDetails($item_id)
  {
    $item = GetItemFullInfoWithDeliveryCosts($item_id);
    if (!empty($item)) {
      $this->storeProductToDatabase($item, $item_id);
      return $this->success([
        'item' => $item
      ]);
    }
    return $this->error('Product not found', 417);
  }


  public function productDescription($item_id)
  {
    $description = getDescription($item_id);
    if (!empty($description)) {
      return $this->success([
        'description' => $description
      ]);
    }
    return $this->error('some error occurred', 417);
  }


  public function productSellerInfo($VendorId)
  {
    $VendorInfo = getSellerInformation($VendorId);
    if (!empty($VendorInfo)) {
      return $this->success([
        'VendorInfo' => $VendorInfo
      ]);
    }
    return $this->error('some error occurred', 417);
  }


  public function storeProductToDatabase($product, $item_id)
  {
    if (is_array($product)) {
      $product_id = key_exists('Id', $product) ? $product['Id'] : 0;
      $PhysicalParameters = key_exists('PhysicalParameters', $product) ? $product['PhysicalParameters'] : [];
      $Price = key_exists('Price', $product) ? $product['Price'] : [];
      $Promotions = key_exists('Promotions', $product) ? $product['Promotions'] : [];
      $Price = checkPromotionalPrice($Promotions, $Price);

      $Pictures = key_exists('Pictures', $product) ? $product['Pictures'] : [];
      $Features = key_exists('Features', $product) ? $product['Features'] : [];
      $VendorId = key_exists('VendorId', $product) ? $product['VendorId'] : '';
      $auth_id = \auth()->check() ? \auth()->id() : null;

      try {
        $test =  Product::updateOrInsert(
          ['ItemId' => $item_id, 'VendorId' => $VendorId],
          [
            'active' => now(),
            'ProviderType' => $product['ProviderType'] ?? '',
            'Title' => $product['Title'] ?? '',
            'CategoryId' => key_exists('CategoryId', $product) ? $product['CategoryId'] : '',
            'ExternalCategoryId' => key_exists('ExternalCategoryId', $product) ? $product['ExternalCategoryId'] : '',
            'VendorName' => key_exists('VendorName', $product) ? $product['VendorName'] : '',
            'VendorScore' => key_exists('VendorScore', $product) ? $product['VendorScore'] : '',
            'PhysicalParameters' => json_encode($PhysicalParameters),
            'BrandId' => $product['BrandId'] ?? '',
            'BrandName' => $product['BrandName'] ?? '',
            'TaobaoItemUrl' => key_exists('TaobaoItemUrl', $product) ? $product['TaobaoItemUrl'] : '',
            'ExternalItemUrl' => key_exists('ExternalItemUrl', $product) ? $product['ExternalItemUrl'] : '',
            'MainPictureUrl' => key_exists('MainPictureUrl', $product) ? $product['MainPictureUrl'] : '',
            'Price' => json_encode($Price ?? []),
            'Pictures' => json_encode($Pictures ?? []),
            'Features' => json_encode($Features ?? []),
            'MasterQuantity' => key_exists('MasterQuantity', $product) ? $product['MasterQuantity'] : '',
            'user_id' => $auth_id,
            'created_at' => now(),
            'updated_at' => now(),
          ]
        );
      } catch (\Throwable $e) {
        return response(['status' => false, 'message' => $e]);
      }
    }
  }
}
