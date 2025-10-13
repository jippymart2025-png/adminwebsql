@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Mart Categories</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a
                                href="{!! route('mart-categories') !!}">Mart Categories</a></li>
                    <li class="breadcrumb-item active">Edit Mart Category</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="cat-edite-page max-width-box">
                <div class="card  pb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li role="presentation" class="nav-item">
                                <a href="#category_information" aria-controls="description" role="tab" data-toggle="tab"
                                   class="nav-link active">Mart Category Information</a>
                            </li>
                            <li role="presentation" class="nav-item">
                                <a href="#review_attributes" aria-controls="review_attributes" role="tab"
                                   data-toggle="tab"
                                   class="nav-link">{{trans('lang.reviewattribute_plural')}}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="error_top" style="display:none"></div>
                        <div class="row restaurant_payout_create" role="tabpanel">
                            <div class="restaurant_payout_create-inner tab-content">
                                <div role="tabpanel" class="tab-pane active" id="category_information">
                                    <fieldset>
                                        <legend>Edit Mart Category</legend>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Mart Category Name</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control cat-name">
                                                <div class="form-text text-muted">Enter the name for this mart category</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label ">Mart Category Description</label>
                                            <div class="col-7">
                                <textarea rows="7" class="category_description form-control"
                                          id="category_description"></textarea>
                                                <div class="form-text text-muted">Enter a description for this mart category</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Mart Category Image</label>
                                            <div class="col-7">
                                                <input type="file" id="category_image">
                                                <div class="placeholder_img_thumb cat_image"></div>
                                                <div id="uploding_image"></div>
                                                <div class="form-text text-muted w-50">Upload an image for this mart category</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Section</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="category_section" placeholder="e.g., Essentials & Daily Needs, Health & Wellness" list="section-suggestions">
                                                <datalist id="section-suggestions">
                                                    <option value="Essentials & Daily Needs">
                                                    <option value="Care for All Ages">
                                                    <option value="Health & Wellness">
                                                    <option value="Personal & Hygiene Care">
                                                    <option value="Home Care">
                                                    <option value="Grocery & Staples">
                                                    <option value="Snacks & Quick Bites">
                                                    <option value="Breakfast & Dairy">
                                                    <option value="Tea, Coffee & Beverages">
                                                    <option value="Pet Care">
                                                    <option value="Home & Utility">
                                                    <option value="Storage & Packaging">
                                                    <option value="First Aid & OTC">
                                                    <option value="Stationery & Office">
                                                    <option value="Beauty & Grooming">
                                                </datalist>
                                                <div class="form-text text-muted w-50">Group categories by sections for better organization</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Order</label>
                                            <div class="col-7">
                                                <input type="number" class="form-control" id="category_order" value="1" min="1">
                                                <div class="form-text text-muted w-50">Display order within section</div>
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
                        <button type="button" class="btn btn-primary edit-setting-btn"><i
                                    class="fa fa-save"></i> {{trans('lang.save')}}</button>
                        <a href="{!! route('mart-categories') !!}" class="btn btn-default"><i
                                    class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
var id = "<?php echo $id;?>";
var database = firebase.firestore();
var ref = database.collection('mart_categories').where("id", "==", id);
var photo = "";
var fileName="";
var catImageFile="";
var placeholderImage = '';
var placeholder = database.collection('settings').doc('placeHolderImage');
var ref_review_attributes = database.collection('review_attributes');
var category = '';
var storageRef = firebase.storage().ref('images');
var storage = firebase.storage();
placeholder.get().then(async function (snapshotsimage) {
    var placeholderImageData = snapshotsimage.data();
    placeholderImage = placeholderImageData.image;
})
$(document).ready(function () {
    jQuery("#data-table_processing").show();
    ref.get().then(async function (snapshots) {
        console.log('📊 Loading category data for ID:', id);
        
        if (snapshots.empty) {
            console.error('❌ No category found with ID:', id);
            alert('Category not found!');
            return;
        }
        
        category = snapshots.docs[0].data();
        console.log('📝 Category data loaded:', category);
        
        $(".cat-name").val(category.title);
        $(".category_description").val(category.description);
        $("#category_section").val(category.section || 'General');
        $("#category_order").val(category.category_order || 1);
        
        console.log('📋 Form fields populated:', {
            title: category.title,
            description: category.description,
            section: category.section || 'General',
            category_order: category.category_order || 1
        });
        
        // Function to fix invalid Firebase photo URLs
        function fixCategoryPhotoUrl(photoUrl) {
            if (!photoUrl || photoUrl === '' || photoUrl === null) {
                return null;
            }
            
            // Check if URL contains the problematic /media/ path (with or without extension)
            if (photoUrl.includes('/media%2Fmedia_') || photoUrl.includes('/media/media_')) {
                console.log('🔧 Fixing invalid photo URL:', photoUrl);
                
                // Extract the filename from the URL
                const urlParts = photoUrl.split('/o/');
                if (urlParts.length > 1) {
                    const pathAndParams = urlParts[1];
                    const pathPart = pathAndParams.split('?')[0];
                    const decodedPath = decodeURIComponent(pathPart);
                    
                    // Replace /media/ with /images/ and add .jpg extension
                    let fixedPath = decodedPath.replace('/media/', '/images/');
                    if (!fixedPath.endsWith('.jpg') && !fixedPath.endsWith('.png') && !fixedPath.endsWith('.jpeg')) {
                        fixedPath += '.jpg';
                    }
                    
                    // Reconstruct the URL
                    const encodedPath = encodeURIComponent(fixedPath);
                    const newUrl = urlParts[0] + '/o/' + encodedPath + '?' + pathAndParams.split('?')[1];
                    
                    console.log('✅ Fixed photo URL:', newUrl);
                    return newUrl;
                }
            }
            
            return photoUrl; // Return original URL if no fix needed
        }

        if (category.photo != '' && category.photo != null) {
            photo = fixCategoryPhotoUrl(category.photo);
            catImageFile = photo;
            $(".cat_image").append('<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="rounded" style="width:50px" src="' + photo + '" alt="image">');
            console.log('🖼️ Category image loaded:', photo);
        } else {
            $(".cat_image").append('<img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">');
            console.log('🖼️ Using placeholder image');
        }
        
        if (category.publish) {
            $("#item_publish").prop('checked', true);
            console.log('✅ Publish checkbox checked');
        }
        
        if (category.show_in_homepage) {
            $("#show_in_homepage").prop('checked', true);
            console.log('✅ Show in homepage checkbox checked');
        }
        
        jQuery("#data-table_processing").hide();
        console.log('✅ Category data loading completed');
    }).catch(function(error) {
        console.error('❌ Error loading category data:', error);
        jQuery("#data-table_processing").hide();
        alert('Error loading category data: ' + error.message);
    })
    ref_review_attributes.get().then(async function (snapshots) {
        var ra_html = '';
        snapshots.docs.forEach((listval) => {
            var data = listval.data();
            ra_html += '<div class="form-check width-100" >';
            var checked = $.inArray(data.id, category.review_attributes) !== -1 ? 'checked' : '';
            ra_html += '<input type="checkbox" id="review_attribute_' + data.id + '" value="' + data.id + '" ' + checked + '>';
            ra_html += '<label class="col-3 control-label" for="review_attribute_' + data.id + '">' + data.title + '</label>';
            ra_html += '</div>';
        })
        $('#review_attributes').html(ra_html);
    })
    $(".edit-setting-btn").click(async function () {
        console.log('🔍 Save button clicked - starting update process...');
        
        var title = $(".cat-name").val();
        var description = $(".category_description").val();
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
            $(".error_top").append("<p>Please enter a mart category name</p>");
            window.scrollTo(0, 0);
            return false;
        }
        
        try {
            var count_mart_categories = 0;
            if (show_in_homepage) {
                // Get all categories with show_in_homepage = true, then filter in JavaScript
                const snapshots = await database.collection('mart_categories').where('show_in_homepage', "==", true).get();
                count_mart_categories = snapshots.docs.filter(doc => doc.data().id !== id).length;
                console.log('📊 Found', count_mart_categories, 'other categories with show_in_homepage = true');
            }
            
            if (count_mart_categories >= 5) {
                alert("Already 5 mart categories are active for show in homepage..");
                return false;
            }
            
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
                IMG = photo || catImageFile;
                console.log('🔄 Using existing image as fallback:', IMG);
            }
            
            // Get section value - save user input even if not from suggestions
            var section = $('#category_section').val().trim();
            
            console.log('📝 Section value:', section);
            console.log('📝 Category order:', parseInt($('#category_order').val()) || 1);
            
            // Prepare update data
            const updateData = {
                'title': title,
                'description': description,
                'photo': IMG,
                'section': section || 'General',
                'section_order': parseInt($('#category_order').val()) || 1,
                'category_order': parseInt($('#category_order').val()) || 1,
                'review_attributes': review_attributes,
                'publish': item_publish,
                'show_in_homepage': show_in_homepage,
            };
            
            console.log('📊 Update data:', updateData);
            
            // Update the document
            await database.collection('mart_categories').doc(id).update(updateData);
            console.log('✅ Mart Category updated successfully, now logging activity...');
            
            try {
                if (typeof logActivity === 'function') {
                    console.log('🔍 Calling logActivity for mart category update...');
                    await logActivity('mart_categories', 'updated', 'Updated mart category: ' + title);
                    console.log('✅ Activity logging completed successfully');
                } else {
                    console.error('❌ logActivity function is not available');
                }
            } catch (error) {
                console.error('❌ Error calling logActivity:', error);
            }
            
            jQuery("#data-table_processing").hide();
            console.log('🎉 Update completed successfully!');
            window.location.href = '{{ route("mart-categories")}}';
            
        } catch (error) {
            console.error('❌ Error during update:', error);
            jQuery("#data-table_processing").hide();
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Error updating category: " + error.message + "</p>");
            window.scrollTo(0, 0);
        }
    });
});
function handleFileSelect(evt) {
    var f = evt.target.files[0];
    var reader = new FileReader();
    reader.onload = (function (theFile) {
        return function (e) {
            var filePayload = e.target.result;
            var val = $('#category_image').val().toLowerCase();
            var ext = val.split('.')[1];
            var docName = val.split('fakepath')[1];
            var filename = $('#category_image').val().replace(/C:\\fakepath\\/i, '')
            var timestamp = Number(new Date());
            var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
            var uploadTask = storageRef.child(filename).put(theFile);
            uploadTask.on('state_changed', function (snapshot) {
                var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
            }, function (error) {
            }, function () {
                uploadTask.snapshot.ref.getDownloadURL().then(function (downloadURL) {
                    jQuery("#uploding_image").text("Upload is completed");
                    photo = downloadURL;
                    $(".cat_image").empty();
                    $(".cat_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
                });
            });
        };
    })(f);
    reader.readAsDataURL(f);
}
async function storeImageData() {
    console.log('🖼️ Starting image processing...');
    console.log('📸 Current photo:', photo);
    console.log('📁 Original file:', catImageFile);
    console.log('📄 File name:', fileName);
    
    var newPhoto = '';
    try {
        // Delete old image if it's different from current
        if (catImageFile != "" && photo != catImageFile) {
            console.log('🗑️ Deleting old image...');
            try {
                var catOldImageUrlRef = await storage.refFromURL(catImageFile);
                var imageBucket = catOldImageUrlRef.bucket; 
                var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                if (imageBucket == envBucket) {
                    await catOldImageUrlRef.delete();
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
        if (photo != catImageFile && photo && fileName) {
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
        newPhoto = photo || catImageFile;
    }
    
    console.log('🖼️ Final photo URL:', newPhoto);
    return newPhoto;
}  
//upload image with compression
$("#category_image").resizeImg({
    callback: function(base64str) {
        try {
            console.log('🖼️ Image compression callback triggered');
            var val = $('#category_image').val().toLowerCase();
            var ext = val.split('.')[1];
            var docName = val.split('fakepath')[1];
            var filename = $('#category_image').val().replace(/C:\\fakepath\\/i, '')
            var timestamp = Number(new Date());
            var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
            
            console.log('📄 Generated filename:', filename);
            console.log('📸 Base64 string length:', base64str.length);
            
            photo = base64str;
            fileName = filename;
            
            $(".cat_image").empty();
            $(".cat_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
            $("#category_image").val('');
            
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