@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Edit Mart Banner Item</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{!! route('mart.banners') !!}">Mart Banner Items</a></li>
                <li class="breadcrumb-item active">Edit Banner</li>
            </ol>
        </div>
    </div>
    <div class="card-body">
        <div class="error_top"></div>
        <div class="row restaurant_payout_create">
            <div class="restaurant_payout_create-inner">
                <fieldset>
                    <legend>Mart Banner Item Details</legend>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Title *</label>
                        <div class="col-7">
                            <input type="text" class="form-control title" placeholder="Enter banner title">
                        </div>
                    </div>
{{--                    <div class="form-group row width-100">--}}
{{--                        <label class="col-3 control-label">Description</label>--}}
{{--                        <div class="col-7">--}}
{{--                            <textarea class="form-control description" rows="3" placeholder="Enter banner description"></textarea>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="form-group row width-100">--}}
{{--                        <label class="col-3 control-label">Text</label>--}}
{{--                        <div class="col-7">--}}
{{--                            <textarea class="form-control text" rows="3" placeholder="Enter additional text content"></textarea>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Set Order</label>
                        <div class="col-7">
                            <input type="number" class="form-control set_order" min="0" value="0">
                        </div>
                    </div>
                    <div class="form-group row width-100">
                        <div class="form-check width-100">
                            <input type="checkbox" id="is_publish">
                            <label class="col-3 control-label" for="is_publish">Publish</label>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Photo</label>
                        <input type="file" onChange="handleFileSelect(event)" class="col-7" accept="image/*">
                        <div id="uploding_image"></div>
                        <div class="placeholder_img_thumb user_image"></div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Position</label>
                        <div class="col-7">
                            <select name="position" id="position" class="form-control">
                                <option value="top">Top</option>
                                <option value="middle">Middle</option>
                                <option value="bottom">Bottom</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row width-50" id="banner_screen">
                        <label class="col-3 control-label">Screen</label>
                        <div class="col-7">
                            <select name="screen" id="screen" class="form-control">
                                <option value="home">Home Screen</option>
                                <option value="product">Product Screen</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Zone</label>
                        <div class="col-7">
                            <select id="zone_select" class="form-control">
                                <option value="">Select Zone (Optional)</option>
                                <!-- options populated dynamically -->
                            </select>
                            <div class="form-text text-muted">
                                Select the zone for this banner (optional)
                            </div>
                        </div>
                    </div>
                    <div class="form-group row width-100 radio-form-row d-flex" id="redirect_type_div">
                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="store" name="redirect_type" id="store">
                            <label class="custom-control-label">Store</label>
                        </div>
                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="product" name="redirect_type" id="product">
                            <label class="custom-control-label">Product</label>
                        </div>
                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="mart_category" name="redirect_type" id="mart_category">
                            <label class="custom-control-label">Mart Category</label>
                        </div>
                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="ads_link" name="redirect_type" id="ads_link">
                            <label class="custom-control-label">Ads Link</label>
                        </div>
                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="external_link" name="redirect_type" id="external_links">
                            <label class="custom-control-label">External Link</label>
                        </div>
                    </div>
                    <div class="form-group row width-50" id="vendor_div" style="display: none;">
                        <label class="col-3 control-label">Store</label>
                        <div class="col-7">
                            <select name="storeId" id="storeId" class="form-control">
                                <option value="">Select Store</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row width-50" id="product_div" style="display: none;">
                        <label class="col-3 control-label">Product</label>
                        <div class="col-7">
                            <select name="productId" id="productId" class="form-control">
                                <option value="">Select Product</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row width-50" id="mart_category_div" style="display: none;">
                        <label class="col-3 control-label">Mart Category</label>
                        <div class="col-7">
                            <select name="martCategoryId" id="martCategoryId" class="form-control">
                                <option value="">Select Mart Category</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row width-50" id="ads_link_div" style="display: none;">
                        <label class="col-3 control-label">Ads Link</label>
                        <div class="col-7">
                            <input type="text" class="form-control" id="ads_link" placeholder="https://example.com/ads">
                        </div>
                    </div>
                    <div class="form-group row width-100" id="external_link_div" style="display: none;">
                        <label class="col-3 control-label">External Link</label>
                        <div class="col-7">
                            <input type="text" class="form-control extlink" id="external_link" placeholder="https://example.com">
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
    <div class="form-group col-12 text-center">
        <button type="button" class="btn btn-primary edit-mart-banner-btn"><i class="fa fa-save"></i> Save</button>
        <a href="{!! route('mart.banners') !!}" class="btn btn-default"><i class="fa fa-undo"></i>Cancel</a>
    </div>
</div>
@endsection
@section('scripts')
<!-- Load toast-master library for notifications -->
<script src="{{ asset('assets/plugins/toast-master/js/jquery.toast.js') }}"></script>

<script>
    // Use global Firebase instances from layout
    var database = window.database || firebase.firestore();
    var storage = window.storage || firebase.storage();
    var photo = '';
    var fileName = '';
    var new_added_photos = [];
    var bannerImageFile = "";
    var id = '{{ $id }}';
    var storageRef = firebase.storage().ref('images');

    $(document).ready(function() {
        // Wait for Firebase to be ready
        if (!database) {
            console.error('Firebase database not initialized');
            $.toast({
                heading: 'Error',
                text: 'Firebase not initialized. Please refresh the page.',
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'error',
                hideAfter: 5000
            });
            return;
        }

        // Load stores for store redirect
        loadStores();

        // Load products for product redirect
        loadProducts();

        // Load mart categories
        loadMartCategories();

        // Load zones
        loadZones();

        // Load existing banner data
        loadBannerData();

        // Handle redirect type change
        $('.redirect_type').on('change', function() {
            var redirectType = $(this).val();
            $('#vendor_div, #product_div, #mart_category_div, #ads_link_div, #external_link_div').hide();

            if (redirectType === 'store') {
                $('#vendor_div').show();
            } else if (redirectType === 'product') {
                $('#product_div').show();
            } else if (redirectType === 'mart_category') {
                $('#mart_category_div').show();
            } else if (redirectType === 'ads_link') {
                $('#ads_link_div').show();
            } else if (redirectType === 'external_link') {
                $('#external_link_div').show();
            }
        });

        // Handle zone change to reload stores
        $('#zone_select').on('change', function() {
            loadStores();
        });

        // Set default redirect type if none is selected
        if (!$('.redirect_type:checked').length) {
            $('.redirect_type[value="external_link"]').prop('checked', true);
            $('#external_link_div').show();
        }

        // Handle save button click
        $('.edit-mart-banner-btn').on('click', function() {
            updateMartBanner();
        });
    });

    // Load existing banner data
    async function loadBannerData() {
        try {
            console.log('🔄 Loading banner data for ID:', id);
            const doc = await database.collection('mart_banners').doc(id).get();

            if (doc.exists) {
                const bannerData = doc.data();
                console.log('📄 Banner data loaded:', bannerData);

                // Populate form fields
                $('.title').val(bannerData.title || '');
                $('.set_order').val(bannerData.set_order || 0);
                $('#is_publish').prop('checked', bannerData.is_publish !== false);
                $('#position').val(bannerData.position || 'top');
                $('#screen').val(bannerData.screen || 'home');

                // Set zone if exists
                if (bannerData.zoneId) {
                    $('#zone_select').val(bannerData.zoneId);
                }

                // Set redirect type
                var redirectType = bannerData.redirect_type || 'external_link';
                $('.redirect_type[value="' + redirectType + '"]').prop('checked', true);

                // Set redirect specific values and show appropriate div
                if (redirectType === 'store') {
                    $('#storeId').val(bannerData.storeId || '');
                    $('#vendor_div').show();
                } else if (redirectType === 'product') {
                    $('#productId').val(bannerData.productId || '');
                    $('#product_div').show();
                } else if (redirectType === 'mart_category') {
                    $('#martCategoryId').val(bannerData.martCategoryId || '');
                    $('#mart_category_div').show();
                } else if (redirectType === 'ads_link') {
                    $('#ads_link').val(bannerData.ads_link || '');
                    $('#ads_link_div').show();
                } else if (redirectType === 'external_link') {
                    $('#external_link').val(bannerData.external_link || '');
                    $('#external_link_div').show();
                }

                // Load image if exists
                if (bannerData.photo) {
                    bannerImageFile = bannerData.photo;
                    $('.user_image').html('<img src="' + bannerData.photo + '" style="max-width: 100px; max-height: 100px; border-radius: 4px;">');
                    photo = bannerData.photo;
                }

                console.log('✅ Banner data loaded successfully');

            } else {
                console.error('❌ Banner not found with ID:', id);
                $.toast({
                    heading: 'Error',
                    text: 'Banner not found',
                    position: 'top-right',
                    loaderBg: '#ff6849',
                    icon: 'error',
                    hideAfter: 5000
                });
                setTimeout(() => {
                    window.location.href = '{{ route("mart.banners") }}';
                }, 2000);
            }
        } catch (error) {
            console.error('❌ Error loading banner data:', error);
            $.toast({
                heading: 'Error',
                text: 'Error loading banner data: ' + error.message,
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'error',
                hideAfter: 5000
            });
        }
    }

    // Load stores for store redirect
    function loadStores() {
        console.log('🔄 Loading stores...');
        $('#storeId').html("");
        $('#storeId').append($("<option value=''>Select Store</option>"));

        var selectedZone = $('#zone_select').val();
        var currentTime = new Date();
        var currentDay = currentTime.getDay(); // 0 = Sunday, 1 = Monday, etc.
        var currentHour = currentTime.getHours();
        var currentMinute = currentTime.getMinutes();
        var currentTimeMinutes = currentHour * 60 + currentMinute;

        var ref_vendors = database.collection('vendors');
        ref_vendors.get().then(async function(snapshots) {
            console.log('📄 Found', snapshots.docs.length, 'vendors');
            var martStoresCount = 0;
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                
                // Filter by vType: mart
                if (data.vType !== 'mart') {
                    return;
                }

                // Filter by zone if selected
                if (selectedZone && data.zoneId !== selectedZone) {
                    return;
                }

                // Check if store is open
                var isOpen = true;
                if (data.isOpen === false) {
                    isOpen = false;
                }

                // Check working hours if available
                if (data.workingHours && data.workingHours[currentDay]) {
                    var dayHours = data.workingHours[currentDay];
                    if (dayHours.isOpen === false) {
                        isOpen = false;
                    } else if (dayHours.openTime && dayHours.closeTime) {
                        var openTime = dayHours.openTime.split(':');
                        var closeTime = dayHours.closeTime.split(':');
                        var openMinutes = parseInt(openTime[0]) * 60 + parseInt(openTime[1]);
                        var closeMinutes = parseInt(closeTime[0]) * 60 + parseInt(closeTime[1]);
                        
                        if (currentTimeMinutes < openMinutes || currentTimeMinutes > closeMinutes) {
                            isOpen = false;
                        }
                    }
                }

                // Only show open stores
                if (isOpen) {
                    martStoresCount++;
                    var storeName = data.title || data.name || 'Unnamed Store';
                    var zoneText = selectedZone ? '' : (data.zoneTitle ? ' (' + data.zoneTitle + ')' : '');
                    $('#storeId').append($("<option></option>")
                        .attr("value", data.id)
                        .text(storeName + zoneText));
                }
            });
            console.log('✅ Loaded', martStoresCount, 'open mart stores successfully');
        }).catch(function(error) {
            console.error('❌ Error loading stores:', error);
            $.toast({
                heading: 'Error',
                text: 'Error loading stores: ' + error.message,
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'error',
                hideAfter: 5000
            });
        });
    }

    // Load products for product redirect
    function loadProducts() {
        console.log('🔄 Loading products...');
        $('#productId').html("");
        $('#productId').append($("<option value=''>Select Product</option>"));

        var ref_products = database.collection('mart_items');
        ref_products.get().then(async function(snapshots) {
            console.log('📄 Found', snapshots.docs.length, 'products');
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                $('#productId').append($("<option></option>")
                    .attr("value", data.id)
                    .text(data.name || data.title || 'Unnamed Product'));
            });
            console.log('✅ Products loaded successfully');
        }).catch(function(error) {
            console.error('❌ Error loading products:', error);
            $.toast({
                heading: 'Error',
                text: 'Error loading products: ' + error.message,
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'error',
                hideAfter: 5000
            });
        });
    }

    // Load mart categories
    function loadMartCategories() {
        console.log('🔄 Loading mart categories...');
        $('#martCategoryId').html("");
        $('#martCategoryId').append($("<option value=''>Select Mart Category</option>"));

        var ref_categories = database.collection('mart_categories').orderBy('title', 'asc');
        ref_categories.get().then(async function(snapshots) {
            console.log('📄 Found', snapshots.docs.length, 'mart categories');
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                var displayText = data.title || data.name || 'Unnamed Category';
                var publishStatus = data.publish ? '' : ' (Unpublished)';
                var sectionText = data.section ? ' - ' + data.section : '';
                $('#martCategoryId').append($("<option></option>")
                    .attr("value", listval.id)
                    .text(displayText + sectionText + publishStatus));
            });
            console.log('✅ Mart categories loaded successfully');
        }).catch(function(error) {
            console.error('❌ Error loading mart categories:', error);
            $.toast({
                heading: 'Error',
                text: 'Error loading mart categories: ' + error.message,
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'error',
                hideAfter: 5000
            });
        });
    }

    // Load zones
    function loadZones() {
        console.log('🔄 Loading zones...');
        $('#zone_select').html("");
        $('#zone_select').append($("<option value=''>Select Zone (Optional)</option>"));

        var ref_zones = database.collection('zone').where('publish', '==', true).orderBy('name', 'asc');
        ref_zones.get().then(async function(snapshots) {
            console.log('📄 Found', snapshots.docs.length, 'zones');
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                $('#zone_select').append($("<option></option>")
                    .attr("value", listval.id)
                    .text(data.name || data.title || 'Unnamed Zone'));
            });
            console.log('✅ Zones loaded successfully');
        }).catch(function(error) {
            console.error('❌ Error loading zones:', error);
            $.toast({
                heading: 'Error',
                text: 'Error loading zones: ' + error.message,
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'error',
                hideAfter: 5000
            });
        });
    }

    // Handle file selection
    function handleFileSelect(event) {
        var f = event.target.files[0];
        if (f) {
            if (f.size > 5 * 1024 * 1024) {
                alert('File size should be less than 5MB');
                return;
            }

            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    var filePayload = e.target.result;
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    photo = filePayload;
                    fileName = filename;
                    $(".user_image").empty();
                    $(".user_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
                };
            })(f);
            reader.readAsDataURL(f);
        }
    }

    async function storeImageData() {
        var newPhoto = '';
        try {
            photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")
            var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', {
                contentType: 'image/jpg'
            });
            var downloadURL = await uploadTask.ref.getDownloadURL();
            newPhoto = downloadURL;
            photo = downloadURL;
        } catch (error) {
            console.log("ERR ===", error);
        }
        return newPhoto;
    }

    // Update mart banner
    async function updateMartBanner() {
        console.log('🔄 Starting banner update process...');

        var title = $('.title').val() ? $('.title').val().trim() : '';
        var setOrder = $('.set_order').val() || 0;
        var isPublish = $('#is_publish').is(':checked');
        var position = $('#position').val() || '';
        var screen = $('#screen').val() || '';
        var zone = $('#zone_select').val() || '';
        var redirectType = $('.redirect_type:checked').val() || '';
        var storeId = $('#storeId').val() || '';
        var productId = $('#productId').val() || '';
        var martCategoryId = $('#martCategoryId').val() || '';
        var adsLink = $('#ads_link').val() ? $('#ads_link').val().trim() : '';
        var externalLink = $('#external_link').val() ? $('#external_link').val().trim() : '';

        console.log('📝 Form data:', {
            title, setOrder, isPublish, position,
            redirectType, storeId, productId, externalLink, photo
        });

        // Clear previous errors
        $('.error_top').html('');

        // Validation
        if (!title) {
            $('.error_top').html('<p style="color: red;">Please enter banner title</p>');
            $.toast({
                heading: 'Validation Error',
                text: 'Please enter banner title',
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'error',
                hideAfter: 5000
            });
            $('.title').focus();
            return;
        }

        // Validate redirect type specific fields
        if (redirectType === 'store' && !storeId) {
            $('.error_top').html('<p style="color: red;">Please select a store</p>');
            $.toast({
                heading: 'Validation Error',
                text: 'Please select a store',
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'error',
                hideAfter: 5000
            });
            $('#storeId').focus();
            return;
        }

        if (redirectType === 'product' && !productId) {
            $('.error_top').html('<p style="color: red;">Please select a product</p>');
            $.toast({
                heading: 'Validation Error',
                text: 'Please select a product',
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'error',
                hideAfter: 5000
            });
            $('#productId').focus();
            return;
        }

        if (redirectType === 'mart_category' && !martCategoryId) {
            $('.error_top').html('<p style="color: red;">Please select a mart category</p>');
            $.toast({
                heading: 'Validation Error',
                text: 'Please select a mart category',
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'error',
                hideAfter: 5000
            });
            $('#martCategoryId').focus();
            return;
        }

        if (redirectType === 'ads_link' && !adsLink) {
            $('.error_top').html('<p style="color: red;">Please enter ads link</p>');
            $.toast({
                heading: 'Validation Error',
                text: 'Please enter ads link',
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'error',
                hideAfter: 5000
            });
            $('#ads_link').focus();
            return;
        }

        if (redirectType === 'external_link' && !externalLink) {
            $('.error_top').html('<p style="color: red;">Please enter external link</p>');
            $.toast({
                heading: 'Validation Error',
                text: 'Please enter external link',
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'error',
                hideAfter: 5000
            });
            $('#external_link').focus();
            return;
        }

        // Get zone title
        var zoneTitle = '';
        if (zone) {
            zoneTitle = $('#zone_select option:selected').text() || '';
        }

        // Show loading
        $('.edit-mart-banner-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        try {
            console.log('💾 Updating banner in Firestore...');

            // Upload new image if one was selected
            var imageUrl = photo;
            if (fileName && photo.includes('data:image')) {
                console.log('📸 Uploading new image to Firebase Storage...');
                imageUrl = await storeImageData();
                console.log('✅ Image uploaded successfully:', imageUrl);
            }

            // Prepare banner data
            var bannerData = {
                title: title,
                photo: imageUrl,
                set_order: parseInt(setOrder) || 0,
                is_publish: isPublish,
                position: position,
                screen: screen,
                zoneId: zone || '',
                zoneTitle: zoneTitle || '',
                redirect_type: redirectType,
                storeId: redirectType === 'store' ? storeId : null,
                productId: redirectType === 'product' ? productId : null,
                martCategoryId: redirectType === 'mart_category' ? martCategoryId : null,
                ads_link: redirectType === 'ads_link' ? adsLink : null,
                external_link: redirectType === 'external_link' ? externalLink : null,
                updated_at: firebase.firestore.FieldValue.serverTimestamp()
            };

            // Update in Firestore
            await database.collection('mart_banners').doc(id).update(bannerData);

            console.log('✅ Banner updated successfully in Firestore');

            // Log activity (don't block redirect if this fails)
            try {
                await logActivity('mart_banner_items', 'updated', 'Updated mart banner item: ' + title);
                console.log('✅ Activity logged successfully');
            } catch (logError) {
                console.warn('⚠️ Activity logging failed:', logError);
            }

            // Success message and redirect
            console.log('🎉 Showing success message and preparing redirect...');
            $.toast({
                heading: 'Success',
                text: 'Mart banner item updated successfully',
                position: 'top-right',
                loaderBg: '#2ecc71',
                icon: 'success',
                hideAfter: 3000
            });

            // Clear any previous errors
            $('.error_top').html('');

            // Redirect after a short delay to ensure user sees success message
            console.log('🔄 Redirecting to index page in 2 seconds...');
            setTimeout(() => {
                console.log('🚀 Executing redirect now...');
                window.location.href = '{{ route("mart.banners") }}';
            }, 2000);

        } catch (error) {
            console.error('❌ Error updating banner:', error);
            $('.error_top').html('<p style="color: red;">Error updating banner: ' + error.message + '</p>');
            $('.edit-mart-banner-btn').prop('disabled', false).html('<i class="fa fa-save"></i> Save');
            $.toast({
                heading: 'Error',
                text: 'Error updating banner: ' + error.message,
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'error',
                hideAfter: 5000
            });
        }
    }

    // Activity logging function
    async function logActivity(module, action, description) {
        try {
            await database.collection('activity_logs').add({
                module: module,
                action: action,
                description: description,
                user_id: '{{ auth()->id() }}',
                user_name: '{{ auth()->user()->name }}',
                timestamp: firebase.firestore.FieldValue.serverTimestamp(),
                ip_address: '{{ request()->ip() }}',
                user_agent: '{{ request()->userAgent() }}'
            });
        } catch (error) {
            console.error('Error logging activity:', error);
        }
    }
</script>
@endsection
