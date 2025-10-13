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
                    <li class="breadcrumb-item"><a href="{!! route('mart-categories') !!}">Mart Categories</a>
                    </li>
                    <li class="breadcrumb-item active">Create Mart Category</li>
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
                                <a href="#review_attributes" aria-controls="review_attributes" role="tab" data-toggle="tab"
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
                                        <legend>Create Mart Category</legend>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Mart Category Name</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control cat-name">
                                                <div class="form-text text-muted">Enter the name for this mart category
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label ">Mart Category Description</label>
                                            <div class="col-7">
                            <textarea rows="7" class="category_description form-control"
                                      id="category_description"></textarea>
                                                <div class="form-text text-muted">Enter a description for this mart category
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Mart Category Image</label>
                                            <div class="col-7">
                                                <input type="file" id="category_image">
                                                <div class="placeholder_img_thumb cat_image"></div>
                                                <div id="uploding_image"></div>
                                                <div class="form-text text-muted w-50">Upload an image for this mart category
                                                </div>
                                            </div>
                                        </div>
                                       <div class="form-check row width-100">
                                        <input type="checkbox" class="item_publish" id="item_publish">
                                        <label class="col-3 control-label"
                                               for="item_publish">Publish</label>
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
                        <a href="{!! route('mart-categories') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
    var database = firebase.firestore();
    var ref = database.collection('mart_categories');
    var photo = "";
    var fileName='';
    var id_category = "<?php echo uniqid();?>";
    var category_length = 1;
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    var ref_review_attributes = database.collection('review_attributes');
    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })
    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        ref.get().then(async function (snapshots) {
            category_length = snapshots.size + 1;
            jQuery("#data-table_processing").hide();
        })
        $(".save-setting-btn").click(async function () {
            var title = $(".cat-name").val();
            var description = $(".category_description").val();
            var section = $("#category_section").val().trim();
            var category_order = parseInt($("#category_order").val()) || 1;
            var item_publish = $("#item_publish").is(":checked");
            var show_in_homepage = $("#show_in_homepage").is(":checked");
            
            console.log('📝 Form values:', {
                title: title,
                description: description,
                section: section,
                category_order: category_order,
                item_publish: item_publish,
                show_in_homepage: show_in_homepage
            });
            var review_attributes = [];
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
            } else {
                var count_mart_categories = 0;
                    if (show_in_homepage) {
                        await database.collection('mart_categories').where('show_in_homepage', "==", true).get().then(async function (snapshots) {
                    count_mart_categories = snapshots.docs.length;
                    });
                    }
                    if (count_mart_categories >= 5) {
                alert("Already 5 mart categories are active for show in homepage..");
                return false;
            } else {
				jQuery("#data-table_processing").show();
              storeImageData().then(IMG => {
                database.collection('mart_categories').doc(id_category).set({
                    'id': id_category,
                    'title': title,
                    'description': description,
                    'photo': IMG,
                    'section': section || 'General',
                    'category_order': category_order,
                    'section_order': category_order,
                    'review_attributes': review_attributes,
                    'publish': item_publish,
                    'show_in_homepage': show_in_homepage,
                }).then(async function (result) {
                    console.log('✅ Mart Category saved successfully, now logging activity...');
                    try {
                        if (typeof logActivity === 'function') {
                            console.log('🔍 Calling logActivity for mart category creation...');
                            await logActivity('mart_categories', 'created', 'Created new mart category: ' + title);
                            console.log('✅ Activity logging completed successfully');
                        } else {
                            console.error('❌ logActivity function is not available');
                        }
                    } catch (error) {
                        console.error('❌ Error calling logActivity:', error);
                    }
                    jQuery("#data-table_processing").hide();
                    window.location.href = '{{ route("mart-categories")}}';
                });
                }).catch(function (error) {
				jQuery("#data-table_processing").hide();
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>" + error + "</p>");
            })
             }
            }
        });
    });
    ref_review_attributes.get().then(async function (snapshots) {
        var ra_html = '';
        snapshots.docs.forEach((listval) => {
            var data = listval.data();
            ra_html += '<div class="form-check width-100">';
            ra_html += '<input type="checkbox" id="review_attribute_' + data.id + '" value="' + data.id + '">';
            ra_html += '<label class="col-3 control-label" for="review_attribute_' + data.id + '">' + data.title + '</label>';
            ra_html += '</div>';
        });
        $('#review_attributes').html(ra_html);
    });
    var storageRef = firebase.storage().ref('images');
    async function storeImageData() {
        var newPhoto = '';
        try {
            photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")
            var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', {contentType: 'image/jpg'});
            var downloadURL = await uploadTask.ref.getDownloadURL();
            newPhoto = downloadURL;
            photo = downloadURL;
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
    //upload image with compression
    $("#category_image").resizeImg({
        callback: function(base64str) {
            var val = $('#category_image').val().toLowerCase();
            var ext = val.split('.')[1];
            var docName = val.split('fakepath')[1];
            var filename = $('#category_image').val().replace(/C:\\fakepath\\/i, '')
            var timestamp = Number(new Date());
            var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
            photo = base64str;
            fileName=filename;
            $(".cat_image").empty();
            $(".cat_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
            $("#category_image").val('');
        }
    });
</script>
@endsection