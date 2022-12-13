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

if (!function_exists('getSiteUrl')) {
    function getSiteUrl()
    {
        return get_setting('site_url');
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
            'xmlParameters' => '<SearchItemsParameters><ItemTitle>' . $search . '</ItemTitle><SearchMethod>' . $type . '</SearchMethod></SearchItemsParameters>',
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
                    getArrayKeyData($content, 'OtapiItemDescription', []),
                    'ItemDescription',
                    []
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

if (!function_exists('products_from_same_vendor')) {
    function products_from_same_vendor($vendorId, $offset = 1, $limit = 24)
    {
        $query = [
            'instanceKey' => setOtcParams(),
            'language' => 'en',
            'xmlParameters' => '<SearchItemsParameters><VendorId>' . $vendorId . '</VendorId></SearchItemsParameters>',
            'framePosition' => $offset,
            'frameSize' => $limit,
            'blockList' => '',
        ];

        $client = new Client();
        $response = $client->request('GET', load_otc_api() . 'BatchSearchItemsFrame', ['query' => $query]);

        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            $body = json_decode($response->getBody(), true);
            $result = getArrayKeyData($body, 'Result', []);
            $items = getArrayKeyData($result, 'Items', []);
            $items = getArrayKeyData($items, 'Items', []);
            $contents = getArrayKeyData($items, 'Content', []);

            $data = [];

            foreach ($contents as $content) {
                $img = getArrayKeyData($content, 'MainPictureUrl', []);
                $name = getArrayKeyData($content, 'Title', []);
                $product_code = getArrayKeyData($content, 'Id', []);
                $stock = getArrayKeyData($content, 'MasterQuantity', []);

                $price = getArrayKeyData($content, 'Price', []);
                $regular_price = getArrayKeyData($price, 'MarginPrice', []);
                $sale_price = $regular_price;

                $rating = "";
                $total_sold = "";
                $featured_values = getArrayKeyData($content, 'FeaturedValues', []);
                foreach ($featured_values as $featured_value) {
                    if ($featured_value['Name'] == 'rating') {
                        $rating = $featured_value['Value'];
                    }

                    if ($featured_value['Name'] == 'TotalSales') {
                        $total_sold = $featured_value['Value'];
                    }
                }

                $content_data = [
                    'img' => $img,
                    'name' => $name,
                    'product_code' => $product_code,
                    'rating' => $rating,
                    'regular_price' => $regular_price,
                    'sale_price' => $sale_price,
                    'stock' => $stock,
                    'total_sold' => $total_sold
                ];
                array_push($data, $content_data);
            }

            $TotalCount = getArrayKeyData($items, 'TotalCount', 0);
            return [
                'TotalCount' => $TotalCount,
                'Content' => $data
            ];
        }

        return [
            'TotalCount' => 0,
            'Content' => []
        ];
    }
}

if (!function_exists('product_bulk_prices')) {
    function product_bulk_prices($itemId)
    {
        $query = [
            'instanceKey' => setOtcParams(),
            'language' => 'en',
            'itemId' => $itemId,
            'xmlRequest' => '',
            'blockList' => '',
        ];

        $client = new Client();
        $response = $client->request('GET', load_otc_api() . 'BatchGetSimplifiedItemConfigurationInfo', ['query' => $query]);

        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            $body = json_decode($response->getBody(), true);
            $result = getArrayKeyData($body, 'Result', []);
            return $result;
        }
    }
}

if (!function_exists('otc_image_search_items')) {
    function otc_image_search_items($search, $offset = 0, $limit = 36)
    {
        // otc_search_items('bag', 'text', 0, 5)
        $query = [
            'instanceKey' => setOtcParams(),
            'language' => 'en',
            'xmlParameters' => '<SearchItemsParameters><ImageUrl>' . getSiteUrl() . '/' . $search . '</ImageUrl></SearchItemsParameters>',
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

            $data = [];

            foreach ($Content as $content) {
                $ItemId = getArrayKeyData($content, 'Id', []);
                $name = getArrayKeyData($content, 'Title', []);

                $price = getArrayKeyData($content, 'Price', []);
                $sale_price = getArrayKeyData($price, 'MarginPrice', []);

                $img = getArrayKeyData($content, 'MainPictureUrl', []);

                $total_sold = "";
                $featured_values = getArrayKeyData($content, 'FeaturedValues', []);
                foreach ($featured_values as $featured_value) {
                    if ($featured_value['Name'] == 'TotalSales') {
                        $total_sold = $featured_value['Value'];
                    }
                }

                $content_data = [
                    'ItemId' => $ItemId,
                    'name' => $name,
                    'img' => $img,
                    'sale_price' => $sale_price,
                    'total_sold' => $total_sold
                ];
                array_push($data, $content_data);
            }

            return [
                'Content' => $data,
                'TotalCount' => $TotalCount
            ];
        }
        return [
            'Content' => [],
            'TotalCount' => 0
        ];
    }
}
