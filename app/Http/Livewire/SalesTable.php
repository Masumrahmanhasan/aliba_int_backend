<?php

namespace App\Http\Livewire;

use App\Models\Content\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Traits\HtmlComponents;
use Rappasoft\LaravelLivewireTables\Views\Column;

class SalesTable extends TableComponent
{
    use HtmlComponents;
    /**
     * @var string
     */
    public $sortField = 'id';
    public $sortDirection = 'desc';

    public $perPage = 20;
    public $perPageOptions = [10, 20, 50, 100, 150];
    public $loadingIndicator = true;

    protected $options = [
        'bootstrap.classes.table' => 'table table-bordered table-hover',
        'bootstrap.classes.thead' => null,
        'bootstrap.classes.buttons.export' => 'btn btn-info',
        'bootstrap.container' => true,
        'bootstrap.responsive' => true,
    ];

    public $sortDefaultIcon = '<i class="text-muted fa fa-sort"></i>';
    public $ascSortIcon = '<i class="fa fa-sort-up"></i>';
    public $descSortIcon = '<i class="fa fa-sort-down"></i>';

    public $exportFileName = 'Order-table';
    public $exports = [];

    public function query(): Builder
    {
        return OrderItem::whereNotIn('status', ['waiting-for-payment', 'partial-paid', 'full-paid', 'out-of-stock', 'refunded']);
    }

    public function columns(): array
    {
        return [
            Column::make(__('Action'), 'action')
                ->format(function (OrderItem $model) {
                    $htmlHref = '<div class="d-flex"><a href="' . route('admin.order.wallet.details', $model->id) . '" class="btn btn-secondary btn-sm mr-2" data-method="show" data-toggle="tooltip" data-placement="top" title="Order Details"><i class="fa fa-file-o"></i></a>';
                    return $this->html($htmlHref);
                })
                ->excludeFromExport(),
            Column::make('Date', 'created_at')
                ->searchable()
                ->format(function (OrderItem $model) {
                    return date('d-M-Y', strtotime($model->created_at));
                }),
            Column::make('Status', 'status')
                ->searchable(),
            Column::make('Order No.', 'order_item_number')
                ->searchable(),
            Column::make('Product Total Value', 'product_value')
                ->searchable()
                ->format(function (OrderItem $model) {
                    return floating($model->product_value);
                }),
            Column::make('Total Weight', 'actual_weight')
                ->searchable(),
            Column::make('Shipping Rate per KG', 'shipping_rate')
                ->searchable(),
            Column::make('Total Shipping Value', 'shipping_charge')
                ->searchable(),
            Column::make('Product Value in RMB', 'accounts_rmb_price_value')
                ->searchable(),
            Column::make('RMB Rate', 'accounts_rmb_buying_rate')
                ->searchable(),
            Column::make('Buying Agent Percentage', 'accounts_agent_percentage')
                ->searchable(),
            Column::make('Aliba Buying Value', 'aliba_buying_value')
                ->format(function (OrderItem $model) {
                    return (($model->accounts_rmb_price_value * $model->accounts_rmb_buying_rate) + (($model->accounts_rmb_price_value * $model->accounts_rmb_buying_rate) * ($model->accounts_agent_percentage / 100)));
                }),
            Column::make('Aliba Weight', 'accounts_company_shipping_weight')
                ->searchable(),
            Column::make('Aliba CNF per KG', 'accounts_company_shipping_rate')
                ->searchable(),
            Column::make('Aliba Shipping Value', 'aliba_shipping_value')
                ->format(function (OrderItem $model) {
                    return ($model->accounts_company_shipping_weight * $model->accounts_company_shipping_rate);
                }),
            Column::make('Aliba Shipping Profit(+) Loss(-)', 'aliba_shipping_profit_loss')
                ->format(function (OrderItem $model) {
                    return ($model->shipping_charge - ($model->accounts_company_shipping_weight * $model->accounts_company_shipping_rate));
                }),
            Column::make('Aliba Buying + Shipping Profit', 'aliba_buying_shipping_profit')
                ->format(function (OrderItem $model) {
                    return (($model->product_value - $model->accounts_rmb_price_value * $model->accounts_rmb_buying_rate + ($model->accounts_agent_percentage / 100)) + ($model->shipping_charge + ($model->accounts_company_shipping_weight * $model->accounts_company_shipping_rate)));
                }),
            Column::make('Net Profit Margin', 'net_profit_margin')
                ->format(function (OrderItem $model) {
                    return floating($model->product_value / ($model->product_value - $model->accounts_rmb_price_value * $model->accounts_rmb_buying_rate + ($model->accounts_agent_percentage / 100)) + ($model->shipping_charge + ($model->accounts_company_shipping_weight * $model->accounts_company_shipping_rate)));
                }),
        ];
    }

    public function setTableHeadClass($attribute): ?string
    {
        $array = ['created_at', 'status', 'order_number', 'product_value', 'actual_weight', 'shipping_rate', 'shipping_charge', 'accounts_rmb_price_value', 'accounts_rmb_buying_rate', 'accounts_agent_percentage', 'aliba_buying_value', 'accounts_company_shipping_weight', 'accounts_company_shipping_rate', 'aliba_shipping_value', 'aliba_shipping_profit_loss', 'aliba_buying_shipping_profit', 'net_profit_margin', 'action'];
        if (in_array($attribute, $array)) {
            return 'text-center align-middle w-100';
        }
        return $attribute;
    }


    public function setTableDataClass($attribute, $value): ?string
    {
        $array = ['created_at', 'status', 'order_number', 'product_value', 'actual_weight', 'shipping_rate', 'shipping_charge', 'accounts_rmb_price_value', 'accounts_rmb_buying_rate', 'accounts_agent_percentage', 'aliba_buying_value', 'accounts_company_shipping_weight', 'accounts_company_shipping_rate', 'aliba_shipping_value', 'aliba_shipping_profit_loss', 'aliba_buying_shipping_profit', 'net_profit_margin', 'action'];
        if (in_array($attribute, $array)) {
            return 'text-center align-middle';
        }
        return 'align-middle';
    }

    public function setTableRowId($model): ?string
    {
        return $model->id;
    }
}
