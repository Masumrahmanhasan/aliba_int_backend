<?php

Breadcrumbs::for('admin.product-category-shipping-rate.index', function ($trail) {
    $trail->push('Product Category Shipping Rate', route('admin.product-category-shipping-rate.index'));
});

Breadcrumbs::for('admin.product-category-shipping-rate.create', function ($trail) {
    $trail->push('Crate Product Category Shipping Rate', route('admin.product-category-shipping-rate.create'));
});
