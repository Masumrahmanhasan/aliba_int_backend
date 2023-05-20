<?php

namespace App\Http\Controllers\Backend\Content;

use App\Http\Controllers\Controller;
use App\Models\Content\ProductCategoryShippingRate;
use Illuminate\Http\Request;

class ProductCategoryShippingRateController extends Controller
{
    public function index()
    {
        $data['productCategoryShippingRates'] = ProductCategoryShippingRate::all();
        return view('backend.content.productCategoryShippingRate.index', $data);
    }

    public function create()
    {
        return view('backend.content.productCategoryShippingRate.create');
    }

    public function store(Request $request)
    {
        ProductCategoryShippingRate::create([
            'category' => $request->category,
            'shipping_rate' => $request->shipping_rate
        ]);

        return redirect()->route('admin.product-category-shipping-rate.index');
    }

    public function destroy(ProductCategoryShippingRate $productCategoryShippingRate)
    {
        $productCategoryShippingRate->delete();
        return redirect()->route('admin.product-category-shipping-rate.index');
    }
}
