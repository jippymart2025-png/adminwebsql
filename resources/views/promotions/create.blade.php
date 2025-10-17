@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Create Promotion</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('promotions') !!}">Promotions</a></li>
                    <li class="breadcrumb-item active">Create Promotion</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="cat-edite-page max-width-box">
                <div class="card pb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li role="presentation" class="nav-item">
                                <a href="#promotion_information" aria-controls="description" role="tab" data-toggle="tab"
                                   class="nav-link active">Promotion Information</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="error_top" style="display:none"></div>
                        <div class="row restaurant_payout_create" role="tabpanel">
                            <div class="restaurant_payout_create-inner tab-content">
                                <div role="tabpanel" class="tab-pane active" id="promotion_information">
                                    <fieldset>
                                        <legend>Create Promotion</legend>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Type</label>
                                            <div class="col-7">
                                                <select id="promotion_vtype" class="form-control">
                                                    <option value="">Select Type</option>
                                                    <option value="restaurant">Restaurant</option>
                                                    <option value="mart">Mart</option>
                                                </select>
                                                <div class="form-text text-muted">Choose whether this promotion is for a Restaurant or Mart.</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Zone</label>
                                            <div class="col-7">
                                                <select id="promotion_zone" class="form-control"></select>
                                                <div class="form-text text-muted">Filter vendors by zone.</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Restaurant / Mart</label>
                                            <div class="col-7">
                                                <select id="promotion_restaurant" class="form-control"></select>
                                                <div class="form-text text-muted">Select the restaurant/mart for this promotion.</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Product</label>
                                            <div class="col-7">
                                                <select id="promotion_product" class="form-control"></select>
                                                <div class="form-text text-muted">
                                                    Select the product for this promotion (filtered by restaurant).
                                                    <span id="actual_price_display" class="text-warning" style="display: none;"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Special Price</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="promotion_special_price" min="0" step="0.01">
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Item Limit</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="promotion_item_limit" min="1" value="2">
                                                <div class="form-text text-muted">Maximum number of items that can be ordered with this promotion. Default: 2</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Extra KM Charge</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="promotion_extra_km_charge" min="0" value="7">
                                                <div class="form-text text-muted">Additional charge per kilometer beyond free delivery distance. Default: 7</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Free Delivery KM</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="promotion_free_delivery_km" min="0" value="3">
                                                <div class="form-text text-muted">Distance in kilometers for free delivery. Default: 3</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Start Time</label>
                                            <div class="col-7">
                                                <input type="datetime-local" class="form-control" id="promotion_start_time">
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">End Time</label>
                                            <div class="col-7">
                                                <input type="datetime-local" class="form-control" id="promotion_end_time">
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Payment Mode</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" value="prepaid" id="promotion_payment_mode" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <div class="col-7 offset-3">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="promotion_is_available" checked>
                                                    <label class="form-check-label" for="promotion_is_available">
                                                        Available
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="button" class="btn btn-primary save-promotion-btn"><i class="fa fa-save"></i>
                            Save
                        </button>
                        <a href="{!! route('promotions') !!}" class="btn btn-default"><i class="fa fa-undo"></i>Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
var database = firebase.firestore();
var restaurantSelect = $('#promotion_restaurant');
var productSelect = $('#promotion_product');
var vtypeSelect = $('#promotion_vtype');
var zoneSelect = $('#promotion_zone');
var restaurantList = [];
var productList = [];

function populateZones(selectedId) {
    zoneSelect.empty();
    zoneSelect.append('<option value="">All Zones</option>');
    database.collection('zone').where('publish','==',true).orderBy('name','asc').get().then(function(snapshots) {
        snapshots.forEach(function(doc) {
            var data = doc.data();
            var selected = (selectedId && doc.id === selectedId) ? 'selected' : '';
            zoneSelect.append('<option value="' + doc.id + '" ' + selected + '>' + (data.name || doc.id) + '</option>');
        });
    });
}

function populateRestaurants(selectedVType, selectedZoneId) {
    restaurantSelect.empty();
    restaurantSelect.append('<option value="">Select Restaurant / Mart</option>');
    // Fetch all vendors and filter client-side to avoid composite index issues
    database.collection('vendors').get().then(function(snapshot) {
        restaurantList = [];
        snapshot.forEach(function(doc) {
            var data = doc.data();
            restaurantList.push(data);
            var vendorType = (data.vType || '').toString().toLowerCase();
            var zoneId = data.zoneId || '';
            if ((!
                selectedVType || vendorType === selectedVType
            ) && (
                !selectedZoneId || zoneId === selectedZoneId
            )) {
                // Use Firestore doc id as value; include legacy id if present
                restaurantSelect.append('<option value="' + doc.id + '" data-vtype="' + vendorType + '" data-zoneid="' + zoneId + '" data-legacy-id="' + (data.id || '') + '">' + (data.title || doc.id) + '</option>');
            }
        });
    });
}

function populateProducts(restaurantId) {
    productSelect.empty();
    productSelect.append('<option value="">Select Product</option>');
    $('#actual_price_display').hide();
    if (!restaurantId) return;
    var selectedOption = restaurantSelect.find('option:selected');
    var legacyId = (selectedOption.data('legacy-id') || '').toString();
    var vendorType = (selectedOption.data('vtype') || vtypeSelect.val() || '').toString().toLowerCase();

    var queries = [];
    if (vendorType === 'mart') {
        queries.push(database.collection('mart_items').where('vendorID', '==', restaurantId).get());
        if (legacyId && legacyId !== restaurantId) {
            queries.push(database.collection('mart_items').where('vendorID', '==', legacyId).get());
        }
    } else {
        queries.push(database.collection('vendor_products').where('vendorID', '==', restaurantId).get());
        if (legacyId && legacyId !== restaurantId) {
            queries.push(database.collection('vendor_products').where('vendorID', '==', legacyId).get());
        }
    }

    Promise.all(queries).then(function(results) {
        var seen = {};
        var total = 0;
        results.forEach(function(snapshot) {
            snapshot.forEach(function(doc) {
                if (seen[doc.id]) return;
                seen[doc.id] = true;
                var data = doc.data();
                var title = data.name || data.item_name || data.title || 'Item';
                var displayPrice = data.disPrice && data.disPrice > 0 ? data.disPrice : (data.price || data.item_price || 0);
                productSelect.append('<option value="' + (data.id || doc.id) + '" data-price="' + displayPrice + '">' + title + '</option>');
                total++;
            });
        });
        if (total === 0) {
            productSelect.append('<option value="">No products found</option>');
        }
    });
}

$(document).ready(function () {
    populateZones('');
    populateRestaurants('', '');

    // Input validation for numeric fields
    $('#promotion_special_price, #promotion_item_limit, #promotion_extra_km_charge, #promotion_free_delivery_km').on('input', function() {
        var value = $(this).val();
        // Remove non-numeric characters except decimal point
        value = value.replace(/[^0-9.]/g, '');
        // Ensure only one decimal point
        var parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        $(this).val(value);
    });

    // Type and Zone filters
    vtypeSelect.on('change', function() {
        var vtype = ($(this).val() || '').toString().toLowerCase();
        var zoneId = zoneSelect.val() || '';
        populateRestaurants(vtype, zoneId);
        productSelect.empty();
        productSelect.append('<option value=\"\">Select Product</option>');
        $('#actual_price_display').hide();
    });
    zoneSelect.on('change', function() {
        var vtype = (vtypeSelect.val() || '').toString().toLowerCase();
        var zoneId = ($(this).val() || '').toString();
        populateRestaurants(vtype, zoneId);
        productSelect.empty();
        productSelect.append('<option value=\"\">Select Product</option>');
        $('#actual_price_display').hide();
    });

    restaurantSelect.on('change', function() {
        var restId = $(this).val();
        populateProducts(restId);
    });

    productSelect.on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var price = selectedOption.data('price');
        if (price && price > 0) {
            $('#actual_price_display').show().text('Actual price: ₹' + price);
        } else {
            $('#actual_price_display').hide();
        }
    });
    $('.save-promotion-btn').click(async function () {
        var restaurant_id = restaurantSelect.val();
        var product_id = productSelect.val();
        var special_price = parseFloat($('#promotion_special_price').val()) || 0;
        var item_limit = parseInt($('#promotion_item_limit').val()) || 2;
        var extra_km_charge = parseFloat($('#promotion_extra_km_charge').val()) || 7;
        var free_delivery_km = parseFloat($('#promotion_free_delivery_km').val()) || 3;
        var start_time = $('#promotion_start_time').val();
        var end_time = $('#promotion_end_time').val();
        var payment_mode = 'prepaid';
        var vType = (vtypeSelect.val() || '').toString().toLowerCase();
        var zoneId = zoneSelect.val() || '';
        var isAvailable = $('#promotion_is_available').is(':checked');

        if (!restaurant_id || !product_id || !start_time || !end_time) {
            $('.error_top').show().html('<p>Please fill all required fields.</p>');
            window.scrollTo(0, 0);
            return;
        }

        // Get restaurant and product titles
        var restaurant_title = restaurantSelect.find('option:selected').text();
        var product_title = productSelect.find('option:selected').text();

        // Create formatted document name: restaurantTitle-productTitle
        var documentName = restaurant_title + '-' + product_title;
        // Clean the document name to be Firestore-compatible (remove special characters, spaces, etc.)
        documentName = documentName.replace(/[^a-zA-Z0-9-_]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');

        // Check if end time is expired
        var endDateTime = new Date(end_time);
        var currentDateTime = new Date();
        if (endDateTime < currentDateTime) {
            isAvailable = false; // Force isAvailable to false if expired
        }

        $('.error_top').hide();
        jQuery('#data-table_processing').show();

        // Check if document with this name already exists
        var docRef = database.collection('promotions').doc(documentName);
        var docExists = await docRef.get().then(function(doc) {
            return doc.exists;
        });

        if (docExists) {
            // If document exists, append timestamp to make it unique
            documentName = documentName + '-' + Date.now();
        }

        await database.collection('promotions').doc(documentName).set({
            restaurant_id,
            restaurant_title,
            product_id,
            product_title,
            vType,
            zoneId,
            special_price,
            item_limit,
            extra_km_charge,
            free_delivery_km,
            start_time: new Date(start_time),
            end_time: new Date(end_time),
            payment_mode,
            isAvailable
        }).then(async function () {
            console.log('✅ Promotion saved successfully with document name:', documentName);
            try {
                if (typeof logActivity === 'function') {
                    console.log('🔍 Calling logActivity for promotion creation...');
                    await logActivity('promotions', 'created', 'Created new promotion: ' + restaurant_title + ' - ' + product_title + ' (Special price: ₹' + special_price + ')');
                    console.log('✅ Activity logging completed successfully');
                } else {
                    console.error('❌ logActivity function is not available');
                }
            } catch (error) {
                console.error('❌ Error calling logActivity:', error);
            }
            jQuery('#data-table_processing').hide();
            window.location.href = '{!! route('promotions') !!}';
        }).catch(function (error) {
            jQuery('#data-table_processing').hide();
            $('.error_top').show().html('<p>' + error + '</p>');
            window.scrollTo(0, 0);
        });
    });
});
</script>
@endsection
