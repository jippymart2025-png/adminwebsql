@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.coupon_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.coupon_table')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/coupon.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.coupon_plural')}}</h3>
                        <span class="counter ml-3 coupon_count"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                        <div class="select-box pl-3">
                            <select class="form-control coupon_type_selector filteredRecords extraordinary-select">
                                <option value="" selected>🎯 {{trans("lang.coupon_type")}}</option>
                                <option value="restaurant">🍽️ {{trans("lang.restaurant")}}</option>
                                <option value="mart">🛒 {{trans("lang.mart")}}</option>
                            </select>
                        </div>
                    <div class="d-flex top-title-right align-self-center">
                        <div class="select-box pl-3">

                        </div>
                    </div>
                </div>
            </div>
        </div>
       </div>
       <div class="table-list">
       <div class="row">
           <div class="col-12">
           <?php if ($id != '') { ?>
                    <div class="menu-tab">
                        <ul>
                            <li>
                                <a href="{{route('restaurants.view', $id)}}">{{trans('lang.tab_basic')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.foods', $id)}}">{{trans('lang.tab_foods')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.orders', $id)}}">{{trans('lang.tab_orders')}}</a>
                            </li>
                            <li class="active">
                                <a href="{{route('restaurants.coupons', $id)}}">{{trans('lang.tab_promos')}}</a>
                            <li>
                                <a href="{{route('restaurants.payout', $id)}}">{{trans('lang.tab_payouts')}}</a>
                            </li>
                            <li>
                                <a href="{{route('payoutRequests.restaurants.view', $id)}}">{{trans('lang.tab_payout_request')}}</a>
                            </li>
                            <li>
                                <a href="{{route('restaurants.booktable', $id)}}">{{trans('lang.dine_in_future')}}</a>
                            </li>
                            <li id="restaurant_wallet"></li>
                            <li id="subscription_plan"></li>
                        </ul>
                    </div>
                <?php } ?>
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.coupon_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.coupons_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                        <div class="card-header-btn mr-3">
                            <?php if ($id != '') { ?>
                                <a class="btn-primary btn rounded-full" href="{!! route('coupons.create') !!}/{{$id}}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.coupon_create')}}</a>
                            <?php } else { ?>
                                <a class="btn-primary btn rounded-full" href="{!! route('coupons.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.coupon_create')}}</a>
                            <?php } ?>
                        </div>
                   </div>
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                         <table id="couponTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <?php if (in_array('coupons.delete', json_decode(@session('user_permissions'), true))) { ?>
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label>
                                    <?php } ?>
                                    </th>
                                    <th>{{trans('lang.coupon_code')}}</th>
                                    <th>{{trans('lang.coupon_discount')}}</th>
                                    <th>Item Value</th>
                                    <th style="display: none;">Usage Limit</th>
                                    <th>{{trans('lang.coupon_privacy')}}</th>
                                    <th>{{trans('lang.coupon_type')}}</th>
                                    <th>{{trans('lang.coupon_restaurant_id')}}</th>
                                    <th>{{trans('lang.coupon_expires_at')}}</th>
                                    <th>{{trans('lang.coupon_enabled')}}</th>
                                    <th>{{trans('lang.coupon_description')}}</th>
                                    <th>{{trans('lang.actions')}}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection
@section('styles')
<style>
/* 🎨 EXTRAORDINARY COUPON TYPE CARDS */
.coupon-type-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2px;
}

.coupon-type-card {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    min-width: 100px;
    backdrop-filter: blur(10px);
}

.coupon-type-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.coupon-type-card:hover::before {
    left: 100%;
}

.type-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    margin-right: 8px;
    position: relative;
    z-index: 2;
}

.type-icon {
    font-size: 14px;
    transition: all 0.3s ease;
}

.type-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    position: relative;
    z-index: 2;
}

.type-label {
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
}

.type-indicator {
    width: 100%;
    height: 2px;
    border-radius: 1px;
    margin-top: 2px;
    transition: all 0.3s ease;
}

/* 🍽️ RESTAURANT CARD - Extraordinary Design */
.restaurant-card {
    background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
    color: white;
    border: 2px solid #ff5252;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
}

.restaurant-card .type-icon-wrapper {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(5px);
}

.restaurant-card .type-icon {
    color: #fff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.restaurant-card .type-indicator {
    background: linear-gradient(90deg, #fff, #ffebee);
    box-shadow: 0 1px 3px rgba(255, 255, 255, 0.3);
}

.restaurant-card:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
    border-color: #ff1744;
}

.restaurant-card:hover .type-icon {
    transform: rotate(15deg) scale(1.1);
    color: #ffebee;
}

/* 🛒 MART CARD - Extraordinary Design */
.mart-card {
    background: linear-gradient(135deg, #4ecdc4, #44a08d);
    color: white;
    border: 2px solid #26a69a;
    box-shadow: 0 4px 15px rgba(78, 205, 196, 0.3);
}

.mart-card .type-icon-wrapper {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(5px);
}

.mart-card .type-icon {
    color: #fff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.mart-card .type-indicator {
    background: linear-gradient(90deg, #fff, #e0f2f1);
    box-shadow: 0 1px 3px rgba(255, 255, 255, 0.3);
}

.mart-card:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(78, 205, 196, 0.4);
    border-color: #00bcd4;
}

.mart-card:hover .type-icon {
    transform: rotate(-15deg) scale(1.1);
    color: #e0f2f1;
}

/* ❓ DEFAULT CARD - Extraordinary Design */
.default-card {
    background: linear-gradient(135deg, #a8a8a8, #757575);
    color: white;
    border: 2px solid #616161;
    box-shadow: 0 4px 15px rgba(168, 168, 168, 0.3);
}

.default-card .type-icon-wrapper {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(5px);
}

.default-card .type-icon {
    color: #fff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.default-card .type-indicator {
    background: linear-gradient(90deg, #fff, #f5f5f5);
    box-shadow: 0 1px 3px rgba(255, 255, 255, 0.3);
}

.default-card:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(168, 168, 168, 0.4);
    border-color: #424242;
}

.default-card:hover .type-icon {
    transform: rotate(360deg) scale(1.1);
    color: #f5f5f5;
}

/* 🌟 PULSE ANIMATION */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.coupon-type-card.pulse {
    animation: pulse 2s infinite;
}

/* 🎭 GLOW EFFECT */
@keyframes glow {
    0% { box-shadow: 0 0 5px currentColor; }
    50% { box-shadow: 0 0 20px currentColor, 0 0 30px currentColor; }
    100% { box-shadow: 0 0 5px currentColor; }
}

.coupon-type-card.glow {
    animation: glow 2s infinite;
}

/* 📱 RESPONSIVE DESIGN */
@media (max-width: 768px) {
    .coupon-type-card {
        min-width: 80px;
        padding: 6px 10px;
    }
    
    .type-icon-wrapper {
        width: 20px;
        height: 20px;
        margin-right: 6px;
    }
    
    .type-icon {
        font-size: 12px;
    }
    
    .type-label {
        font-size: 10px;
    }
}

/* 🎨 DARK MODE SUPPORT */
@media (prefers-color-scheme: dark) {
    .coupon-type-card {
        backdrop-filter: blur(15px);
    }
}

/* ⚡ PERFORMANCE OPTIMIZATIONS */
.coupon-type-card {
    will-change: transform, box-shadow;
    transform: translateZ(0);
    backface-visibility: hidden;
}

/* 🎨 EXTRAORDINARY SELECT DROPDOWN */
.extraordinary-select {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: 2px solid #5a67d8;
    border-radius: 12px;
    padding: 8px 12px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.extraordinary-select:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    border-color: #4c51bf;
}

.extraordinary-select:focus {
    outline: none;
    border-color: #3182ce;
    box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
}

.extraordinary-select option {
    background: white;
    color: #2d3748;
    padding: 8px;
    font-weight: 500;
}

.extraordinary-select option:hover {
    background: #f7fafc;
}

/* 🌟 FILTER ACTIVE STATE */
.extraordinary-select.filter-active {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border-color: #e53e3e;
    animation: pulse 1.5s infinite;
}

/* 🎭 LOADING ANIMATION */
@keyframes shimmer {
    0% { background-position: -200px 0; }
    100% { background-position: calc(200px + 100%) 0; }
}

.coupon-type-card.loading {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200px 100%;
    animation: shimmer 1.5s infinite;
}
</style>
@endsection
@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();
    var getId = '{{$id}}';
    <?php if ($id != '') { ?>
    database.collection('vendors').where("id", "==", '<?php    echo $id; ?>').get().then(async function(snapshots) {
        var vendorData = snapshots.docs[0].data();
        walletRoute = "{{route('users.walletstransaction', ':id')}}";
        walletRoute = walletRoute.replace(":id", vendorData.author);
        $('#restaurant_wallet').append('<a href="' + walletRoute + '">{{trans("lang.wallet_transaction")}}</a>');
        $('#subscription_plan').append('<a href="' + "{{route('vendor.subscriptionPlanHistory', ':id')}}".replace(':id', vendorData.author) + '">' + '{{trans('lang.subscription_history')}}' + '</a>');
    });
    var ref = database.collection('coupons').where('resturant_id', 'in', ['<?php echo $id; ?>', 'ALL']);
    const getStoreName = getStoreNameFunction('<?php echo $id; ?>');
    console.log('🏪 Restaurant-specific coupons query for ID:', '<?php echo $id; ?>');
    <?php } else { ?>
    var ref = database.collection('coupons');
    console.log('🌐 Global coupons query (all coupons)');
    <?php } ?>
    ref = ref.orderBy('expiresAt', 'desc');
    console.log('📊 Firebase query reference set:', ref);
    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;
    var refCurrency = database.collection('currencies').where('isActive', '==', true);
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;
        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });
    var user_permissions = '<?php echo @session("user_permissions")?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('coupons.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
    $(document).ready(function () {
        console.log('🚀 Document ready - initializing coupons DataTable');
        
        // Test Firebase connection
        console.log('🔥 Testing Firebase connection...');
        database.collection('coupons').limit(1).get().then(function(snapshot) {
            console.log('✅ Firebase connection successful - found', snapshot.size, 'coupons');
            if (snapshot.size > 0) {
                console.log('📄 Sample coupon data:', snapshot.docs[0].data());
            }
        }).catch(function(error) {
            console.error('❌ Firebase connection failed:', error);
        });
        
        // Test the actual query that will be used
        console.log('🔍 Testing actual query...');
        ref.limit(5).get().then(function(snapshot) {
            console.log('✅ Actual query successful - found', snapshot.size, 'coupons');
            snapshot.docs.forEach(function(doc, index) {
                console.log('📄 Coupon', index + 1, ':', doc.data().code, 'ID:', doc.id);
            });
        }).catch(function(error) {
            console.error('❌ Actual query failed:', error);
        });
        
        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });
        
        jQuery("#data-table_processing").show();
        console.log('📊 Initializing DataTable for coupons...');
        
        const table = $('#couponTable').DataTable({
            pageLength: 10, // Number of rows per page
            processing: false, // Show processing indicator
            serverSide: true, // Enable server-side processing
            responsive: true,
            ajax: function (data, callback, settings) {
                console.log('🔄 DataTable AJAX request:', data);
                
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;
                const orderableColumns =(checkDeletePermission) ? ['','code', 'discount', 'item_value', 'usageLimit', 'isPublic', 'cType','restaurantName','expiresAt','', 'description',''] : ['code', 'discount', 'item_value', 'usageLimit', 'isPublic', 'cType', 'restaurantName', 'expiresAt', '', 'description', '']; // Ensure this matches the actual column names
                const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                const selectedCouponType = $('.coupon_type_selector').val(); // Get selected coupon type
                
                console.log('🔍 Search value:', searchValue);
                console.log('🎯 Selected coupon type:', selectedCouponType);
                console.log('📊 Order by field:', orderByField, orderDirection);
                
                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }
                ref.get().then(async function (querySnapshot) {
                    console.log('🔍 Firebase query result:', querySnapshot.size, 'documents found');
                    
                    if (querySnapshot.empty) {
                        $('.coupon_count').text(0);
                        console.log("ℹ️ No coupons found in Firestore.");
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: [] // No data
                        });
                        return;
                    }
                    let records = [];
                    let filteredRecords = [];
                    console.log('🔄 Processing', querySnapshot.docs.length, 'coupon documents...');
                    
                    await Promise.all(querySnapshot.docs.map(async (doc) => {
                        let childData = doc.data();
                        childData.id = doc.id; // Ensure the document ID is included in the data
                        console.log('📄 Processing coupon:', childData.code, 'ID:', childData.id);
                        childData.restaurantName = await getrestaurantName(childData.resturant_id);
                        // Apply coupon type filter first
                        var passesTypeFilter = true;
                        if (selectedCouponType && selectedCouponType !== '') {
                            var couponType = childData.cType || 'restaurant'; // Default to restaurant if cType is not set
                            passesTypeFilter = (couponType === selectedCouponType);
                        }

                        if (passesTypeFilter) {
                            if (searchValue) {
                                var date = '';
                                var time = '';
                                if (childData.hasOwnProperty("expiresAt")) {
                                    try {
                                        date = childData.expiresAt.toDate().toDateString();
                                        time = childData.expiresAt.toDate().toLocaleTimeString('en-US');
                                    } catch (err) {
                                    }
                                }
                                var expiresAt = date + ' ' + time;
                                if (
                                    (childData.code && childData.code.toString().toLowerCase().includes(searchValue)) ||
                                    (expiresAt && expiresAt.toString().toLowerCase().indexOf(searchValue) > -1) || (childData.restaurantName && childData.restaurantName.toString().toLowerCase().includes(searchValue)) || (childData.description && childData.description.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.usageLimit && childData.usageLimit.toString().toLowerCase().includes(searchValue))
                                ) {
                                    filteredRecords.push(childData);
                                }
                            } else {
                                filteredRecords.push(childData);
                            }
                        }
                    }));
                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase() : '';
                        let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase() : '';
                        if (orderByField === 'expiresAt') {
                            aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                            bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                        }
                        if (orderByField === 'discount') {
                            aValue = a[orderByField] ? parseInt(a[orderByField] ) : 0;
                            bValue = b[orderByField] ? parseInt(b[orderByField]) : 0;
                        }
                        if (orderByField === 'item_value') {
                            aValue = a[orderByField] ? parseInt(a[orderByField] ) : 0;
                            bValue = b[orderByField] ? parseInt(b[orderByField]) : 0;
                        }
                        if (orderByField === 'usageLimit') {
                            aValue = a[orderByField] ? parseInt(a[orderByField] ) : 0;
                            bValue = b[orderByField] ? parseInt(b[orderByField]) : 0;
                        }
                        if (orderDirection === 'asc') {
                            return (aValue > bValue) ? 1 : -1;
                        } else {
                            return (aValue < bValue) ? 1 : -1;
                        }
                    });
                    const totalRecords = filteredRecords.length;
                    var countText = totalRecords;
                    // if (selectedCouponType && selectedCouponType !== '') {
                    //     countText += ' (' + selectedCouponType + ' only)';
                    // }
                    $('.coupon_count').text(countText);
                    console.log('📊 Total filtered records:', totalRecords);
                    console.log('📄 Pagination: start=', start, 'length=', length);
                    
                    const paginatedRecords = filteredRecords.slice(start, start + length);
                    console.log('📄 Paginated records to display:', paginatedRecords.length);
                    
                    paginatedRecords.forEach(function (childData) {
                        var route1 = '{{route("coupons.edit", ":id")}}';
                        route1 = route1.replace(':id', childData.id);
                        <?php if ($id != '') { ?>
                        route1 = route1 + '?eid={{$id}}';
                        <?php } ?>
                        var route_view = '{{route("restaurants.view", ":id")}}';
                        route_view = route_view.replace(':id', childData.resturant_id);
                        var date = '';
                        var time = '';
                        if (childData.hasOwnProperty("expiresAt")) {
                            try {
                                date = childData.expiresAt.toDate().toDateString();
                                time = childData.expiresAt.toDate().toLocaleTimeString('en-US');
                            } catch (err) {
                            }
                        }
                        if (currencyAtRight) {
                            if (childData.discountType == 'Percent' || childData.discountType == 'Percentage') {
                                discount_price = childData.discount + "%";
                            } else {
                                discount_price = parseFloat(childData.discount).toFixed(decimal_degits) + "" + currentCurrency;
                            }
                        } else {
                            if (childData.discountType == 'Percent' || childData.discountType == 'Percentage') {
                                discount_price = childData.discount + "%";
                            } else {
                                discount_price = currentCurrency + "" + parseFloat(childData.discount).toFixed(decimal_degits);
                            }
                        }
                        const expireDate = new Date(childData.expiresAt.toDate());
                        const currentDate = new Date();
                        const isExpired = expireDate < currentDate;
                        records.push([
                            checkDeletePermission ? '<td class="delete-all"><input type="checkbox" id="is_open_' + childData.id + '" class="is_open" dataId="' + childData.id + '"><label class="col-3 control-label"\n' + 'for="is_open_' + childData.id + '" ></label></td>' : '',
                            '<a href="' + route1 + '"  class="redirecttopage">' + childData.code + '</a>',
                            discount_price,
                            (childData.item_value ? '<span class="item-value-td">' + childData.item_value + '</span>' : '<span class="text-muted">-</span>'),
                            '<span style="display: none;">' + (childData.usageLimit || 0) + '</span>', // Hidden usage limit column
                            childData.hasOwnProperty('isPublic') && childData.isPublic ? '<td class="success"><span class="badge badge-success py-2 px-3">{{trans("lang.public")}}</sapn></td>' : '<td class="danger"><span class="badge badge-danger py-2 px-3">{{trans("lang.private")}}</sapn></td>',
                            childData.cType || 'Unknown',
                            '<td  data-url="' + route_view + '" class="redirecttopage storeName_' + childData.resturant_id + '" >' + childData.restaurantName + '</td>',
                            '<td class="dt-time">' + date + ' ' + time + '</td>',
                            (() => {
                                return childData.isEnabled
                                    ? '<label class="switch"><input type="checkbox" ' + (isExpired ? 'disabled' : 'checked') + ' id="' + childData.id + '" name="isActive"><span class="slider round"></span></label>'
                                    : '<label class="switch"><input type="checkbox" ' + (isExpired ? 'disabled' : '') + ' id="' + childData.id + '" name="isActive"><span class="slider round"></span></label>';
                            })(),
                            childData.description,
                            '<span class="action-btn"><a href="' + route1 + '"><i class="mdi mdi-lead-pencil" title="Edit"></i></a><?php if (in_array('coupons.delete', json_decode(@session('user_permissions'), true))) { ?> <a id="' + childData.id + '" name="coupon_delete_btn" class="delete-btn" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a></span><?php } ?>'
                        ]);
                    });
                    $('#data-table_processing').hide(); // Hide loader
                    console.log('✅ DataTable callback - records:', records.length, 'total:', totalRecords);
                    
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords, // Total number of records in Firestore
                        recordsFiltered: totalRecords, // Number of records after filtering (if any)
                        data: records // The actual data to display in the table
                    });
                }).catch(function (error) {
                    console.error("❌ Error fetching data from Firestore:", error);
                    console.error("❌ Error details:", error.message, error.code);
                    $('.coupon_count').text('Error');
                    $('#data-table_processing').hide(); // Hide loader
                    
                    // Show user-friendly error message
                    if (error.code === 'permission-denied') {
                        console.error("❌ Permission denied - check Firebase security rules");
                    } else if (error.code === 'unavailable') {
                        console.error("❌ Firebase service unavailable");
                    }
                    
                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: [] // No data due to error
                    });
                });
            },
            order: (checkDeletePermission) ? [6, 'desc'] : [5, 'desc'],
            columnDefs: [
                { targets: (checkDeletePermission) ? [0, 4, 7, 9] : [4, 6, 8], orderable: false }, // Hide usage limit column (index 4)
                { targets: 4, visible: false } // Hide usage limit column completely
            ],
            language: {
                zeroRecords: "{{trans("lang.no_record_found")}}",
                emptyTable: "{{trans("lang.no_record_found")}}"
            },
        });
        table.columns.adjust().draw();
        function debounce(func, wait) {
            let timeout;
            const context = this;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }
        $('#search-input').on('input', debounce(function () {
            const searchValue = $(this).val();
            if (searchValue.length >= 3) {
                $('#data-table_processing').show();
                table.search(searchValue).draw();
            } else if (searchValue.length === 0) {
                $('#data-table_processing').show();
                table.search('').draw();
            }
        }, 300));
        
        // Add event handler for coupon type filtering
        $('.coupon_type_selector').on('change', function() {
            var selectedType = $(this).val();
            console.log('🎯 Coupon type filter changed to:', selectedType);
            
            // Add visual indicator when filter is active
            if (selectedType && selectedType !== '') {
                $(this).addClass('filter-active');
            } else {
                $(this).removeClass('filter-active');
            }
            
            // Trigger table redraw with new filter
            $('#data-table_processing').show();
            table.draw();
        });
        
        // Initialize select2 for coupon type selector
        $('.coupon_type_selector').select2({
            placeholder: '{{trans("lang.coupon_type")}}',
            minimumResultsForSearch: Infinity,
            allowClear: true
        });
        
        // Add error handling for DataTable initialization
        table.on('error.dt', function(e, settings, techNote, message) {
            console.error('❌ DataTable error:', message);
            $('.coupon_count').text('Error');
        });
        
        // Add success callback
        table.on('draw.dt', function() {
            console.log('✅ DataTable draw completed');
        });
    });
    async function getStoreNameFunction(resturant_id) {
        var vendorName = '';
        await database.collection('vendors').where('id', '==', resturant_id).get().then(async function (snapshots) {
            if(!snapshots.empty){
            var vendorData = snapshots.docs[0].data();
            vendorName = vendorData.title;
            $('.restaurantTitle').html('{{trans("lang.coupon_plural")}} - ' + vendorName);
            if (vendorData.dine_in_active == true) {
                $(".dine_in_future").show();
            }
        }
        });
        return vendorName;
    }
    $(document).on("click", "input[name='isActive']", async function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;

        // Get coupon code for logging
        var couponCode = '';
        try {
            var doc = await database.collection('coupons').doc(id).get();
            if (doc.exists) {
                couponCode = doc.data().code || 'Unknown';
            }
        } catch (error) {
            console.error('Error getting coupon code:', error);
        }

        if (ischeck) {
            database.collection('coupons').doc(id).update({'isEnabled': true}).then(async function (result) {
                console.log('✅ Coupon enabled successfully, now logging activity...');

                // Log the enable activity
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for coupon enable...');
                        await logActivity('coupons', 'enabled', 'Enabled coupon: ' + couponCode);
                        console.log('✅ Activity logging completed successfully');
                    } else {
                        console.error('❌ logActivity function is not available');
                    }
                } catch (error) {
                    console.error('❌ Error calling logActivity:', error);
                }
            });
        } else {
            database.collection('coupons').doc(id).update({'isEnabled': false}).then(async function (result) {
                console.log('✅ Coupon disabled successfully, now logging activity...');

                // Log the disable activity
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for coupon disable...');
                        await logActivity('coupons', 'disabled', 'Disabled coupon: ' + couponCode);
                        console.log('✅ Activity logging completed successfully');
                    } else {
                        console.error('❌ logActivity function is not available');
                    }
                } catch (error) {
                    console.error('❌ Error calling logActivity:', error);
                }
            });
        }
    });

    async function getrestaurantName(resturant_id) {
        if (resturant_id === "ALL") {
            return "All Restaurants";
        }
        var title = '';
        if (resturant_id) {
            await database.collection('vendors').where("id", "==", resturant_id).get().then(async function (snapshots) {
                if (snapshots.docs.length > 0) {
                    var data = snapshots.docs[0].data();
                    title = data.title;
                }
            });
        }
        return title;
    }
    $("#is_active").click(function () {
        $("#couponTable .is_open").prop('checked', $(this).prop('checked'));
    });
    $("#deleteAll").click(async function () {
        if ($('#couponTable .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();

                // Get all selected coupon codes for logging
                var selectedCoupons = [];
                for (let i = 0; i < $('#couponTable .is_open:checked').length; i++) {
                    var dataId = $('#couponTable .is_open:checked').eq(i).attr('dataId');
                    try {
                        var doc = await database.collection('coupons').doc(dataId).get();
                        if (doc.exists) {
                            selectedCoupons.push(doc.data().code || 'Unknown');
                        }
                    } catch (error) {
                        console.error('Error getting coupon code:', error);
                    }
                }

                // Delete all selected coupons
                for (let i = 0; i < $('#couponTable .is_open:checked').length; i++) {
                    var dataId = $('#couponTable .is_open:checked').eq(i).attr('dataId');
                    await deleteDocumentWithImage('coupons',dataId,'image');
                }

                // Log the bulk deletion activity
                console.log('✅ Bulk coupon deletion completed, now logging activity...');
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for bulk coupon deletion...');
                        await logActivity('coupons', 'bulk_deleted', 'Bulk deleted coupons: ' + selectedCoupons.join(', '));
                        console.log('✅ Activity logging completed successfully');
                    } else {
                        console.error('❌ logActivity function is not available');
                    }
                } catch (error) {
                    console.error('❌ Error calling logActivity:', error);
                }

                window.location.reload();
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
    $(document).on("click", "a[name='coupon_delete_btn']", async function (e) {
        var id = this.id;

        // Get coupon code before deletion for logging
        var couponCode = '';
        var couponData = null;
        try {
            var doc = await database.collection('coupons').doc(id).get();
            if (doc.exists) {
                couponData = doc.data();
                couponCode = couponData.code || 'Unknown';
            }
        } catch (error) {
            console.error('Error getting coupon code:', error);
        }

        // Check if coupon has active orders before deletion
        if (couponCode && couponData) {
            const hasActiveOrders = await checkCouponActiveOrders(couponCode, couponData.resturant_id);
            
            if (hasActiveOrders) {
                alert(`🛡️ Cannot delete coupon "${couponCode}" because it has active orders. Please wait for orders to complete or cancel them first.`);
                return;
            }
        }

        // Confirm deletion
        if (!confirm(`Are you sure you want to delete coupon "${couponCode}"? This action cannot be undone.`)) {
            return;
        }

        await deleteDocumentWithImage('coupons',id,'image');

        console.log('✅ Coupon deleted successfully, now logging activity...');

        // Log the deletion activity with error handling and await the Promise
        try {
            if (typeof logActivity === 'function') {
                console.log('🔍 Calling logActivity for coupon deletion...');
                await logActivity('coupons', 'deleted', 'Deleted coupon: ' + couponCode);
                console.log('✅ Activity logging completed successfully');
            } else {
                console.error('❌ logActivity function is not available');
            }
        } catch (error) {
            console.error('❌ Error calling logActivity:', error);
        }

        window.location.reload();
    });

    // 🔍 Check Coupon Active Orders Function - Using global function from app.blade.php
    // Note: checkCouponActiveOrders function is already defined in layouts/app.blade.php
</script>
@endsection

