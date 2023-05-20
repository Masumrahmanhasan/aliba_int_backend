<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class ProductCategoryShippingRate extends Model
{
    public $primaryKey = 'id';

    public $timestamps = true;

    protected $guarded = [];

    protected $fillable = [
        'category',
        'shipping_rate'
    ];
}
