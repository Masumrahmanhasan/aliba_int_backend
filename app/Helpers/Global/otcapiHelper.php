<?php

use GuzzleHttp\Client;

if (!function_exists('load_otc_api')) {
  function load_otc_api()
  {
    return get_setting('mybd_api_url') . '/service-json/';
  }
}

if (!function_exists('setOtcParams')) {
  function setOtcParams()
  {
    return get_setting('mybd_api_token');
  }
}

if (!function_exists('getArrayKeyData')) {
  function getArrayKeyData(array $array, string $key, $default = null)
  {
    if (is_array($array)) {
      return array_key_exists($key, $array) ? $array[$key] : $default;
    }
    return $default;
  }
}

if (!function_exists('GetThreeLevelRootCategoryInfoList')) {
  function GetThreeLevelRootCategoryInfoList()
  {
    $query = [
        'instanceKey' => setOtcParams(),
        'language' => 'en'
    ];

    $client = new Client();
    $response = $client->request('GET', load_otc_api() . 'GetThreeLevelRootCategoryInfoList', ['query' => $query]);

    $statusCode = $response->getStatusCode();
    if ($statusCode == 200) {
      $content = json_decode($response->getBody(), true);
      if (is_array($content)) {
        $CategoryInfoList = array_key_exists('CategoryInfoList', $content) ? $content['CategoryInfoList'] : [];
        if (is_array($CategoryInfoList)) {
          return array_key_exists('Content', $CategoryInfoList) ? $CategoryInfoList['Content'] : [];
        }
      }
    }
    return [];
  }
}

if (!function_exists('otc_category_items')) {
  function otc_category_items($cat_id, $offset = 0, $limit = 50)
  {
    $query = [
        'instanceKey' => setOtcParams(),
        'language' => 'en',
        'categoryId' => $cat_id,
        'framePosition' => $offset,
        'frameSize' => $limit
    ];

    $client = new Client();
    $response = $client->request('GET', load_otc_api() . 'GetCategoryItemInfoListFrame', ['query' => $query]);

    $statusCode = $response->getStatusCode();
    if ($statusCode == 200) {
      $content = json_decode($response->getBody(), true);
      if (is_array($content)) {
        return array_key_exists('OtapiItemInfoSubList', $content) ? $content['OtapiItemInfoSubList'] : [];
      }
    }
    return ['Content' => [], 'TotalCount' => 0];
  }
}

if (!function_exists('otc_search_items')) {
  function otc_search_items($search, $type, $offset = 1, $limit = 24)
  {
    // otc_search_items('bag', 'text', 0, 5)
    parse_str(parse_url($search, PHP_URL_QUERY), $search_array);
    $data_id = key_exists('id', $search_array) ? $search_array['id'] : null;
    $search = $data_id ? "https://item.taobao.com/item.htm?id={$data_id}" : $search;

    $query = [
        'instanceKey' => setOtcParams(),
        'language' => 'en',
        'xmlParameters' => '<SearchItemsParameters><ItemTitle>' . $search . '</ItemTitle><SearchMethod>' . $type. '</SearchMethod></SearchItemsParameters>',
        'framePosition' => $offset,
        'frameSize' => $limit
    ];

    $client = new Client();
    $response = $client->request('GET', load_otc_api() . 'SearchItemsFrame', ['query' => $query]);

    $statusCode = $response->getStatusCode();
    if ($statusCode == 200) {
      $body = json_decode($response->getBody(), true);
      $result = getArrayKeyData($body, 'Result', []);
      $Items = getArrayKeyData($result, 'Items', []);
      $Content = getArrayKeyData($Items, 'Content', []);
      $TotalCount = getArrayKeyData($Items, 'TotalCount', 0);
      return ['Content' => $Content, 'TotalCount' => $TotalCount];
    }
    return ['Content' => [], 'TotalCount' => 0];
  }
}

if (!function_exists('otc_items_full_info')) {
  function otc_items_full_info($item_id)
  {
    //otc_items_full_info('520672721526')

    $query = [
        'instanceKey' => setOtcParams(),
        'language' => 'en',
        'itemId' => $item_id
    ];

    $client = new Client();
    $response = $client->request('GET', load_otc_api() . 'GetItemFullInfo', ['query' => $query]);

    if ($response->getStatusCode() == 200) {
      $body = json_decode($response->getBody(), true);
      if (is_array($body)) {
        return array_key_exists('OtapiItemFullInfo', $body) ? $body['OtapiItemFullInfo'] : [];
      }
    }
    return [];
  }
}

if (!function_exists('GetItemFullInfoWithDeliveryCosts')) {
  function GetItemFullInfoWithDeliveryCosts($item_id)
  {
    $query = [
        'instanceKey' => setOtcParams(),
        'language' => 'en',
        'itemId' => $item_id
    ];

    $client = new Client();
    $response = $client->request('GET', load_otc_api() . 'GetItemFullInfoWithDeliveryCosts', ['query' => $query]);

    if ($response->getStatusCode() == 200) {
      $body = json_decode($response->getBody(), true);
      if (is_array($body)) {
        return key_exists('OtapiItemFullInfo', $body) ? $body['OtapiItemFullInfo'] : [];
      }
    }
    return [];
  }
}

if (!function_exists('getDescription')) {
  function getDescription($item_id)
  {
    $query = [
        'instanceKey' => setOtcParams(),
        'language' => 'en',
        'itemId' => $item_id
    ];

    $client = new Client();
    $response = $client->request('GET', load_otc_api() . 'GetItemDescription', ['query' => $query]);

    if ($response->getStatusCode() == 200) {
      $content = json_decode($response->getBody(), true);
      if (is_array($content)) {
        return getArrayKeyData(
            getArrayKeyData($content, 'OtapiItemDescription', []), 'ItemDescription', []
        );
      }
    }
    return [];
  }
}

if (!function_exists('getSellerInformation')) {
  function getSellerInformation($VendorId)
  {
    $query = [
        'instanceKey' => setOtcParams(),
        'language' => 'en',
        'vendorId' => $VendorId
    ];

    $client = new Client();
    $response = $client->request('GET', load_otc_api() . 'GetVendorInfo', ['query' => $query]);

    if ($response->getStatusCode() == 200) {
      $content = json_decode($response->getBody(), true);
      if (is_array($content)) {
        return getArrayKeyData($content, 'VendorInfo', []);
      }
    }
    return [];
  }
}
