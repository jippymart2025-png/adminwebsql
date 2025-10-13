@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Mart Sub-Categories</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('mart-categories') !!}">Mart Categories</a></li>
                    <li class="breadcrumb-item"><a href="#" id="subcategoriesLink">Sub-Categories</a></li>
                    <li class="breadcrumb-item active">Edit Sub-Category</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="cat-edite-page max-width-box">
                <div class="card  pb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li role="presentation" class="nav-item">
                                <a href="#subcategory_information" aria-controls="description" role="tab" data-toggle="tab"
                                   class="nav-link active">Sub-Category Information</a>
                            </li>
                            <li role="presentation" class="nav-item">
                                <a href="#review_attributes" aria-controls="review_attributes" role="tab" data-toggle="tab"
                                   class="nav-link">{{trans('lang.reviewattribute_plural')}}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="error_top" style="display:none"></div>
                        <div class="row restaurant_payout_create" role="tabpanel">
                            <div class="restaurant_payout_create-inner tab-content">
                                <div role="tabpanel" class="tab-pane active" id="subcategory_information">
                                    <fieldset>
                                        <legend>Edit Mart Sub-Category</legend>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Sub-Category Name</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control subcategory-name">
                                                <div class="form-text text-muted">Enter the name for this sub-category
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label ">Sub-Category Description</label>
                                            <div class="col-7">
                            <textarea rows="7" class="subcategory_description form-control"
                                      id="subcategory_description"></textarea>
                                                <div class="form-text text-muted">Enter a description for this sub-category
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Sub-Category Image</label>
                                            <div class="col-7">
                                                <input type="file" id="subcategory_image">
                                                <div class="placeholder_img_thumb subcategory_image"></div>
                                                <div id="uploding_image"></div>
                                                <div class="form-text text-muted w-50">Upload an image for this sub-category
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Order</label>
                                            <div class="col-7">
                                                <input type="number" class="form-control" id="subcategory_order" value="1" min="1">
                                                <div class="form-text text-muted w-50">Display order within parent category</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Section</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="section_info" readonly>
                                                <div class="form-text text-muted w-50">Inherited from parent category</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Parent Category</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="parent_category_info" readonly>
                                                <div class="form-text text-muted w-50">Parent category information</div>
                                            </div>
                                        </div>
                                       <div class="form-check row width-100">
                                        <input type="checkbox" class="item_publish" id="item_publish">
                                        <label class="col-3 control-label"
                                               for="item_publish">Publish</label>
                                       </div>
                                        <div class="form-check row width-100" id="show_in_home">
                                            <input type="checkbox" id="show_in_homepage">
                                            <label class="col-3 control-label" for="show_in_homepage">{{trans('lang.show_in_home')}}</label>
                                            <div class="form-text text-muted w-50">{{trans('lang.show_in_home_desc')}}<span id="forsection"></span></div>
                                        </div>
                                    </fieldset>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="review_attributes">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="button" class="btn btn-primary save-setting-btn"><i class="fa fa-save"></i>
                            {{trans('lang.save')}}
                        </button>
                        <a href="#" id="backToSubcategories" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
var subcategoryId = "{{ $id }}";
var database = firebase.firestore();
var ref = database.collection('mart_subcategories').where("id", "==", subcategoryId);
var photo = "";
var fileName="";
var subcategoryImageFile="";
var placeholderImage = '';
var placeholder = database.collection('settings').doc('placeHolderImage');
var ref_review_attributes = database.collection('review_attributes');
var subcategory = '';
var storageRef = firebase.storage().ref('images');
var storage = firebase.storage();

placeholder.get().then(async function (snapshotsimage) {
    var placeholderImageData = snapshotsimage.data();
    placeholderImage = placeholderImageData.image;
})

$(document).ready(function () {
    jQuery("#data-table_processing").show();
    ref.get().then(async function (snapshots) {
        console.log('📊 Loading subcategory data for ID:', subcategoryId);
        
        if (snapshots.empty) {
            console.error('❌ No subcategory found with ID:', subcategoryId);
            alert('Sub-category not found!');
            return;
        }
        
        subcategory = snapshots.docs[0].data();
        console.log('📝 Subcategory data loaded:', subcategory);
        
        $(".subcategory-name").val(subcategory.title);
        $(".subcategory_description").val(subcategory.description);
        $("#subcategory_order").val(subcategory.subcategory_order || 1);
        $("#section_info").val(subcategory.section || 'General');
        $("#parent_category_info").val(subcategory.parent_category_title || 'Unknown');
        
        console.log('📋 Form fields populated:', {
            title: subcategory.title,
            description: subcategory.description,
            subcategory_order: subcategory.subcategory_order || 1,
            section: subcategory.section || 'General',
            parent_category_title: subcategory.parent_category_title || 'Unknown'
        });
        
        if (subcategory.photo != '' && subcategory.photo != null) {
            photo = subcategory.photo;
            subcategoryImageFile = subcategory.photo;
            $(".subcategory_image").append('<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="rounded" style="width:50px" src="' + photo + '" alt="image">');
            console.log('🖼️ Subcategory image loaded:', photo);
        } else {
            $(".subcategory_image").append('<img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">');
            console.log('🖼️ Using placeholder image');
        }
        
        if (subcategory.publish) {
            $("#item_publish").prop('checked', true);
            console.log('✅ Publish checkbox checked');
        }
        
        if (subcategory.show_in_homepage) {
            $("#show_in_homepage").prop('checked', true);
            console.log('✅ Show in homepage checkbox checked');
        }
        
        // Update navigation links
        var subcategoriesUrl = '{{ route("mart-subcategories.index", ["category_id" => ":category_id"]) }}'.replace(':category_id', subcategory.parent_category_id);
        $('#subcategoriesLink').attr('href', subcategoriesUrl);
        $('#backToSubcategories').attr('href', subcategoriesUrl);
        
        jQuery("#data-table_processing").hide();
        console.log('✅ Subcategory data loading completed');
    }).catch(function(error) {
        console.error('❌ Error loading subcategory data:', error);
        jQuery("#data-table_processing").hide();
        alert('Error loading subcategory data: ' + error.message);
    })
    
    ref_review_attributes.get().then(async function (snapshots) {
        var ra_html = '';
        snapshots.docs.forEach((listval) => {
            var data = listval.data();
            ra_html += '<div class="form-check width-100" >';
            var checked = subcategory && subcategory.review_attributes && $.inArray(data.id, subcategory.review_attributes) !== -1 ? 'checked' : '';
            ra_html += '<input type="checkbox" id="review_attribute_' + data.id + '" value="' + data.id + '" ' + checked + '>';
            ra_html += '<label class="col-3 control-label" for="review_attribute_' + data.id + '">' + data.title + '</label>';
            ra_html += '</div>';
        })
        $('#review_attributes').html(ra_html);
    })
    
    $(".save-setting-btn").click(async function () {
        console.log('🔍 Save button clicked - starting update process...');
        
        var title = $(".subcategory-name").val();
        var description = $(".subcategory_description").val();
        var item_publish = $("#item_publish").is(":checked");
        var show_in_homepage = $("#show_in_homepage").is(":checked");
        var review_attributes = [];
        
        console.log('📝 Form values:', {
            title: title,
            description: description,
            item_publish: item_publish,
            show_in_homepage: show_in_homepage
        });
        
        $('#review_attributes input').each(function () {
            if ($(this).is(':checked')) {
                review_attributes.push($(this).val());
            }
        });
        
        if (title == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Please enter a sub-category name</p>");
            window.scrollTo(0, 0);
            return false;
        }
        
        try {
            console.log('🔄 Starting image processing...');
            jQuery("#data-table_processing").show();
            
            // Process image first
            let IMG;
            try {
                IMG = await storeImageData();
                console.log('✅ Image processed:', IMG);
            } catch (imageError) {
                console.error('❌ Image processing error:', imageError);
                // Use existing image if processing fails
                IMG = photo || subcategoryImageFile;
                console.log('🔄 Using existing image as fallback:', IMG);
            }
            
            // Prepare update data
            const updateData = {
                'title': title,
                'description': description,
                'photo': IMG,
                'subcategory_order': parseInt($('#subcategory_order').val()) || 1,
                'review_attributes': review_attributes,
                'publish': item_publish,
                'show_in_homepage': show_in_homepage,
            };
            
            console.log('📊 Update data:', updateData);
            
            // Update the document
            await database.collection('mart_subcategories').doc(subcategoryId).update(updateData);
            console.log('✅ Sub-category updated successfully, now logging activity...');
            
            try {
                if (typeof logActivity === 'function') {
                    console.log('🔍 Calling logActivity for sub-category update...');
                    await logActivity('mart_subcategories', 'updated', 'Updated sub-category: ' + title);
                    console.log('✅ Activity logging completed successfully');
                } else {
                    console.error('❌ logActivity function is not available');
                }
            } catch (error) {
                console.error('❌ Error calling logActivity:', error);
            }
            
            jQuery("#data-table_processing").hide();
            console.log('🎉 Update completed successfully!');
            
            // Redirect to subcategories list
            var subcategoriesUrl = '{{ route("mart-subcategories.index", ["category_id" => ":category_id"]) }}'.replace(':category_id', subcategory.parent_category_id);
            window.location.href = subcategoriesUrl;
            
        } catch (error) {
            console.error('❌ Error during update:', error);
            jQuery("#data-table_processing").hide();
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Error updating sub-category: " + error.message + "</p>");
            window.scrollTo(0, 0);
        }
    });
});

async function storeImageData() {
    console.log('🖼️ Starting image processing...');
    console.log('📸 Current photo:', photo);
    console.log('📁 Original file:', subcategoryImageFile);
    console.log('📄 File name:', fileName);
    
    var newPhoto = '';
    try {
        // Delete old image if it's different from current
        if (subcategoryImageFile != "" && photo != subcategoryImageFile) {
            console.log('🗑️ Deleting old image...');
            try {
                var subcategoryOldImageUrlRef = await storage.refFromURL(subcategoryImageFile);
                var imageBucket = subcategoryOldImageUrlRef.bucket; 
                var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                if (imageBucket == envBucket) {
                    await subcategoryOldImageUrlRef.delete();
                    console.log("✅ Old file deleted successfully!");
                } else {
                    console.log('⚠️ Bucket not matched, skipping delete');
                }
            } catch (deleteError) {
                console.log("⚠️ Error deleting old file:", deleteError);
                // Continue with update even if delete fails
            }
        } 
        
        // Upload new image if it's different from original
        if (photo != subcategoryImageFile && photo && fileName) {
            console.log('📤 Uploading new image...');
            photo = photo.replace(/^data:image\/[a-z]+;base64,/, "");
            var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', { contentType: 'image/jpg' });
            var downloadURL = await uploadTask.ref.getDownloadURL();
            newPhoto = downloadURL;
            photo = downloadURL;
            console.log('✅ New image uploaded:', newPhoto);
        } else {
            newPhoto = photo;
            console.log('ℹ️ Using existing image:', newPhoto);
        }
    } catch (error) {
        console.error("❌ Error in storeImageData:", error);
        // Return existing photo if upload fails
        newPhoto = photo || subcategoryImageFile;
    }
    
    console.log('🖼️ Final photo URL:', newPhoto);
    return newPhoto;
}

//upload image with compression
$("#subcategory_image").resizeImg({
    callback: function(base64str) {
        try {
            console.log('🖼️ Image compression callback triggered');
            var val = $('#subcategory_image').val().toLowerCase();
            var ext = val.split('.')[1];
            var docName = val.split('fakepath')[1];
            var filename = $('#subcategory_image').val().replace(/C:\\fakepath\\/i, '')
            var timestamp = Number(new Date());
            var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
            
            console.log('📄 Generated filename:', filename);
            console.log('📸 Base64 string length:', base64str.length);
            
            photo = base64str;
            fileName = filename;
            
            $(".subcategory_image").empty();
            $(".subcategory_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
            $("#subcategory_image").val('');
            
            console.log('✅ Image processed and displayed successfully');
        } catch (error) {
            console.error('❌ Error in image compression callback:', error);
        }
    },
    error: function(error) {
        console.error('❌ Image compression error:', error);
        alert('Error processing image: ' + error.message);
    }
});
</script>
@endsection
