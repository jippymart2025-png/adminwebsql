    @extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Edit Promotion</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('promotions') !!}">Promotions</a></li>
                    <li class="breadcrumb-item active">Edit Promotion</li>
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
                                        <legend>Edit Promotion</legend>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">Restaurant</label>
                                            <div class="col-7">
                                                <select id="promotion_restaurant" class="form-control"></select>
                                                <div class="form-text text-muted">Select the restaurant for this promotion.</div>
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
                                                    <input type="checkbox" class="form-check-input" id="promotion_is_available">
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
                            Update
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
var restaurantList = [];
var productList = [];
var promotionId = '{{ $id ?? '' }}';
console.log('Promotion ID from controller:', '{{ $id ?? "NOT_SET" }}');

function populateRestaurants(selectedId) {
    console.log('Populating restaurants with selected ID:', selectedId);
    restaurantSelect.empty();
    restaurantSelect.append('<option value="">Select Restaurant</option>');
    database.collection('vendors').orderBy('title').get().then(function(snapshot) {
        console.log('Found', snapshot.docs.length, 'restaurants');
        snapshot.forEach(function(doc) {
            var data = doc.data();
            restaurantList.push(data);
            var selected = (selectedId && data.id === selectedId) ? 'selected' : '';
            restaurantSelect.append('<option value="' + data.id + '" ' + selected + '>' + data.title + '</option>');
        });
    }).catch(function(error) {
        console.error('Error loading restaurants:', error);
    });
}

function populateProducts(restaurantId, selectedProductId) {
    console.log('Populating products for restaurant:', restaurantId, 'with selected product:', selectedProductId);
    productSelect.empty();
    productSelect.append('<option value="">Select Product</option>');
    $('#actual_price_display').hide();
    if (!restaurantId) return;
    database.collection('vendor_products').where('vendorID', '==', restaurantId).get().then(function(snapshot) {
        console.log('Found', snapshot.docs.length, 'products for restaurant:', restaurantId);
        snapshot.forEach(function(doc) {
            var data = doc.data();
            var selected = (selectedProductId && data.id === selectedProductId) ? 'selected' : '';
            var displayPrice = data.disPrice && data.disPrice > 0 ? data.disPrice : (data.price || 0);
            productSelect.append('<option value="' + data.id + '" data-price="' + displayPrice + '" ' + selected + '>' + data.name + '</option>');
        });

        // If there's a selected product, show its price
        if (selectedProductId) {
            var selectedOption = productSelect.find('option[value="' + selectedProductId + '"]');
            var price = selectedOption.data('price');
            if (price && price > 0) {
                $('#actual_price_display').show().text('Actual price: ₹' + price);
            }
        }
    }).catch(function(error) {
        console.error('Error loading products:', error);
    });
}

function formatDateTimeForInput(timestamp) {
    if (!timestamp) return '';
    
    let date;
    if (timestamp.seconds) {
        // Firebase Timestamp
        date = new Date(timestamp.seconds * 1000);
    } else if (timestamp.toDate) {
        // Firebase Timestamp with toDate method
        date = timestamp.toDate();
    } else if (timestamp instanceof Date) {
        // Already a Date object
        date = timestamp;
    } else {
        // Try to parse as regular date
        date = new Date(timestamp);
    }
    
    console.log('Original timestamp:', timestamp);
    console.log('Parsed date:', date);
    
    // Format for datetime-local input (YYYY-MM-DDTHH:MM)
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    const formatted = `${year}-${month}-${day}T${hours}:${minutes}`;
    console.log('Formatted for input:', formatted);
    
    return formatted;
}

function loadPromotionData() {
    if (!promotionId) {
        console.log('No promotion ID provided');
        return;
    }
    console.log('Loading promotion data for ID:', promotionId);

    // First, let's check if there are any promotions in the database
    database.collection('promotions').get().then(function(snapshot) {
        console.log('Total promotions in database:', snapshot.docs.length);
        snapshot.docs.forEach(function(doc) {
            console.log('Promotion ID:', doc.id, 'Data:', doc.data());
        });
    });

    database.collection('promotions').doc(promotionId).get().then(function(doc) {
        if (doc.exists) {
            var data = doc.data();
            console.log('Promotion data loaded:', data);
            // Pre-fill fields
            populateRestaurants(data.restaurant_id);
            setTimeout(function() {
                populateProducts(data.restaurant_id, data.product_id);
            }, 400); // Wait for restaurant dropdown to populate
            $('#promotion_special_price').val(data.special_price || 0);
            $('#promotion_item_limit').val(data.item_limit || 2);
            $('#promotion_extra_km_charge').val(data.extra_km_charge || 7);
            $('#promotion_free_delivery_km').val(data.free_delivery_km || 3);
            $('#promotion_is_available').prop('checked', data.isAvailable !== false);
            if (data.start_time) {
                $('#promotion_start_time').val(formatDateTimeForInput(data.start_time));
            }
            if (data.end_time) {
                $('#promotion_end_time').val(formatDateTimeForInput(data.end_time));
            }
        } else {
            console.log('Promotion not found with ID:', promotionId);
        }
    }).catch(function(error) {
        console.error('Error loading promotion data:', error);
    });
}

$(document).ready(function () {
    console.log('Document ready, promotionId:', promotionId);
    
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
    
    if (promotionId) {
        loadPromotionData();
    } else {
        console.log('No promotion ID, just populating restaurants');
        populateRestaurants();
    }
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
        var newDocumentName = restaurant_title + '-' + product_title;
        // Clean the document name to be Firestore-compatible (remove special characters, spaces, etc.)
        newDocumentName = newDocumentName.replace(/[^a-zA-Z0-9-_]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');

        // Check if end time is expired
        var endDateTime = new Date(end_time);
        var currentDateTime = new Date();
        if (endDateTime < currentDateTime) {
            isAvailable = false; // Force isAvailable to false if expired
        }

        $('.error_top').hide();
        jQuery('#data-table_processing').show();
        
        // If document name has changed, we need to create a new document and delete the old one
        if (newDocumentName !== promotionId) {
            // Check if new document name already exists
            var newDocRef = database.collection('promotions').doc(newDocumentName);
            var newDocExists = await newDocRef.get().then(function(doc) {
                return doc.exists;
            });

            if (newDocExists) {
                // If document exists, append timestamp to make it unique
                newDocumentName = newDocumentName + '-' + Date.now();
            }

            // Create new document with updated data
            await database.collection('promotions').doc(newDocumentName).set({
                restaurant_id,
                restaurant_title,
                product_id,
                product_title,
                special_price,
                item_limit,
                extra_km_charge,
                free_delivery_km,
                start_time: new Date(start_time),
                end_time: new Date(end_time),
                payment_mode,
                isAvailable
            }).then(async function () {
                // Delete old document
                await database.collection('promotions').doc(promotionId).delete();
                
                console.log('✅ Promotion updated successfully with new document name:', newDocumentName);
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for promotion update...');
                        await logActivity('promotions', 'updated', 'Updated promotion: ' + restaurant_title + ' - ' + product_title + ' (Special price: ₹' + special_price + ')');
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
        } else {
            // Document name hasn't changed, just update the existing document
            await database.collection('promotions').doc(promotionId).update({
                restaurant_id,
                restaurant_title,
                product_id,
                product_title,
                special_price,
                item_limit,
                extra_km_charge,
                free_delivery_km,
                start_time: new Date(start_time),
                end_time: new Date(end_time),
                payment_mode,
                isAvailable
            }).then(async function () {
                console.log('✅ Promotion updated successfully');
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for promotion update...');
                        await logActivity('promotions', 'updated', 'Updated promotion: ' + restaurant_title + ' - ' + product_title + ' (Special price: ₹' + special_price + ')');
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
        }
    });
});
</script>
@endsection
