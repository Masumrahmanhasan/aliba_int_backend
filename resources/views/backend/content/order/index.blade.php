@extends('backend.layouts.app')

@section('title', 'Recent Orders')

@section('content')
    @php
        $options = [
            'purchased' => 'Purchased',
            'shipped-from-suppliers' => 'Shipped from Suppliers',
            'received-in-china-warehouse' => 'Received in China Warehouse',
            'shipped-from-china-warehouse' => 'Shipped from China Warehouse',
            'received-in-BD-warehouse' => 'Received in BD Warehouse',
            'on-transit-to-customer' => 'On Transit to Customer',
            'out-of-stock' => 'Out of Stock',
            'adjustment' => 'Adjustment',
            'refunded' => 'Refunded',
            'delivered' => 'Delivered',
            'Waiting for Payment' => 'Waiting for Payment',
            'Partial Paid' => 'Partial Paid',
        ];
    @endphp
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-sm-5">
                    <h4 class="my-1">
                        @lang('Recent Orders')
                        <a href="#" class="ml-3 btn btn-light process_multiple_delete btn-sm" data-table="orders"><i
                                class="fa fa-trash-o"></i> Multiple Delete</a>
                    </h4>
                </div> <!-- col-->
                <div class="col-sm-7 pull-right">
                    @include('backend.content.order.includes.header-buttons')
                </div> <!-- col-->
            </div> <!-- row-->
        </div>
        <div class="card-body">
            @livewire('order-table')
        </div> <!-- card-body-->
    </div> <!-- card-->
    <div class="modal fade" id="changeStatusButton" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.order.store') }}" id="statusChargeForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCenterTitle">Change Status <span class="orderId"></span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="hiddenField">
                            {{-- hidden input field append here --}}
                        </div>

                        <div class="form-group">
                            @php
                                unset($options['Waiting for Payment'], $options['Partial Paid']);
                            @endphp
                            {{ html()->select('status', $options)->class('form-control')->attribute('maxlength', 255)->required() }}
                        </div> <!--  form-group-->

                        <div class="form-group" id="additionInputStatusForm">

                        </div> <!-- additionInputStatusForm -->

                        <div class="form-group form-check">
                            <input type="checkbox" name="notify" value="1" class="form-check-input" id="notify"
                                checked="true">
                            <label class="form-check-label" for="notify">Notify User</label>
                        </div>

                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="submit" class="btn btn-primary" id="statusSubmitBtn">Save changes</button>
                    </div>

                </form>
            </div>
        </div>
    </div> <!-- changeStatusButton -->
@endsection


@push('after-styles')
    @livewireStyles
@endpush

@push('after-scripts')
    @livewireScripts
    <script>
        const popupCenter = ({
            url,
            title,
            w,
            h
        }) => { // Fixes dual-screen position                             Most browsers      Firefox
            const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
            const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;
            const width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document
                .documentElement.clientWidth : screen.width;
            const height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document
                .documentElement.clientHeight : screen.height;

            const systemZoom = width / window.screen.availWidth;
            const left = (width - w) / 2 / systemZoom + dualScreenLeft
            const top = (height - h) / 2 / systemZoom + dualScreenTop
            const newWindow = window.open(url, title,
                `scrollbars=yes, width=${w / systemZoom}, height=${h / systemZoom}, top=${top},left=${left}`)
            if (window.focus) newWindow.focus();
        }

        $(function() {
            $(document).on('click', '.btn_details', function(event) {
                event.preventDefault();
                var href = $(this).attr('href');
                popupCenter({
                    url: href,
                    title: 'Print Order',
                    w: 1080,
                    h: 720
                });
            });
        });

        function remove_space(stringData) {
            return stringData
                .trim() // remove white spaces at the start and end of string
                // .toLowerCase() // string will be lowercase
                .replace(/^-+/g, "") // remove one or more dash at the start of the string
                .replace(/[^\w-]+/g, "-") // convert any on-alphanumeric character to a dash
                .replace(/-+/g, "-") // convert consecutive dashes to singular one
                .replace(/-+$/g, "");
        };


        (function($) {

            let body = $("body");

            function updateColumnValue(itemData) {
                var itemRow = $(document).find('#' + itemData.id);
                if (itemData.order_number) {
                    itemRow.find('.order_number').text(itemData.order_number);
                }
                if (itemData.tracking_number) {
                    itemRow.find('.tracking_number').text(itemData.tracking_number);
                }
                if (itemData.actual_weight) {
                    itemRow.find('.actual_weight').text(itemData.actual_weight);
                }
                if (itemData.quantity) {
                    itemRow.find('.quantity').text(itemData.quantity);
                }
                if (itemData.product_value) {
                    itemRow.find('.product_value').text(itemData.product_value);
                }
                if (itemData.first_payment) {
                    itemRow.find('.first_payment').text(itemData.first_payment);
                }
                if (itemData.shipping_charge) {
                    itemRow.find('.shipping_charge').text(itemData.shipping_charge);
                }
                if (itemData.courier_bill) {
                    itemRow.find('.courier_bill').text(itemData.courier_bill);
                }
                if (itemData.out_of_stock) {
                    itemRow.find('.out_of_stock').text(itemData.out_of_stock);
                }
                if (itemData.missing) {
                    itemRow.find('.missing').text(itemData.missing);
                }
                if (itemData.adjustment) {
                    itemRow.find('.adjustment').text(itemData.adjustment);
                }
                if (itemData.refunded) {
                    itemRow.find('.refunded').text(itemData.refunded);
                }
                if (itemData.last_payment) {
                    itemRow.find('.last_payment').text(itemData.last_payment);
                }
                if (itemData.due_payment) {
                    itemRow.find('.due_payment').text(itemData.due_payment);
                }
                if (itemData.status) {
                    itemRow.find('.status').text(itemData.status);
                    itemRow.find('.checkboxItem').attr('data-status', itemData.status);
                }
            }

            function enable_proceed_button() {
                $('#changeGroupStatusButton').removeAttr('disabled');
                $('#generateInvoiceButton').removeAttr('disabled');
            }

            function disabled_proceed_button() {
                $('#changeGroupStatusButton').attr('disabled', 'disabled');
                $('#generateInvoiceButton').attr('disabled', 'disabled');
            }


            function generate_process_related_data() {
                var invoiceFooter = $('#invoiceFooter');
                var courier_bill = invoiceFooter.find('.courier_bill').text();
                var payment_method = invoiceFooter.find('#payment_method').val();
                var delivery_method = invoiceFooter.find('#delivery_method').val();
                var total_payable = invoiceFooter.find('.total_payable').text();
                var total_due = invoiceFooter.find('.total_due').text();
                var customer_id = invoiceFooter.find('.total_payable').attr('data-user');
                var isNotify = $('#notifyUser').is(':checked') ? 1 : 0;
                var related_data = {};
                related_data.courier_bill = courier_bill;
                related_data.payment_method = payment_method;
                related_data.delivery_method = delivery_method;
                related_data.total_due = total_due;
                related_data.total_payable = total_payable;
                related_data.user_id = customer_id;
                related_data.isNotify = isNotify;
                return related_data;
            }


            body.on('click', 'tbody>tr', function(event) {
                let doubleClick = 2;
                let trippleClick = 3;
                if (event.detail === doubleClick) {
                    event.preventDefault();
                    var changeStatusButton = $('#changeStatusButton');
                    var hiddenField = changeStatusButton.find('.hiddenField');
                    changeStatusButton.modal('show');
                    var statusChargeForm = $('#statusChargeForm')
                    statusChargeForm.find('#status').find('option').removeAttr('selected');
                    statusChargeForm.find('#status').trigger('change');

                    var data_id = $(this).attr('id');

                    var order_id = $(this).attr('id');

                    $.ajax({
                        type: "get",
                        url: "{{ route('admin.ajax.getOrderItems') }}",
                        data: {
                            orderId: order_id
                        },
                        dataType: "json",
                        success: function (response) {
                            console.log(response);
                            $.each(collection, function (indexInArray, valueOfElement) {

                            });
                        }
                    });

                    var hiddenInput = `<input type="text" name="item_id" id="item_id" value="${data_id}">`;
                    hiddenField.html(hiddenInput);
                    var status = $(this).find('.status').text();

                    // console.log('status', status, remove_space(status));

                    changeStatusButton.find('.orderId').text('#' + $(this).find('.order_item_number').text());
                    body.find('option[value=' + remove_space(status) + ']').attr('selected', 'selected');

                    let statusGroup = ["shipped-from-suppliers", "received-in-BD-warehouse", "out-of-stock",
                        "adjustment", "refunded"
                    ];
                    if (statusGroup.includes(status)) {
                        statusChargeForm.find('#status').trigger('change');
                    }
                }

            }).on('change', 'select[name="out_of_stock_type"]', function(event) {
                var item_id = body.find('#item_id').val();
                var value = $(this).val();
                var itemRow = body.find('#' + item_id);
                var out_of_stock = body.find('input[name="out_of_stock"]');
                if (value === 'full') {
                    var dueValue = 2 * Number(itemRow.find('.first_payment').text());
                    out_of_stock.val(dueValue);
                } else {
                    out_of_stock.val('');
                }

            }).on('change', '#status', function(event) {
                event.preventDefault();
                var item_id = body.find('#item_id').val();
                var status = $(this).val();
                var additionStatus = $('#additionInputStatusForm');
                var itemRow = body.find('#' + item_id);
                var inputData = '';

                if (status === 'purchased') {
                    var order_number = itemRow.find('.order_number').text();
                    inputData =
                        `<input type="text" name="order_number" value="${order_number}" placeholder="Order Number" class="form-control" required="true">`;
                } else if (status === 'shipped-from-suppliers') {
                    var tracking_number = itemRow.find('.tracking_number').text();
                    inputData =
                        `<input type="text" name="tracking_number" value="${tracking_number}" placeholder="Tracking Number" class="form-control" required="true">`;
                } else if (status === 'received-in-BD-warehouse') {
                    var actual_weight = itemRow.find('.actual_weight').text();
                    inputData =
                        `<input type="text" name="actual_weight" value="${actual_weight}" placeholder="Actual Weight" class="form-control" required="true">`;
                } else if (status === 'out-of-stock') {
                    var out_of_stock = itemRow.find('.out_of_stock').text();
                    inputData =
                        `<select name="out_of_stock_type" class="form-control mb-3" required="true">
                          <option value="partial">Partial</option>
                          <option value="full">Full</option>
                      </select>
                      <input type="text" name="out_of_stock" value="${out_of_stock}" placeholder="Amount" class="form-control" required="true">`;
                } else if (status === 'adjustment') {
                    var adjustment = itemRow.find('.adjustment').text();
                    inputData =
                        `<input type="text" name="adjustment" value="${adjustment}" placeholder="Adjustment Amount" class="form-control" required="true">`;
                } else if (status === 'refunded') {
                    var refunded = itemRow.find('.refunded').text();
                    inputData =
                        `<input type="text" name="refunded" value="${refunded}" placeholder="Refund Amount" class="form-control" required="true">`;
                }

                additionStatus.html(inputData);

            }).on('submit', '#statusChargeForm', function(event) {
                event.preventDefault();
                var csrf = $('meta[name="csrf-token"]');
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': csrf.attr('content')
                    },
                    beforeSend: function() {
                        // before loading...
                    },
                    success: function(response) {
                        if (response.status) {
                            var orderItem = response.orderItem;
                            if (response.is_array) {
                                orderItem.map((item, key) => {
                                    updateColumnValue(item);
                                });
                            } else {
                                updateColumnValue(orderItem);
                            }
                        }
                        csrf.attr('content', response.csrf);
                    },
                    error: function(xhr) { // if error occured
                        // console.log('error', xhr);
                    },
                    complete: function() {
                        $('#changeStatusButton').modal('hide');
                        body.find('#statusSubmitBtn').removeAttr('disabled');
                    }
                });
            }).on('click', '.findResultButton', function(event) {
                event.preventDefault();
                body.find('#filterWalletForm').submit();

            }).on('submit', '#filterWalletForm', function(event) {
                event.preventDefault();
                var customer = $(this).find('#customer').val();
                var status = $(this).find('#findStatus').val();
                window.location.href = '/admin/order/wallet?status=' + status + '&customer=' + customer;

            }).on('change', '#allSelectCheckbox', function() {
                var tbodyCheckbox = $('tbody').find('input.checkboxItem');
                if ($(this).is(':checked')) {
                    tbodyCheckbox.prop("checked", true);
                    enable_proceed_button();
                } else {
                    tbodyCheckbox.prop("checked", false);
                    disabled_proceed_button();
                }

            }).on('change', 'input.checkboxItem', function() {
                var checked_item = $('input.checkboxItem:checked').length;
                var uncheck_item = $('input.checkboxItem:not(":checked")').length;

                if (uncheck_item == 0) {
                    $('#allSelectCheckbox').prop("checked", true);
                } else {
                    $('#allSelectCheckbox').prop("checked", false);
                }
                if (checked_item > 0) {
                    enable_proceed_button();
                } else {
                    disabled_proceed_button();
                }

            }).on('click', '#changeGroupStatusButton', function() {
                var changeStatusModal = $('#changeStatusButton');
                var hiddenField = changeStatusModal.find('.hiddenField');
                var hiddenInput = '';
                $('input.checkboxItem:checked').each(function(index) {
                    hiddenInput += `<input type="hidden" name="item_id[]" value="${$(this).val()}">`;
                });
                hiddenField.html(hiddenInput);
                changeStatusModal.modal('show');
                $('#statusChargeForm').trigger("reset");

            }).on('click', '#generateInvoiceButton', function() {
                var generateInvoiceModal = $('#generateInvoiceModal');
                var hiddenInput = '';
                var is_generate = true;
                var duePayment = '';
                var serial = 1;
                var userTrack = 0;
                var total_due = 0;
                var total_weight = 0;
                var invoices = [];

                $('input.checkboxItem:checked').each(function(index) {
                    var item_id = $(this).val();
                    var status = $(this).attr('data-status');
                    var user_id = $(this).attr('data-user');

                    var invoice_item = {};
                    if (userTrack === 0) {
                        userTrack = user_id;
                    }
                    if (userTrack !== 0 && userTrack !== user_id) {
                        is_generate = false;
                    }
                    var status_allow = ['received-in-BD-warehouse', 'out-of-stock', 'adjustment',
                        'refunded'
                    ];
                    if (!status_allow.includes(status)) {
                        is_generate = false;
                    }
                    if (is_generate) {
                        var itemRow = $(document).find('#' + item_id);
                        var product_name = itemRow.find('.product_name').text();
                        var product_id = itemRow.find('.product_name').attr('data-product-id');
                        var order_item_number = itemRow.find('.order_item_number').text();
                        var actual_weight = itemRow.find('.actual_weight').text();
                        var due_payment = itemRow.find('.due_payment').text();
                        var due = due_payment;
                        var shipping_charge = itemRow.find('.shipping_charge').text();
                        var shipping_rate = itemRow.find('.text-danger').text();
                        due_payment = Number(due_payment) + Number(shipping_charge);

                        total_due += Number(due_payment);
                        total_weight += Number(actual_weight);
                        duePayment += `<tr>
                                <td class=" align-middle">${serial}</td>
                                <td class=" align-middle">${order_item_number}</td>
                                <td class="text-left align-middle">${product_name}</td>
                                <td class=" align-middle">${status}</td>
                                <td class="text-right align-middle">${Number(due).toFixed(2)}</td>
                                <td class="text-right align-middle">${Number(actual_weight).toFixed(2)}</td>
                                <td class="text-right align-middle">${Number(shipping_rate).toFixed(2)}</td>
                                <td class="text-right align-middle">${Number(shipping_charge).toFixed(2)}</td>
                                <td class="text-right align-middle">${Number(due_payment).toFixed(2)}</td>
                              </tr>`;
                        invoice_item.id = item_id;
                        invoice_item.order_item_number = order_item_number;
                        invoice_item.product_id = product_id;
                        invoice_item.product_name = product_name;
                        invoice_item.actual_weight = actual_weight;
                        invoice_item.due_payment = due_payment;
                        invoice_item.status = status;
                    }
                    serial += 1;
                    invoices.push(invoice_item);
                });

                if (is_generate) {
                    var invoiceFooter = $('#invoiceFooter');
                    invoiceFooter.find('.total_weight').text(Number(total_weight).toFixed(3));
                    invoiceFooter.find('.total_due').text(Number(total_due).toFixed(2));
                    // invoiceFooter.find('.courier_bill').text(Number(0.00).toFixed(2));
                    invoiceFooter.find('.total_payable').text(Number(total_due).toFixed(2));
                    invoiceFooter.find('.total_payable').attr('data-user', userTrack);
                    invoiceFooter.find('.total_payable').attr('data-invoices', encodeURIComponent(JSON
                        .stringify(invoices)));
                    $('#invoiceItem').html(duePayment);
                    generateInvoiceModal.modal('show');
                } else {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Selected Item Not Ready or Not Same User for Generate Invoice',
                    });
                }
                //console.log('invoices', invoices);
                // hiddenField.html(hiddenInput);
                // changeStatusModal.modal('show');
            }).on('click', '.applyCourierBtn', function() {
                var courier_bill = $(this).closest('.input-group').find('.form-control').val();
                var total_due = $('#invoiceFooter').find('.total_due').text();
                var total_payable = Number(courier_bill) + Number(total_due);
                $('#invoiceFooter').find('.courier_bill').text(Number(courier_bill).toFixed(2));
                $('#invoiceFooter').find('.total_payable').text(Number(total_payable).toFixed(2));

                $('.courier_bill_text').show();
                $('.courierSubmitForm').hide();

            }).on('click', '.removeCourierBtn', function() {
                $(this).closest('div').find('.form-control').val('');
                var total_due = $('#invoiceFooter').find('.total_due').text();
                $('#invoiceFooter').find('.courier_bill').text(0.00);
                $('#invoiceFooter').find('.total_payable').text(Number(total_due).toFixed(2));
                $('.courier_bill_text').hide();
                $('.courierSubmitForm').show();

            }).on('click', '#generateSubmitBtn', function() {
                var invoices = $('#invoiceFooter').find('.total_payable').attr('data-invoices');
                if (invoices) {
                    invoices = decodeURIComponent(invoices);
                }
                var related = generate_process_related_data();
                var csrf = $('meta[name="csrf-token"]');
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('data-action'),
                    data: {
                        invoices: invoices,
                        related: JSON.stringify(related)
                    },
                    headers: {
                        'X-CSRF-TOKEN': csrf.attr('content')
                    },
                    beforeSend: function() {
                        // before loading...
                    },
                    success: function(response) {
                        if (response.status) {
                            window.location.href = '/admin/invoice';
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                text: 'Invoice Generate Fail',
                            });
                        }
                    },
                    error: function(xhr) { // if error occurred
                        Swal.fire({
                            icon: 'warning',
                            text: 'Invoice Generate Error',
                        });
                    },
                    complete: function() {
                        $('#generateInvoiceModal').modal('hide');
                    }
                });

            });


            $('.select2').select2();


        })(jQuery);
    </script>
@endpush
