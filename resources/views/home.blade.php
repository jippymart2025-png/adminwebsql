@extends('layouts.app')
@section('content')
<div id="main-wrapper" class="page-wrapper" style="min-height: 207px;">
    <div class="container-fluid">
        <div class="card mb-3 business-analytics">
            <div class="card-body">
                <div class="row flex-between align-items-center g-2 mb-3 order_stats_header">
                    <div class="col-sm-6">
                        <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                            {{trans('lang.dashboard_business_analytics')}}</h4>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--8" onclick="location.href='{!! route('payments') !!}'">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 earnings_count" id="earnings_count"></h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_earnings')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/total_earning.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--1" onclick="location.href='{!! route('restaurants') !!}'">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 vendor_count" id="vendor_count"></h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_restaurants')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/restaurant_icon.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--5" onclick="location.href='{!! route('orders') !!}'">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4" id="order_count"></h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_orders')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/active_restaurant.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--24" onclick="location.href='{!! route('foods') !!}'">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4" id="product_count"></h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_products')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/inactive_restaurant.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--14" onclick="location.href='{!! route('payments') !!}'">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4" id="admincommission_count"></h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.admin_commission')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/price.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--6" onclick="location.href='{!! route('users') !!}'">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4" id="users_count"></h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_clients')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/new_restaurant.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--15" onclick="location.href='{!! route('drivers') !!}'">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4" id="driver_count"></h4>
                                    <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_drivers')}}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/total_order.png') }}"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row business-analytics_list">
                    <div class="col-sm-6 col-lg-3">
                        <a class="order-status pending" href="{{ route('orders','status=order-placed') }}">
                            <div class="data">
                                <i class="mdi mdi-lan-pending"></i>
                                <h6 class="status">{{trans('lang.dashboard_order_placed')}}</h6>
                            </div>
                            <span class="count" id="placed_count"></span> </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="order-status confirmed"  href="{!! route('orders','status=order-confirmed') !!}">
                            <div class="data">
                                <i class="mdi mdi-check-circle"></i>
                                <h6 class="status">{{trans('lang.dashboard_order_confirmed')}}</h6>
                            </div>
                            <span class="count" id="confirmed_count"></span> </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="order-status packaging"  href="{!! route('orders','status=order-shipped') !!}">
                            <div class="data">
                                <i class="mdi mdi-clipboard-outline"></i>
                                <h6 class="status">{{trans('lang.dashboard_order_shipped')}}</h6>
                            </div>
                            <span class="count" id="shipped_count"></span> </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="order-status delivered" href="{!! route('orders','status=order-completed') !!}">
                            <div class="data">
                                <i class="mdi mdi-check-circle-outline"></i>
                                <h6 class="status">{{trans('lang.dashboard_order_completed')}}</h6>
                            </div>
                            <span class="count" id="completed_count"></span>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="order-status canceled" href="{!! route('orders','status=order-canceled') !!}">
                            <div class="data">
                                <i class="mdi mdi-window-close"></i>
                                <h6 class="status">{{trans('lang.dashboard_order_canceled')}}</h6>
                            </div>
                            <span class="count" id="canceled_count"></span>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="order-status failed" href="{!! route('orders','status=order-failed') !!}">
                            <div class="data">
                                <i class="mdi mdi-alert-circle-outline"></i>
                                <h6 class="status">{{trans('lang.dashboard_order_failed')}}</h6>
                            </div>
                            <span class="count" id="failed_count"></span>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="order-status failed" href="{!! route('orders','status=order-pending') !!}">
                            <div class="data">
                                <i class="mdi mdi-car-connected"></i>
                                <h6 class="status">{{trans('lang.dashboard_order_pending')}}</h6>
                            </div>
                            <span class="count" id="pending_count"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header no-border">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">{{trans('lang.total_sales')}}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="position-relative">
                            <canvas id="sales-chart" height="200"></canvas>
                        </div>
                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2"> <i class="fa fa-square" style="color:#2EC7D9"></i> {{trans('lang.dashboard_this_year')}} </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header no-border">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">{{trans('lang.service_overview')}}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="flex-row">
                            <canvas id="visitors" height="222"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header no-border">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">{{trans('lang.sales_overview')}}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="flex-row">
                            <canvas id="commissions" height="222"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row daes-sec-sec mb-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header no-border d-flex justify-content-between">
                        <h3 class="card-title">{{trans('lang.restaurant_plural')}}</h3>
                        <div class="card-tools">
                            <a href="{{route('restaurants')}}" class="btn btn-tool btn-sm"><i class="fa fa-bars"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                       <div class="table-responsive px-3">
                        <table class="table table-striped table-valign-middle" id="restaurantTable">
                            <thead>
                            <tr>
                                <th style="text-align:center">{{trans('lang.restaurant_image')}}</th>
                                <th>{{trans('lang.restaurant')}}</th>
                                <th>{{trans('lang.restaurant_review_review')}}</th>
                                <th>{{trans('lang.actions')}}</th>
                            </tr>
                            </thead>
                            <tbody id="append_list">
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header no-border d-flex justify-content-between">
                        <h3 class="card-title">{{trans('lang.top_drivers')}}</h3>
                        <div class="card-tools">
                            <a href="{{route('drivers')}}" class="btn btn-tool btn-sm"><i class="fa fa-bars"></i> </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive px-3">
                        <table class="table table-striped table-valign-middle" id="driverTable">
                            <thead>
                            <tr>
                                <th style="text-align:center">{{trans('lang.restaurant_image')}}</th>
                                <th>{{trans('lang.driver')}}</th>
                                <th>{{trans('lang.order_completed')}}</th>
                                <th>{{trans('lang.actions')}}</th>
                            </tr>
                            </thead>
                            <tbody id="append_list_top_drivers">
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row daes-sec-sec">
        <div class="col-lg-6">
                <div class="card">
                    <div class="card-header no-border d-flex justify-content-between">
                        <h3 class="card-title">{{trans('lang.recent_orders')}}</h3>
                        <div class="card-tools">
                            <a href="{{route('orders')}}" class="btn btn-tool btn-sm"><i class="fa fa-bars"></i> </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                       <div class="table-responsive px-3">
                        <table class="table table-striped table-valign-middle" id="orderTable">
                            <thead>
                            <tr>
                                <th style="text-align:center">{{trans('lang.order_id')}}</th>
                                <th>{{trans('lang.restaurant')}}</th>
                                <th>{{trans('lang.total_amount')}}</th>
                                <th>{{trans('lang.quantity')}}</th>
                            </tr>
                            </thead>
                            <tbody id="append_list_recent_order">
                            </tbody>
                        </table>
                      </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header no-border d-flex justify-content-between">
                        <h3 class="card-title">{{trans('lang.recent_payouts')}}</h3>
                        <div class="card-tools">
                            <a href="{{route('payoutRequests.restaurants')}}" class="btn btn-tool btn-sm"><i class="fa fa-bars"></i> </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive px-3">
                        <table class="table table-striped table-valign-middle" id="recentPayoutsTable">
                            <thead>
                            <tr>
                                <th>{{ trans('lang.restaurant')}}</th>
                                <th>{{trans('lang.paid_amount')}}</th>
                                <th>{{trans('lang.date')}}</th>
                                <th>{{trans('lang.restaurants_payout_note')}}</th>
                            </tr>
                            </thead>
                            <tbody id="append_list_recent_payouts">
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Right sidebar -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- End footer -->
    <!-- ============================================================== -->
</div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {
            $("#data-table_processing").show();

            $.getJSON("{{ url('/dashboard/stats') }}", function (response) {
                if (response.success) {
                    const data = response.data;
                    const currency = "₹";

                    $("#order_count").text(data.orders);
                    $("#product_count").text(data.products);
                    $("#users_count").text(data.users);
                    $("#driver_count").text(data.drivers);
                    $("#vendor_count").text(data.vendors);

                    const earnings = parseFloat(data.earnings || 0).toFixed(2);
                    $("#earnings_count").text(currency + earnings);

                    // Status counts
                    $("#placed_count").text(data.orders_by_status.placed);
                    $("#confirmed_count").text(data.orders_by_status.confirmed);
                    $("#shipped_count").text(data.orders_by_status.shipped);
                    $("#completed_count").text(data.orders_by_status.completed);
                    $("#canceled_count").text(data.orders_by_status.canceled);
                    $("#failed_count").text(data.orders_by_status.failed);
                    $("#pending_count").text(data.orders_by_status.pending);
                } else {
                    console.error("Error:", response.message);
                }
                $("#data-table_processing").hide();
            }).fail(function (jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                $("#data-table_processing").hide();
            });
        });
    </script>
@endsection
