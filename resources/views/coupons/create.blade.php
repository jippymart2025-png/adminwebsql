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
                <?php if ($id != '') { ?>
                    <li class="breadcrumb-item"><a href="{{route('restaurants.coupons',$id)}}">{{trans('lang.coupon_plural')}}</a>
                    </li>
                <?php } else { ?>
                    <li class="breadcrumb-item"><a href="{!! route('coupons') !!}">{{trans('lang.coupon_plural')}}</a>
                    </li>
                <?php } ?>
                <li class="breadcrumb-item active">{{trans('lang.coupon_create')}}</li>
            </ol>
        </div>
        <div>
            <div class="card-body">
                <div class="error_top" style="display:none"></div>
                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner">
                        <fieldset>
                            <legend>{{trans('lang.coupon_create')}}</legend>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.coupon_code')}}</label>
                                <div class="col-7">
                                    <input type="text" type="text" class="form-control coupon_code">
                                    <div class="form-text text-muted">{{ trans("lang.coupon_code_help") }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.coupon_discount_type')}}</label>
                                <div class="col-7">x
                                    <select id="coupon_discount_type" class="form-control">
                                        <option value="Fix Price" selected>{{trans('lang.coupon_fixed')}}</option>
                                        <option value="Percentage">{{trans('lang.coupon_percent')}}</option>
                                    </select>
                                    <div class="form-text text-muted">{{ trans("lang.coupon_discount_type_help") }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control coupon_discount">
                                    <div class="form-text text-muted">{{ trans("lang.coupon_discount_help") }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">Item Value</label>
                                <div class="col-7">
                                    <input type="number" class="form-control item_value" min="0">
                                    <div class="form-text text-muted">Minimum order value required to use this coupon (e.g., 299 for FLAT100, 30 for SAVE30)</div>
                                </div>
                            </div>
                            <div class="form-group row width-50" style="display: none;">
                                <label class="col-3 control-label">Usage Limit</label>
                                <div class="col-7">
                                    <input type="number" class="form-control usage_limit" min="0" placeholder="0 for unlimited" value="0">
                                    <div class="form-text text-muted">Maximum number of users who can use this coupon (e.g., 100 for "First-100"). Leave empty or 0 for unlimited usage.</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.coupon_expires_at')}}</label>
                                <div class="col-7">
                                    <div class='input-group date' id='datetimepicker1'>
                                        <input type='text' class="form-control date_picker input-group-addon"/>
                                        <span class=""></span>
                                    </div>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.coupon_expires_at_help") }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.coupon_type')}}</label>
                                <div class="col-7">
                                    <select class="form-control" id="coupon_type">
                                    <option value="" selected>select coupon type</option>
                                        <option value="restaurant">🍽️ {{trans('lang.restaurant')}}</option>
                                        <option value="mart">🛒 {{trans('lang.mart')}}</option>
                                    </select>
                                </div>
                            </div>
                            <?php if ($id == '') { ?>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.coupon_restaurant_id')}}</label>
                                    <div class="col-7">
                                        <select id="vendor_restaurant_select" class="form-control">
                                            <option value="">{{trans('lang.select_restaurant')}}</option>
                                        </select>
                                        <div class="form-text text-muted">
                                            {{ trans("lang.coupon_restaurant_id_help") }}
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                             <div class="form-group row width-50">
                                <div class="form-check">
                                 <input type="checkbox" class="coupon_public" id="coupon_public">
                                     <label class="col-3 control-label" for="coupon_public">{{trans('lang.coupon_public')}}</label>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{trans('lang.coupon_description')}}</label>
                                <div class="col-7">
                                    <textarea rows="12" class="form-control coupon_description"
                                              id="coupon_description"></textarea>
                                    <div class="form-text text-muted">{{ trans("lang.coupon_description_help") }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{trans('lang.category_image')}}</label>
                                <div class="col-7">
                                    <input type="file" onChange="handleFileSelect(event)">
                                    <div class="placeholder_img_thumb coupon_image"></div>
                                    <div id="uploding_image"></div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <div class="form-check">
                                    <input type="checkbox" class="coupon_enabled" id="coupon_enabled">
                                    <label class="col-3 control-label" for="coupon_enabled">{{trans('lang.coupon_enabled')}}</label>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i> {{
                    trans('lang.save')}}
                </button>
                <?php if ($id != '') { ?>
                    <a href="{{route('restaurants.coupons',$id)}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                <?php } else { ?>
                    <a href="{!! route('coupons') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
<script>
var database = firebase.firestore();
var photo_coupon = "";
var fileName="";
var restaurantOwnerId = "";
var restaurantOwnerOnline = false;
    $(document).ready(function () {
        console.log('🚀 Coupon create form - document ready');
        jQuery("#data-table_processing").show();
    // Function to load vendors based on coupon type
    function loadVendorsByType(couponType) {
        console.log('🚀 loadVendorsByType called with:', couponType);
        $('#vendor_restaurant_select').empty();

        // If no coupon type is selected, show only the placeholder
        if (!couponType || couponType === '') {
            console.log('📋 No coupon type selected - showing placeholder only');
            $('#vendor_restaurant_select').append($('<option></option>')
                .attr('value', '')
                .text('{{trans("lang.select_restaurant")}}'));
            return;
        }

        // Add "All [Type]" option when coupon type is selected
        $('#vendor_restaurant_select').append($('<option></option>')
            .attr('value', 'ALL')
            .text('All ' + couponType + 's'));

        var vendorQuery = database.collection('vendors');

        // Filter by vendor type since coupon type is specified
        console.log('🔍 Filtering vendors by vType:', couponType);
        vendorQuery = vendorQuery.where('vType', '==', couponType);

        vendorQuery.get().then(async function (snapshots) {
            console.log('📊 Found', snapshots.docs.length, 'vendors');

            // Sort vendors by title on client side
            var vendors = [];
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                vendors.push({id: listval.id, data: data});
            });

            // Sort by title
            vendors.sort((a, b) => a.data.title.localeCompare(b.data.title));

            // Add sorted vendors to dropdown
            vendors.forEach((vendor) => {
                console.log('🏪 Vendor:', vendor.data.title, 'vType:', vendor.data.vType);
                $('#vendor_restaurant_select').append($('<option></option>')
                    .attr('value', vendor.id)
                    .text(vendor.data.title));
            });
        }).catch(function(error) {
            console.error('❌ Error loading vendors:', error);
        });
    }

    // Load all vendors initially
    loadVendorsByType('');

    // Debug: Check what vendors exist in the database
    console.log('🔍 Checking all vendors in database...');
    database.collection('vendors').get().then(function(snapshots) {
        console.log('📊 Total vendors in database:', snapshots.docs.length);
        snapshots.docs.forEach(function(doc) {
            var data = doc.data();
            console.log('🏪 Vendor:', data.title, 'vType:', data.vType, 'ID:', doc.id);
        });
    });

    $(function () {
        $('#datetimepicker1 .date_picker').datepicker({
            dateFormat: 'mm/dd/yyyy',
            startDate: new Date(),
        });

        // Add event handler for coupon type change after DOM is ready
        $('#coupon_type').on('change', function() {
            var selectedCouponType = $(this).val();
            console.log('🎯 Coupon type changed to:', selectedCouponType);
            console.log('🔄 Reloading vendors with type:', selectedCouponType);
            loadVendorsByType(selectedCouponType);
        });

        // Debug: Check if element exists
        console.log('🔍 Coupon type element found:', $('#coupon_type').length);
    });
    var id = "<?php echo uniqid();?>";
    var resturant = "<?php echo $id; ?>";
    var resturant_id = '';
    if (resturant == '') {
        $("#vendor_restaurant_select").change(function () {
            resturant_id = $(this).val();
        });
    } else {
        resturant_id = "<?php echo $id; ?>";
    }
    $(".save-form-btn").click(function () {
    var code = $(".coupon_code").val();
    var discount = $(".coupon_discount").val();
    var description = $(".coupon_description").val();
    var item_value = parseInt($(".item_value").val(), 10);
    var usage_limit = parseInt($(".usage_limit").val(), 10);
    var couponType = $("#coupon_type").val();

    // Item value validation
    if (isNaN(item_value) || item_value < 0) {
        $(".error_top").show();
        $(".error_top").html("");
        $(".error_top").append("<p>Item Value must be a valid number (0 or greater).</p>");
        $(".item_value").focus();
        window.scrollTo(0, 0);
        return;
    }

    // Usage limit validation (hidden field - default to 0 for unlimited)
    if (isNaN(usage_limit) || usage_limit < 0) {
        usage_limit = 0; // Default to unlimited if invalid
    }
    var newdate = new Date($(".date_picker").val());
    var expiresAt = new Date(newdate.setHours(23, 59, 59, 999));
    var isEnabled = $(".coupon_enabled").is(":checked");
    var isPublic = $(".coupon_public").is(":checked");
    var discountType = $("#coupon_discount_type").val() || 'Fix Price'; // Default fallback
    if (discountType === 'Percentage' && (discount < 0 || discount > 100)) {
        $(".error_top").show();
        $(".error_top").html("");
        $(".error_top").append("<p>{{trans('Percentage discount is between 0% and 100%.')}}</p>");
        window.scrollTo(0, 0);
        return;
    }

    // Validate coupon type is selected
    if (!couponType || couponType === '') {
        $(".error_top").show();
        $(".error_top").html("");
        $(".error_top").append("<p>Please select a coupon type (Restaurant or Mart).</p>");
        window.scrollTo(0, 0);
        return;
    }

    // Validate restaurant/vendor selection - get current selected value
    var currentRestaurantId = $("#vendor_restaurant_select option:selected").val();
    if (!currentRestaurantId || currentRestaurantId === '') {
        $(".error_top").show();
        $(".error_top").html("");
        $(".error_top").append("<p>Please select a restaurant/vendor or choose 'All " + couponType + "s'.</p>");
        window.scrollTo(0, 0);
        return;
    }
    database.collection('coupons').where('code', '==', code).get().then(function (snapshot) {
        if (snapshot.size > 0) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('Code is already exist try another one!')}}</p>");
            window.scrollTo(0, 0);
        }
        else {
            if (code == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_coupon_code_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (discount == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_coupon_discount_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (discountType == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.select_coupon_discountType_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (newdate == 'Invalid Date') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.select_coupon_expdate_error')}}</p>");
                window.scrollTo(0, 0);
            } else {
                jQuery("#data-table_processing").show();
                storeImageData().then(IMG => {
                    database.collection('coupons').doc(id).set({
                        'code': code,
                        'description': description,
                        'discount': discount,
                        'expiresAt': expiresAt,
                        'isEnabled': isEnabled,
                        'id': id,
                        'discountType': discountType,
                        'image': IMG,
                        'resturant_id': currentRestaurantId,
                        'cType': couponType,
                        'isPublic': isPublic,
                        'item_value': item_value,
                        'usageLimit': usage_limit || 0,
                        'usedCount': 0,
                        'usedBy': []
                    }).then(async function (result) {
                        console.log('✅ Coupon saved successfully, now logging activity...');

                        // Log the activity with error handling and await the Promise
                        try {
                            if (typeof logActivity === 'function') {
                                console.log('🔍 Calling logActivity for coupon creation...');
                                await logActivity('coupons', 'created', 'Created new coupon: ' + code);
                                console.log('✅ Activity logging completed successfully');
                            } else {
                                console.error('❌ logActivity function is not available');
                            }
                        } catch (error) {
                            console.error('❌ Error calling logActivity:', error);
                        }

                        if (resturant) {
                            jQuery("#data-table_processing").hide();
                            window.location.href = "{{route('restaurants.coupons',$id)}}";
                        } else {
                            jQuery("#data-table_processing").hide();
                            window.location.href = '{{ route("coupons")}}';
                        }
                    }).catch(function (error) {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + error + "</p>");
                    });
                });
            }
        }
    });
});
    jQuery("#data-table_processing").hide();
});
var storageRef = firebase.storage().ref('images');
async function storeImageData() {
        var newPhoto = '';
        try {
            photo_coupon = photo_coupon.replace(/^data:image\/[a-z]+;base64,/, "")
            var uploadTask = await storageRef.child(fileName).putString(photo_coupon, 'base64', {contentType: 'image/jpg'});
            var downloadURL = await uploadTask.ref.getDownloadURL();
            newPhoto = downloadURL;
            photo_coupon = downloadURL;
        } catch (error) {
            console.log("ERR ===", error);
        }
        return newPhoto;
    }
function handleFileSelect(evt) {
    var f = evt.target.files[0];
    var reader = new FileReader();
    reader.onload = (function (theFile) {
        return function (e) {
            var filePayload = e.target.result;
            var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
            var val = f.name;
            var ext = val.split('.')[1];
            var docName = val.split('fakepath')[1];
            var filename = (f.name).replace(/C:\\fakepath\\/i, '')
            var timestamp = Number(new Date());
            var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
            photo_coupon = filePayload;
            fileName=filename;
            $(".coupon_image").empty();
            $(".coupon_image").append('<img class="rounded" style="width:50px" src="' + photo_coupon + '" alt="image">');
        };
    })(f);
    reader.readAsDataURL(f);
}
</script>
@endsection
