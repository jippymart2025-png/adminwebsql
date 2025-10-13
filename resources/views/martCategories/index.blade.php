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
                <li class="breadcrumb-item active">Mart Categories</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/category.png') }}"></span>
                        <h3 class="mb-0">Mart Categories Table</h3>
                        <span class="counter ml-3 category_count"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                        <div class="select-box pl-3">
                        </div>
                    </div>
                </div>
            </div>
        </div> 
      
       </div>
       @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row mb-4">
    <div class="col-12">
        <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">Bulk Import Mart Categories</h3>
                                <p class="mb-0 text-dark-2">Upload Excel file to import multiple mart categories at once</p>
                                <small class="text-info">
                                    <i class="mdi mdi-lightbulb-outline mr-1"></i>
                                    <strong>Tip:</strong> For photos, use media names, slugs, or direct URLs from the media module!
                                </small>
                                <br><small class="text-success">
                                    <i class="mdi mdi-shield-check mr-1"></i>
                                    <strong>Smart Media Protection:</strong> Images are only deleted when no other items reference them!
                                </small>
                            </div>
                <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">
                        <a href="{{ route('mart-categories.download-template') }}" class="btn btn-outline-primary rounded-full">
                            <i class="mdi mdi-download mr-2"></i>Download Template
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('mart-categories.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="importFile" class="control-label">Select Excel File (.xls/.xlsx)</label>
                                <input type="file" name="file" id="importFile" accept=".xls,.xlsx" class="form-control" required>
                                <div class="form-text text-muted">
                                    <i class="mdi mdi-information-outline mr-1"></i>
                                    File should contain: title, description, photo (media name/slug/URL), section, category_order, publish, show_in_homepage, mart_id, review_attributes
                                    <br><small class="text-success">
                                        <i class="mdi mdi-check-circle mr-1"></i>
                                        <strong>Advanced Media Integration:</strong> Supports media names, slugs, image names, direct URLs, and Firebase Storage URLs!
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary rounded-full">
                                <i class="mdi mdi-upload mr-2"></i>Import Mart Categories
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
       <div class="table-list">
       <div class="row">
           <div class="col-12">
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">Mart Categories Table</h3>
                    <p class="mb-0 text-dark-2">Manage all mart categories in the system</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('mart-categories.create') !!}"><i class="mdi mdi-plus mr-2"></i>Create Mart Category</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="categoriesTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <?php if (in_array('mart-categories.delete', json_decode(@session('user_permissions'),true))) { ?>
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php } ?>
                                    <th>Mart Category Name</th>
                                    <th>Section</th>
                                    <th>Sub-Categories</th>
                                    <th>Mart Items</th>
                                    <th>Published</th>
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
@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();
    var ref = database.collection('mart_categories').orderBy('title');
    var placeholderImage = '';
    var user_permissions = '<?php echo @session("user_permissions")?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    var subcategoryCountsCache = {}; // Cache for sub-category counts
    var cacheTimestamp = 0;
    var CACHE_DURATION = 60000; // 1 minute cache
    
    if ($.inArray('mart-categories.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
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

    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function (snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        });
        const table = $('#categoriesTable').DataTable({
            pageLength: 10, // Number of rows per page
            processing: false, // Show processing indicator
            serverSide: true, // Enable server-side processing
            responsive: true,
            ajax: function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;
                const orderableColumns = (checkDeletePermission) ? ['','title', 'section', 'subcategories_count', 'totalProducts','',''] : ['title', 'section', 'subcategories_count', 'totalProducts','','']; // Ensure this matches the actual column names
                const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }
                ref.get().then(async function (querySnapshot) {
                    if (querySnapshot.empty) {
                        $('.category_count').text(0);    
                        console.error("No data found in Firestore.");
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
                    // Get cached sub-categories count for better performance
                    const subcategoryCounts = await getCachedSubcategoryCounts();

                    await Promise.all(querySnapshot.docs.map(async (doc) => {
                        let childData = doc.data();
                        childData.id = doc.id; // Ensure the document ID is included in the data
                        if (childData.id) {
                            childData.totalProducts = await getProductTotal(childData.id);
                            // Use cached sub-categories count
                            childData.subcategories_count = subcategoryCounts[childData.id] || 0;
                            childData.has_subcategories = childData.subcategories_count > 0;
                        }
                        else {
                            childData.totalProducts = 0;
                            childData.subcategories_count = 0;
                            childData.has_subcategories = false;
                        }
                        if (searchValue) {
                            if (
                                (childData.title && childData.title.toString().toLowerCase().includes(searchValue)) ||
                                (childData.section && childData.section.toString().toLowerCase().includes(searchValue)) ||
                                (childData.subcategories_count && childData.subcategories_count.toString().includes(searchValue)) ||
                                (childData.totalProducts && childData.totalProducts.toString().includes(searchValue))
                            ) {
                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    }));
                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase() : '';
                        let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase() : '';
                        if (orderByField === 'totalProducts') {
                            aValue = a[orderByField] ? parseInt(a[orderByField]) : 0;
                            bValue = b[orderByField] ? parseInt(b[orderByField]) : 0;
                        }                        
                        if (orderDirection === 'asc') {
                            return (aValue > bValue) ? 1 : -1;
                        } else {
                            return (aValue < bValue) ? 1 : -1;
                        }
                    });
                    const totalRecords = filteredRecords.length;
                    $('.category_count').text(totalRecords);    
                    filteredRecords.slice(start, start + length).forEach(function (childData) {
                        var id = childData.id;
                        var route1 = '{{route("mart-categories.edit",":id")}}';
                        route1 = route1.replace(':id', id);
                        var url = '{{url("mart-items?categoryID=id")}}';
                        url = url.replace("id", id);
                        // Fix photo URL if needed
                        var fixedPhotoUrl = fixCategoryPhotoUrl(childData.photo);
                        var ImageHtml=childData.photo == '' || childData.photo == null ? '<img alt="" width="100%" style="width:70px;height:70px;" src="' + placeholderImage + '" alt="image">' : '<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="" width="100%" style="width:70px;height:70px;" src="' + fixedPhotoUrl + '" alt="image">'
                        var subcategoryLink = '{{ route("mart-subcategories.index", ["category_id" => ":category_id"]) }}'.replace(':category_id', childData.id);
                        var subcategoryCount = childData.subcategories_count || 0;
                        var hasSubcategories = childData.has_subcategories || false;
                        var subcategoryHtml = '<a href="' + subcategoryLink + '" class="text-primary">' + subcategoryCount + ' sub-categories</a>';
                        
                        records.push([
                            checkDeletePermission ? '<td class="delete-all"><input type="checkbox" id="is_open_' + childData.id + '" class="is_open" dataId="' + childData.id + '"><label class="col-3 control-label"\n' + 'for="is_open_' + childData.id + '" ></label></td>' : '',
                            ImageHtml+'<a href="' + route1 + '">' + childData.title + '</a>',
                            '<span class="badge badge-secondary">' + (childData.section || 'General') + '</span>',
                            subcategoryHtml,
                            '<a href="' + url + '">'+childData.totalProducts+'</a>',
                            childData.publish ? '<label class="switch"><input type="checkbox" checked id="' + childData.id + '" name="isSwitch"><span class="slider round"></span></label>' : '<label class="switch"><input type="checkbox" id="' + childData.id + '" name="isSwitch"><span class="slider round"></span></label>',
                            '<span class="action-btn"><a href="' + route1 + '"><i class="mdi mdi-lead-pencil" title="Edit"></i></a> <a href="' + subcategoryLink + '" class="btn btn-sm btn-info mr-1"><i class="mdi mdi-folder-multiple"></i> Manage</a><?php if(in_array('mart-categories.delete', json_decode(@session('user_permissions'),true))){ ?> <a id="' + childData.id + '" name="category-delete" class="delete-btn" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a><?php } ?></span>'                           
                        ]);
                    });
                    $('#data-table_processing').hide(); // Hide loader
                    
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords, // Total number of records in Firestore
                        recordsFiltered: totalRecords, // Number of records after filtering (if any)
                        data: records // The actual data to display in the table
                    });
                }).catch(function (error) {
                    console.error("Error fetching data from Firestore:", error);
                    $('#data-table_processing').hide(); // Hide loader
                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: [] // No data due to error
                    });
                });
            },           
            order: (checkDeletePermission) ? [1, 'asc'] : [0,'asc'],
            columnDefs: [
                { orderable: false, targets: (checkDeletePermission) ? [0,3,4] : [2, 3] },
            ],
            "language": {
                "zeroRecords": "{{trans("lang.no_record_found")}}",
                "emptyTable": "{{trans("lang.no_record_found")}}",
                "processing": "" // Remove default loader
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
    });
    async function getProductTotal(id, section_id) {
        var mart_products = database.collection('mart_items').where('categoryID', '==', id);
        var Product_total = 0;
        if (section_id) {
            mart_products = mart_products.where('section_id', '==', section_id)
        }
        await mart_products.get().then(async function (productSnapshots) {
            Product_total = productSnapshots.docs.length;
        });
        return Product_total;
    }

    async function getSubcategoriesCount(categoryId) {
        try {
            const querySnapshot = await database.collection('mart_subcategories')
                .where('parent_category_id', '==', categoryId)
                .get();
            return querySnapshot.size;
        } catch (error) {
            console.error('Error getting subcategories count for category', categoryId, ':', error);
            return 0;
        }
    }

    async function getCachedSubcategoryCounts() {
        const now = Date.now();
        
        // Return cached data if it's still valid
        if (subcategoryCountsCache && (now - cacheTimestamp) < CACHE_DURATION) {
            console.log('📦 Using cached sub-category counts');
            return subcategoryCountsCache;
        }
        
        console.log('🔄 Fetching fresh sub-category counts');
        const allSubcategoriesSnapshot = await database.collection('mart_subcategories').get();
        const counts = {};
        
        allSubcategoriesSnapshot.docs.forEach(doc => {
            const data = doc.data();
            const parentId = data.parent_category_id;
            if (parentId) {
                counts[parentId] = (counts[parentId] || 0) + 1;
            }
        });
        
        // Update cache
        subcategoryCountsCache = counts;
        cacheTimestamp = now;
        
        console.log('✅ Sub-category counts cached for', Object.keys(counts).length, 'categories');
        return counts;
    }

    function invalidateSubcategoryCache() {
        subcategoryCountsCache = {};
        cacheTimestamp = 0;
        console.log('🗑️ Sub-category cache invalidated');
    }
    $(document).on("click", "a[name='category-delete']", async function (e) {
        var id = this.id;
        var categoryTitle = '';
        try {
            var doc = await database.collection('mart_categories').doc(id).get();
            if (doc.exists) {
                categoryTitle = doc.data().title || 'Unknown';
            }
        } catch (error) {
            console.error('Error getting category title:', error);
        }
        await deleteDocumentWithImage('mart_categories',id,'photo');
        console.log('✅ Mart Category deleted successfully, now logging activity...');
        try {
            if (typeof logActivity === 'function') {
                console.log('🔍 Calling logActivity for mart category deletion...');
                await logActivity('mart_categories', 'deleted', 'Deleted mart category: ' + categoryTitle);
                console.log('✅ Activity logging completed successfully');
            } else {
                console.error('❌ logActivity function is not available');
            }
        } catch (error) {
            console.error('❌ Error calling logActivity:', error);
        }
        invalidateSubcategoryCache();
        window.location.href = '{{ route("mart-categories")}}';
    });
    $("#is_active").click(function () {
        $("#categoriesTable .is_open").prop('checked', $(this).prop('checked'));
    });
    $("#deleteAll").click(async function () {
        if ($('#categoriesTable .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                var selectedCategories = [];
                for (let i = 0; i < $('#categoriesTable .is_open:checked').length; i++) {
                    var dataId = $('#categoriesTable .is_open:checked').eq(i).attr('dataId');
                    try {
                        var doc = await database.collection('mart_categories').doc(dataId).get();
                        if (doc.exists) {
                            selectedCategories.push(doc.data().title || 'Unknown');
                        }
                    } catch (error) {
                        console.error('Error getting category title:', error);
                    }
                }
                for (let i = 0; i < $('#categoriesTable .is_open:checked').length; i++) {
                    var dataId = $('#categoriesTable .is_open:checked').eq(i).attr('dataId');
                    await deleteDocumentWithImage('mart_categories',dataId,'photo');
                }
                console.log('✅ Bulk mart category deletion completed, now logging activity...');
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for bulk mart category deletion...');
                        await logActivity('mart_categories', 'bulk_deleted', 'Bulk deleted mart categories: ' + selectedCategories.join(', '));
                        console.log('✅ Activity logging completed successfully');
                    } else {
                        console.error('❌ logActivity function is not available');
                    }
                } catch (error) {
                    console.error('❌ Error calling logActivity:', error);
                }
                invalidateSubcategoryCache();
                window.location.reload();
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
    $(document).on("click", "input[name='isSwitch']", async function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        var categoryTitle = '';
        try {
            var doc = await database.collection('mart_categories').doc(id).get();
            if (doc.exists) {
                categoryTitle = doc.data().title || 'Unknown';
            }
        } catch (error) {
            console.error('Error getting category title:', error);
        }
        if (ischeck) {
            database.collection('mart_categories').doc(id).update({'publish': true}).then(async function (result) {
                console.log('✅ Mart Category published successfully, now logging activity...');
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for mart category publish...');
                        await logActivity('mart_categories', 'published', 'Published mart category: ' + categoryTitle);
                        console.log('✅ Activity logging completed successfully');
                    } else {
                        console.error('❌ logActivity function is not available');
                    }
                } catch (error) {
                    console.error('❌ Error calling logActivity:', error);
                }
            });
        } else {
            database.collection('mart_categories').doc(id).update({'publish': false}).then(async function (result) {
                console.log('✅ Mart Category unpublished successfully, now logging activity...');
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for mart category unpublish...');
                        await logActivity('mart_categories', 'unpublished', 'Unpublished mart category: ' + categoryTitle);
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
</script>
@endsection
