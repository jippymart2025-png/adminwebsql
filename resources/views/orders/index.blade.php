@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.order_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.order_table')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/order.png') }}"></span>
                            <h3 class="mb-0">{{trans('lang.order_plural')}}</h3>
                            <span class="counter ml-3 order_count"></span>
                        </div>
                        <div class="d-flex top-title-right align-self-center">
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
                                    <select class="form-control zone_selector filteredRecords">
                                        <option value="" selected>{{trans("lang.select_zone")}}</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <select class="form-control status_selector filteredRecords">
                                        <option value="" selected>{{trans("lang.status")}}</option>
                                        <option value="All">{{trans("lang.all_status")}}</option>
                                        <option value="Order Placed">{{trans("lang.order_placed")}}</option>
                                        <option value="Order Accepted">{{trans("lang.order_accepted")}}</option>
                                        <option value="Order Rejected">{{trans("lang.order_rejected")}}</option>
                                        <option value="Driver Pending">{{trans("lang.driver_pending")}}</option>
                                        <option value="Driver Rejected">{{trans("lang.driver_rejected")}}</option>
                                        <option value="Order Shipped">{{trans("lang.order_shipped")}}</option>
                                        <option value="In Transit">{{trans("lang.in_transit")}}</option>
                                        <option value="Order Completed">{{trans("lang.order_completed")}}</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <select class="form-control order_type_selector filteredRecords">
                                        <option value="" selected>{{trans("lang.order_type")}}</option>
                                        <option value="takeaway">{{trans("lang.order_takeaway")}}</option>
                                        <option value="delivery">{{trans("lang.delivery")}}</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <div id="daterange"><i class="fa fa-calendar"></i>&nbsp;
                                        <span></span>&nbsp; <i class="fa fa-caret-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="menu-tab d-none vendorMenuTab">
                        <ul>
                            <li>
                                <a href="{{route('restaurants.view', $id)}}">{{trans('lang.tab_basic')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.foods', $id)}}">{{trans('lang.tab_foods')}}</a>
                            </li>
                            <li class="active">
                                <a href="{{route('restaurants.orders', $id)}}">{{trans('lang.tab_orders')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.coupons', $id)}}">{{trans('lang.tab_promos')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.payout', $id)}}">{{trans('lang.tab_payouts')}}</a>
                            </li>
                            <li>
                                <a
                                    href="{{route('payoutRequests.restaurants.view', $id)}}">{{trans('lang.tab_payout_request')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.booktable', $id)}}">{{trans('lang.dine_in_future')}}</a>
                            </li>
                            <li id="restaurant_wallet"></li>
                            <li id="subscription_plan"></li>
                        </ul>
                    </div>
                    @if(request()->has('driverId'))
                        <div class="menu-tab d-none driverMenuTab">
                            <ul>
                                <li>
                                    <a
                                        href="{{route('drivers.view', request()->query('driverId'))}}">{{trans('lang.tab_basic')}}</a>
                                </li>
                                <li class="active">
                                    <a
                                        href="{{route('orders')}}?driverId={{request()->query('driverId')}}">{{trans('lang.tab_orders')}}</a>
                                </li>
                                <li>
                                    <a
                                        href="{{route('driver.payout', request()->query('driverId'))}}">{{trans('lang.tab_payouts')}}</a>
                                </li>
                                <li>
                                    <a
                                        href="{{route('payoutRequests.drivers.view', request()->query('driverId'))}}">{{trans('lang.tab_payout_request')}}</a>
                                </li>
                                <li>
                                    <a
                                        href="{{route('users.walletstransaction', request()->query('driverId'))}}">{{trans('lang.wallet_transaction')}}</a>
                                </li>
                            </ul>
                        </div>
                    @endif
                    @if(request()->has('userId'))
                        <div class="menu-tab d-none userMenuTab">
                            <ul>
                                <li>
                                    <a
                                        href="{{ route('users.view', request()->query('userId')) }}">{{ trans('lang.tab_basic') }}</a>
                                </li>
                                <li class="active">
                                    <a
                                        href="{{route('orders', 'userId='.request()->query('userId'))}}">{{trans('lang.tab_orders')}}</a>
                                </li>
                                <li>
                                    <a
                                        href="{{route('users.walletstransaction', request()->query('userId'))}}">{{trans('lang.wallet_transaction')}}</a>
                                </li>
                            </ul>
                        </div>
                    @endif
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{trans('lang.order_table')}}</h3>
                                <p class="mb-0 text-dark-2">{{trans('lang.order_table_text')}}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <!-- <a class="btn-primary btn rounded-full" href="{!! route('users.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.user_create')}}</a> -->
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="orderTable"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <?php if (in_array('orders.delete', json_decode(@session('user_permissions'), true))) { ?>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                    class="col-3 control-label" for="is_active">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                        <i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label>
                                            </th>
                                            <?php } ?>
                                            <th>{{trans('lang.order_id')}}</th>
                                            @if ($id == '')
                                                <th>{{trans('lang.restaurant')}}</th>
                                            @endif
                                            @if (isset($_GET['userId']))
                                                <th class="driverClass">{{trans('lang.driver_plural')}}</th>
                                            @elseif (isset($_GET['driverId']))
                                                <th>{{trans('lang.order_user_id')}}</th>
                                            @else
                                                <th class="driverClass">{{trans('lang.driver_plural')}}</th>
                                                <th>{{trans('lang.order_user_id')}}</th>
                                            @endif
                                            <th>{{trans('lang.date')}}</th>
                                            <th>{{trans('lang.restaurants_payout_amount')}}</th>
                                            <th>{{trans('lang.order_type')}}</th>
                                            <th>{{trans('lang.order_order_status_id')}}</th>
                                            <th>{{trans('lang.actions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_list1">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Driver Assignment Modal -->
<div class="modal fade" id="quickDriverAssignmentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('lang.assign_driver') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="quick_driver_selector">{{ trans('lang.select_driver') }}</label>
                        <select id="quick_driver_selector" class="form-control">
                            <option value="">{{ trans('lang.select_driver') }}</option>
                        </select>
                        <div class="form-text text-muted">
                            {{ trans('lang.manual_driver_assignment_help') }}
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('lang.cancel') }}</button>
                <button type="button" class="btn btn-success" id="quick_assign_driver_btn">
                    <i class="fa fa-user-plus"></i> {{ trans('lang.assign_driver') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    // MySQL-based DataTables - Firebase removed

    var vendor_id='<?php echo $id; ?>';
    var append_list='';
    // Currency settings (loaded from config/session if needed, or use defaults)
    var currentCurrency='<?php echo config("app.currency_symbol", "₹"); ?>';
    var currencyAtRight=<?php echo config("app.currency_symbol_at_right", false) ? "true" : "false"; ?>;
    var decimal_degits=<?php echo config("app.currency_decimal_digits", 2); ?>;
    var user_permissions='<?php echo @session("user_permissions") ?>';
    user_permissions=Object.values(JSON.parse(user_permissions));
    var checkDeletePermission=false;
    if($.inArray('orders.delete',user_permissions)>=0) {
        checkDeletePermission=true;
    }
    // Remove Firebase refs - DataTables will handle server-side
    var getId='<?php echo $id; ?>';
    var userID='<?php echo request()->query('userId', ''); ?>';
    var driverID='<?php echo request()->query('driverId', ''); ?>';
    var orderStatus='<?php echo request()->query('status', ''); ?>';

    // Show menu tabs based on context
    if(userID) {
        $('.userMenuTab').removeClass('d-none');
    } else if(driverID) {
        $('.driverMenuTab').removeClass('d-none');
    } else if(getId!='') {
        $('.vendorMenuTab').removeClass('d-none');
    }

    // Load zones from PHP (passed from controller)
    @if(isset($zones))
        @foreach($zones as $zone)
            $('.zone_selector').append($("<option></option>")
                .attr("value",'{{ $zone->id }}')
                .text('{{ $zone->name }}'));
        @endforeach
        $('.zone_selector').prop('disabled', false);
    @endif
    $('.status_selector').select2({
        placeholder: '{{trans("lang.status")}}',
        minimumResultsForSearch: Infinity,
        allowClear: true
    });
    $('.zone_selector').select2({
        placeholder: '{{trans("lang.select_zone")}}',
        minimumResultsForSearch: Infinity,
        allowClear: true
    });
    $('.order_type_selector').select2({
        placeholder: '{{trans("lang.order_type")}}',
        minimumResultsForSearch: Infinity,
        allowClear: true
    });
    $('select').on("select2:unselecting", function(e) {
        var self = $(this);
        setTimeout(function() {
            self.select2('close');
        }, 0);
    });
    function setDate() {
        $('#daterange span').html('{{trans("lang.select_range")}}');
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
        }, function (start, end) {
            $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $('.filteredRecords').trigger('change');
        });
        $('#daterange').on('apply.daterangepicker', function (ev, picker) {
            $('#daterange span').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
            $('.filteredRecords').trigger('change');
        });
        $('#daterange').on('cancel.daterangepicker', function (ev, picker) {
            $('#daterange span').html('{{trans("lang.select_range")}}');
            $('.filteredRecords').trigger('change');
        });
    }
    setDate();

    // Filter change handler - reload DataTable with new filters
    $('.filteredRecords').change(function() {
        $('#orderTable').DataTable().ajax.reload();
    });
    $(document).ready(function() {
        jQuery('#search').hide();
        $(document.body).on('click','.redirecttopage',function() {
            var url=$(this).attr('href') || $(this).attr('data-url');
            if(url) window.location.href=url;
        });
        jQuery("#data-table_processing").show();
        $(document).on('click', '.dt-button-collection .dt-button', function () {
            $('.dt-button-collection').hide();
            $('.dt-button-background').hide();
        });
        $(document).on('click', function (event) {
            if (!$(event.target).closest('.dt-button-collection, .dt-buttons').length) {
                $('.dt-button-collection').hide();
                $('.dt-button-background').hide();
            }
        });
        var fieldConfig = {
            columns: [
                { key: 'id', header: "{{trans('lang.order_id')}}" },
                { key: 'driverName', header: "{{trans('lang.driver_plural')}}" },
                { key: 'client', header: "{{trans('lang.order_user_id')}}" },
                { key: 'status', header: "{{trans('lang.order_order_status_id')}}" },
                { key: 'orderType', header: "{{trans('lang.order_type')}}" },
                { key: 'amount', header: "{{trans('lang.amount')}}" },
                { key: 'createdAt', header: "{{trans('lang.created_at')}}" },
            ],
            fileName: "{{trans('lang.order_table')}}",
        };

        const table=$('#orderTable').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            columns: null, // Let DataTables auto-detect from table headers
            ajax: {
                url: '{{ route("orders") }}',
                type: 'GET',
                data: function(d) {
                    // Add filter parameters
                    d.vendor_id = getId;
                    d.user_id = userID;
                    d.driver_id = driverID;
                    d.status = $('.status_selector').val();
                    d.zone_id = $('.zone_selector').val();
                    d.order_type = $('.order_type_selector').val();

                    // Date range
                    var daterangepicker = $('#daterange').data('daterangepicker');
                    if ($('#daterange span').html() != '{{trans("lang.select_range")}}' && daterangepicker) {
                        d.date_from = daterangepicker.startDate.format('YYYY-MM-DD');
                        d.date_to = daterangepicker.endDate.format('YYYY-MM-DD');
                    }
                },
                dataSrc: function(json) {
                    $('.order_count').text(json.recordsFiltered || 0);
                    $('#data-table_processing').hide();
                    if (!json.data || !Array.isArray(json.data)) {
                        return [];
                    }
                    return json.data;
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables error:', error, thrown);
                    $('#data-table_processing').hide();
                }
            },
            order: (getId!=''||driverID||userID&&checkDeletePermission)? [[4,'desc']]:(getId!=''||driverID||userID)? ((checkDeletePermission)? [[4,'desc']]:[[3,'desc']]):((checkDeletePermission)? [[5,'desc']]:[[4,'desc']]),
            columnDefs: [
                {
                    targets: (getId!=''||driverID||userID&&checkDeletePermission)? 4:(getId!=''||driverID||userID)? ((checkDeletePermission)? 4:3):((checkDeletePermission)? 5:4),
                    type: 'date',
                    render: function(data) {
                        return data;
                    }
                },
                {orderable: false,targets: (getId!=''||driverID||userID&&checkDeletePermission)? [0,8]:(getId!=''||driverID||userID)? ((checkDeletePermission)? [0,8]:[7]):(checkDeletePermission)? [0,9]:[8]},
            ],
            "language": {
                "zeroRecords": "{{trans("lang.no_record_found")}}",
                "emptyTable": "{{trans("lang.no_record_found")}}",
                "processing": ""
            },
            dom: 'lfrtipB',
            buttons: [
                {
                    extend: 'collection',
                    text: '<i class="mdi mdi-cloud-download"></i> Export as',
                    className: 'btn btn-info',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: 'Export Excel',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'excel',fieldConfig);
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: 'Export PDF',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'pdf',fieldConfig);
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: 'Export CSV',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'csv',fieldConfig);
                            }
                        }
                    ]
                }
            ],
            initComplete: function() {
                $(".dataTables_filter").append($(".dt-buttons").detach());
                $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete','new-password').val('');
                $('.dataTables_filter label').contents().filter(function() {
                    return this.nodeType === 3;
                }).remove();
            }
        });

        function debounce(func,wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout=setTimeout(() => func.apply(this,args),wait);
            };
        }
        $('#search-input').on('input',debounce(function() {
            const searchValue=$(this).val();
            if(searchValue.length>=3) {
                $('#data-table_processing').show();
                table.search(searchValue).draw();
            } else if(searchValue.length===0) {
                $('#data-table_processing').show();
                table.search('').draw();
            }
        },300));
    });

    // Delete handlers - MySQL-based
    $("#is_active").click(function() {
        $("#orderTable .is_open").prop('checked',$(this).prop('checked'));
    });

    {{--// Bulk delete orders--}}
    {{--$("#deleteAll").click(function() {--}}
    {{--    if($('#orderTable .is_open:checked').length) {--}}
    {{--        if(confirm("{{trans('lang.selected_delete_alert')}}")) {--}}
    {{--            var selectedIds = [];--}}
    {{--            $('#orderTable .is_open:checked').each(function() {--}}
    {{--                selectedIds.push($(this).attr('dataId'));--}}
    {{--            });--}}
    {{--            --}}
    {{--            // Send AJAX request to delete orders--}}
    {{--            $.ajax({--}}
    {{--                url: '{{ route("orders.bulk.delete") }}',--}}
    {{--                type: 'POST',--}}
    {{--                data: {--}}
    {{--                    _token: '{{ csrf_token() }}',--}}
    {{--                    ids: selectedIds--}}
    {{--                },--}}
    {{--                success: function(response) {--}}
    {{--                    if(response.success) {--}}
    {{--                        $('#orderTable').DataTable().ajax.reload();--}}
    {{--                    } else {--}}
    {{--                        alert('Error: ' + (response.message || 'Failed to delete orders'));--}}
    {{--                    }--}}
    {{--                },--}}
    {{--                error: function() {--}}
    {{--                    alert('{{trans("lang.error_occurred")}}');--}}
    {{--                }--}}
    {{--            });--}}
    {{--        }--}}
    {{--    } else {--}}
    {{--        alert("{{trans('lang.select_delete_alert')}}");--}}
    {{--    }--}}
    {{--});--}}

    {{--// Single order delete--}}
    {{--$(document).on("click","a[name='order-delete']", function(e) {--}}
    {{--    e.preventDefault();--}}
    {{--    var id = $(this).attr('id');--}}
    {{--    if(confirm("{{trans('lang.confirm_delete')}}")) {--}}
    {{--        $.ajax({--}}
    {{--            url: '{{ route("orders.delete", ":id") }}'.replace(':id', id),--}}
    {{--            type: 'DELETE',--}}
    {{--            data: {--}}
    {{--                _token: '{{ csrf_token() }}'--}}
    {{--            },--}}
    {{--            success: function(response) {--}}
    {{--                if(response.success) {--}}
    {{--                    $('#orderTable').DataTable().ajax.reload();--}}
    {{--                } else {--}}
    {{--                    alert('Error: ' + (response.message || 'Failed to delete order'));--}}
    {{--                }--}}
    {{--            },--}}
    {{--            error: function() {--}}
    {{--                alert('{{trans("lang.error_occurred")}}');--}}
    {{--            }--}}
    {{--        });--}}
    {{--    }--}}
    {{--});--}}
</script>
@endsection
