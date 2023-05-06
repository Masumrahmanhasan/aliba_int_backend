<!DOCTYPE html>
@langrtl
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
@else
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@endlangrtl

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', app_name()) - {{ app_name() }}</title>
    <meta name="description" content="@yield('meta_description', 'avanteca.com.bd')">
    <meta name="author" content="@yield('meta_author', 'Avanteca Web Apps Ltd.')">

    <link rel="shortcut icon" type="image/x-icon" href="{{ url(get_setting('favicon')) }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url(get_setting('favicon')) }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ url(get_setting('favicon')) }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url(get_setting('favicon')) }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ url(get_setting('favicon')) }}">
    <link rel="manifest" href="{{ asset('img/brand/site.webmanifest') }}">

    @yield('meta')

    @stack('before-styles')

    {{ style('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700') }}
    {{ style('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css') }}
    {{ style('backend/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}
    {{ style('backend/dist/css/adminlte.min.css') }}

    @stack('middle-styles')
    {{ style(mix('css/backend.css')) }}

    @stack('after-styles')

    @yield('styles')

</head>

<body class="layout-fixed sidebar-mini text-sm sidebar-collapse">
    <!-- Site wrapper -->
    <div class="wrapper">

        @include('backend.includes.header')

        @include('backend.includes.sidebar')


        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    @include('includes.partials.read-only')
                    @include('includes.partials.logged-in-as')
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            {!! Breadcrumbs::render() !!}
                        </div>
                    </div>
                </div> <!-- /.container-fluid -->
            </section>

            <!-- Main content -->
            <section class="content">
                @include('includes.partials.messages-backend')
                @yield('content')
            </section> <!-- /.content -->
        </div> <!-- /.content-wrapper -->

        @include('backend.includes.footer')
        @include('backend.includes.aside')

    </div> <!-- ./wrapper -->


    <!-- Details loading Modal -->
    <div class="modal fade" id="details_loading" tabindex="-1" role="dialog" aria-labelledby="details_loading_title"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="details_loading_title">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="orderUpdate">
                    <div class="modal-body">
                        {{--     details loading here by ajax     --}}
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Scripts -->
    @stack('before-scripts')
    {!! script(mix('js/manifest.js')) !!}
    {!! script(mix('js/vendor.js')) !!}
    {!! script(mix('js/backend.js')) !!}
    {!! script(asset('backend/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')) !!}
    @stack('middle-scripts')
    {!! script(asset('backend/dist/js/adminlte.min.js')) !!}
    {!! script(asset('backend/dist/js/demo.js')) !!}
    @stack('after-scripts')

    <script>
        $('#orderUpdate').submit(function(e) {
            e.preventDefault();

            let inputs = [];
            let shippingRate = null;
            let adjustment = null;
            let status = $('#order_status :selected').val();
            let orderNumber = null;
            let trackingNumber = null;
            let actualWeight = null;
            let refundAmount = null;
            let refundTrxId = null;
            let refundStatement = null;

            let accounts_rmb_price_value = $('#accounts_rmb_price_value').val();
            let accounts_rmb_buying_rate = $('#accounts_rmb_buying_rate').val();
            let accounts_agent_percentage = $('#accounts_agent_percentage').val();
            let accounts_company_shipping_weight = $('#accounts_company_shipping_weight').val();
            let accounts_company_shipping_rate = $('#accounts_company_shipping_rate').val();
            let accounts_profit_loss = $('#accounts_profit_loss').val();

            let data = $('#orderUpdate input');

            $.each(data, function(index, input) {
                if (input.name == 'shipping_rate') {
                    shippingRate = input.value;
                }

                if (input.name == 'adjustment') {
                    adjustment = input.value;
                }

                if (input.name == 'adjustment') {
                    adjustment = input.value;
                }

                if (input.name == 'order_number') {
                    orderNumber = input.value;
                }

                if (input.name == 'tracking_number') {
                    trackingNumber = input.value;
                }

                if (input.name == 'actual_weight') {
                    actualWeight = input.value;
                }

                if (input.name == 'refunded') {
                    refundAmount = input.value;
                }

                if (input.name == 'refund_trxId') {
                    refundTrxId = input.value;
                }

                if (input.name == 'refund_statement') {
                    refundStatement = input.value;
                }
            });

            let order = $('#inputs_for').data('order');

            $.ajax({
                type: "post",
                url: "{{ route('admin.ajax.updateOrderItems') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    order: order,
                    shippingRate: shippingRate,
                    adjustment: adjustment,
                    status: status,
                    orderNumber: orderNumber,
                    trackingNumber: trackingNumber,
                    actualWeight: actualWeight,
                    refundAmount: refundAmount,
                    refundTrxId: refundTrxId,
                    refundStatement: refundStatement,

                    accounts_rmb_price_value: accounts_rmb_price_value,
                    accounts_rmb_buying_rate: accounts_rmb_buying_rate,
                    accounts_agent_percentage: accounts_agent_percentage,
                    accounts_company_shipping_weight: accounts_company_shipping_weight,
                    accounts_company_shipping_rate: accounts_company_shipping_rate,
                    accounts_profit_loss: accounts_profit_loss,
                },
                dataType: "json",
                success: function(response) {
                    Swal.fire(
                        'Success!',
                        'Order updated successfully!',
                        'success'
                    )

                    $('#update_shipping_rate').text(
                        response.orderItem.shipping_charge.toFixed(2)
                    );

                    $('#update_net_due').text(
                        (response.orderItem.due_payment + response.orderItem.shipping_charge +
                            response.orderItem.adjustment).toFixed(2)
                    );

                    $('#update_weight').text(
                        response.orderItem.actual_weight
                    );

                    $('#update_status').text(
                        response.orderItem.status
                    );
                }
            });
        });
    </script>
</body>

</html>
