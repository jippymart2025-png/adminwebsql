@extends('layouts.app')
@section('content')
    <style>
        .editable-price {
            transition: all 0.2s ease;
            border-radius: 3px;
            padding: 2px 4px;
        }
        .editable-price:hover {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .editable-price.text-success {
            background-color: #d4edda !important;
            border-color: #c3e6cb !important;
        }
        .editable-price.text-danger {
            background-color: #f8d7da !important;
            border-color: #f5c6cb !important;
        }
        .editable-price input {
            border: 2px solid #007bff;
            border-radius: 3px;
            padding: 2px 4px;
            font-size: inherit;
        }

        .options-info {
            text-align: center;
        }

        .options-info .badge {
            font-size: 11px;
            padding: 4px 8px;
        }

        .options-info small {
            font-size: 10px;
        }

        /* Delete button styling */
        .delete-btn {
            color: #dc3545 !important;
            margin-left: 10px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .delete-btn:hover {
            color: #c82333 !important;
            text-decoration: none;
        }

        .delete-btn i {
            font-size: 16px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 5px;
        }
    </style>
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor restaurantTitle">Mart Items</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">Mart Items</li>
                </ol>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-self-center">
                                <span class="icon mr-3"><img src="{{ asset('images/food.png') }}"></span>
                                <h3 class="mb-0">{{trans('lang.mart_item_table')}}</h3>
                                <span class="counter ml-3 food_count"></span>
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
                                    <select class="form-control food_type_selector">
                                        <option value=""  selected>{{trans("lang.type")}}</option>
                                        <option value="veg">{{trans("lang.veg")}}</option>
                                        <option value="non-veg">{{trans("lang.non_veg")}}</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <select class="form-control restaurant_selector">
                                        <option value=""  selected>Mart</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <select class="form-control category_selector">
                                        <option value=""  selected>Mart Categories</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <select class="form-control feature_selector">
                                        <option value=""  selected>Item Features</option>
                                        <option value="spotlight">Spotlight</option>
                                        <option value="steal_of_moment">Steal of Moment</option>
                                        <option value="featured">Featured</option>
                                        <option value="trending">Trending</option>
                                        <option value="new">New Arrival</option>
                                        <option value="best_seller">Best Seller</option>
                                        <option value="seasonal">Seasonal</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <select class="form-control brand_selector">
                                        <option value=""  selected>Brands</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Import Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">Bulk Import Mart Items</h3>
                                <p class="mb-0 text-dark-2">Upload Excel file to import multiple mart items at once</p>
                                <small class="text-info">
                                    <i class="mdi mdi-lightbulb-outline mr-1"></i>
                                    <strong>Tip:</strong> You can use vendor names and category names instead of IDs for easier data entry!
                                </small>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a href="{{ route('mart-items.download-template') }}" class="btn btn-outline-primary rounded-full">
                                        <i class="mdi mdi-download mr-2"></i>Download Template
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('mart-items.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="importFile" class="control-label">Select Excel File (.xls/.xlsx)</label>
                                            <input type="file" name="file" id="importFile" accept=".xls,.xlsx" class="form-control" required>
                                            <div class="form-text text-muted">
                                                <i class="mdi mdi-information-outline mr-1"></i>
                                                File should contain: name, price, description, vendorID/vendorName, categoryID/categoryName, subcategoryID/subcategoryName, section, disPrice, publish, nonveg, isAvailable, photo, and optional item features
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary rounded-full">
                                            <i class="mdi mdi-upload mr-2"></i>Import Mart Items
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
                        <?php if ($id != '') { ?>
                        <div class="menu-tab">
                            <ul>
                                <li>
                                    <a href="{{route('restaurants.view', $id)}}">{{trans('lang.tab_basic')}}</a>
                                </li>
                                <li class="active">
                                    <a href="{{route('marts.mart-items', $id)}}">Mart Items</a>
                                </li>
                                <li>
                                    <a href="{{route('restaurants.orders', $id)}}">{{trans('lang.tab_orders')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('restaurants.coupons', $id)}}">{{trans('lang.tab_promos')}}</a>
                                <li>
                                    <a href="{{route('restaurants.payout', $id)}}">{{trans('lang.tab_payouts')}}</a>
                                </li>
                                <li>
                                    <a
                                        href="{{route('payoutRequests.restaurants.view', $id)}}">{{trans('lang.tab_payout_request')}}</a>
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
                                    <h3 class="text-dark-2 mb-2 h4">Mart Items Table</h3>
                                    <p class="mb-0 text-dark-2">Manage all mart items in the system</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <?php if ($id != '') { ?>
                                        <a class="btn-primary btn rounded-full"
                                           href="{!! route('mart-items.create') !!}/{{$id}}"><i
                                                class="mdi mdi-plus mr-2"></i>Create Mart Item</a>
                                        <?php } else { ?>
                                        <a class="btn-primary btn rounded-full" href="{!! route('mart-items.create') !!}"><i
                                                class="mdi mdi-plus mr-2"></i>Create Mart Item</a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="foodTable"
                                           class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                           cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <?php if (in_array('mart-items.delete', json_decode(@session('user_permissions'), true))) { ?>
                                            <th class="delete-all"><input type="checkbox" id="select-all">
                                                <label class="col-3 control-label" for="select-all">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i
                                                            class="mdi mdi-delete"></i> {{trans('lang.all')}}</a>
                                                </label>
                                            </th>
                                            <?php } ?>
                                            <th>Item Name</th>
                                            <th>Price</th>
                                            <th>Discount Price</th>
                                            <?php if ($id == '') { ?>
                                            <th>Mart</th>
                                            <?php } ?>
                                            <th>Mart Categories</th>
                                            <th>Brand</th>
                                            <th>Options</th>
                                            <th>Published</th>
                                            <th>Available</th>
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
        const urlParams=new URLSearchParams(location.search);
        for(const [key,value] of urlParams) {
            if(key=='categoryID') {
                var categoryID=value;
            } else {
                var categoryID='';
            }
        }
        var database=firebase.firestore();
        var currentCurrency='';
        var currencyAtRight=false;
        var decimal_degits=0;
        var storage=firebase.storage();
        var storageRef=firebase.storage().ref('images');
        var user_permissions='<?php echo @session("user_permissions") ?>';
        user_permissions=Object.values(JSON.parse(user_permissions));
        var checkDeletePermission=false;
        console.log('🔍 User permissions:', user_permissions);
        console.log('🔍 Checking for mart-items.delete permission...');
        if($.inArray('mart-items.delete',user_permissions)>=0) {
            checkDeletePermission=true;
            console.log('✅ Delete permission granted');
        } else {
            console.log('❌ Delete permission not found. Available permissions:', user_permissions);
        }
        var restaurantID="{{$id}}";
        if(categoryID!=''&&categoryID!=undefined) {
            var ref=database.collection('mart_items').where('categoryID','==',categoryID);
        } else {
            <?php if ($id != '') { ?>
            database.collection('vendors').doc('<?php echo $id; ?>').get().then(async function(snapshot) {
                if(snapshot.exists) {
                    var vendorData = snapshot.data();
                    // Check if it's a mart vendor
                    if (vendorData.vType && (vendorData.vType.toLowerCase() === 'mart' || vendorData.vType === 'Mart')) {
                        walletRoute="{{route('users.walletstransaction', ':id')}}";
                        walletRoute=walletRoute.replace(":id",vendorData.author);
                        $('#restaurant_wallet').append('<a href="'+walletRoute+'">{{trans("lang.wallet_transaction")}}</a>');
                        $('#subscription_plan').append('<a href="'+"{{route('vendor.subscriptionPlanHistory', ':id')}}".replace(':id',vendorData.author)+'">'+'{{trans('lang.subscription_history')}}'+'</a>');
                    }
                }
            });
            var ref=database.collection('mart_items').where('vendorID','==','<?php echo $id; ?>');
            const getStoreName=getStoreNameFunction('<?php echo $id; ?>');
            <?php } else { ?>
            var ref=database.collection('mart_items');
            <?php } ?>
        }
        var refCurrency=database.collection('currencies').where('isActive','==',true);
        var append_list='';
        refCurrency.get().then(async function(snapshots) {
            var currencyData=snapshots.docs[0].data();
            currentCurrency=currencyData.symbol;
            currencyAtRight=currencyData.symbolAtRight;
            if(currencyData.decimal_degits) {
                decimal_degits=currencyData.decimal_degits;
            }
        });
        var placeholderImage='';
        var placeholder=database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData=snapshotsimage.data();
            placeholderImage=placeholderImageData.image;
        })
        database.collection('mart_categories').get().then(async function(snapshots) {
            snapshots.docs.forEach((listval) => {
                var data=listval.data();
                $('.category_selector').append($("<option></option>")
                    .attr("value",data.id)
                    .text(data.title));
            })
        });
        
        // Load brands for filter
        database.collection('brands').where('status','==',true).get().then(async function(snapshots) {
            snapshots.docs.forEach((listval) => {
                var data=listval.data();
                $('.brand_selector').append($("<option></option>")
                    .attr("value",data.id)
                    .text(data.name));
            })
        });
        // Try both 'mart' and 'Mart' to handle case sensitivity
        // Fetch vendors with vType: 'mart' (case-insensitive)
        database.collection('vendors').get().then(async function(snapshots) {
            console.log('🔍 Found ' + snapshots.docs.length + ' total vendors');
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                // Check for mart vendors (case-insensitive)
                if (data.vType && (data.vType.toLowerCase() === 'mart' || data.vType === 'Mart')) {
                    console.log('📋 Mart Vendor:', data.title, 'ID:', data.id, 'vType:', data.vType);
                    if (data.title != '' && data.title != null) {
                        $('.restaurant_selector').append($("<option></option>")
                            .attr("value", data.id)
                            .text(data.title));
                    }
                }
            });
        }).catch(function(error) {
            console.error('❌ Error fetching vendors:', error);
        });
        var initialRef=ref;
        $('select').change(async function() {
            var restaurant = $('.restaurant_selector').val();
            var foodType = $('.food_type_selector').val();
            var category = $('.category_selector').val();
            var feature = $('.feature_selector').val();
            var brand = $('.brand_selector').val();
            refData = initialRef;
            if (restaurant) {
                refData = refData.where('vendorID', '==', restaurant);
            }
            if (foodType) {
                refData= (foodType=="veg") ? refData.where('nonveg', '==', false) : refData.where('nonveg', '==', true)
            }
            if (category) {
                refData=refData.where('categoryID','==',category);
            }
            if (feature) {
                switch(feature) {
                    case 'spotlight':
                        refData = refData.where('isSpotlight', '==', true);
                        break;
                    case 'steal_of_moment':
                        refData = refData.where('isStealOfMoment', '==', true);
                        break;
                    case 'featured':
                        refData = refData.where('isFeature', '==', true);
                        break;
                    case 'trending':
                        refData = refData.where('isTrending', '==', true);
                        break;
                    case 'new':
                        refData = refData.where('isNew', '==', true);
                        break;
                    case 'best_seller':
                        refData = refData.where('isBestSeller', '==', true);
                        break;
                    case 'seasonal':
                        refData = refData.where('isSeasonal', '==', true);
                        break;
                }
            }
            if (brand) {
                refData = refData.where('brandID', '==', brand);
            }
            ref=refData;
            $('#foodTable').DataTable().ajax.reload();
        });
        $(document).ready(function() {
            $('.restaurant_selector').select2({
                placeholder: "Mart",
                minimumResultsForSearch: Infinity,
                allowClear: true
            });
            $('.food_type_selector').select2({
                placeholder: "{{trans('lang.type')}}",
                minimumResultsForSearch: Infinity,
                allowClear: true
            });
            $('.category_selector').select2({
                placeholder: "Mart Categories",
                minimumResultsForSearch: Infinity,
                allowClear: true
            });
            $('.feature_selector').select2({
                placeholder: "Item Features",
                minimumResultsForSearch: Infinity,
                allowClear: true
            });
            $('.brand_selector').select2({
                placeholder: "Brands",
                minimumResultsForSearch: Infinity,
                allowClear: true
            });
            $('select').on("select2:unselecting", function(e) {
                var self = $(this);
                setTimeout(function() {
                    self.select2('close');
                }, 0);
            });
            $('#category_search_dropdown').hide();
            $(document.body).on('click','.redirecttopage',function() {
                var url=$(this).attr('data-url');
                window.location.href=url;
            });
            jQuery("#data-table_processing").show();
            $(document).on('click', '.dt-button-collection .dt-button', function () {
                $('.dt-button-collection').hide();
                $('.dt-button-background').hide();
            });
            $(document).on('click', function (event) {
                if (!$(event.target).closest('.dt-button-collection, .dt-buttons').length) {
                    $('.dt-button-collection').hide();
                    $('.dt-button-background').hide();
                }
            });
            var fieldConfig = {
                columns: [
                    { key: 'id', header: "Product ID" },
                    { key: 'foodName', header: "Item Name" },
                    { key: 'vendorID', header: "Mart ID" },
                    { key: 'restaurant', header: "Mart Name" },
                    { key: 'category', header: "Mart Categories" },
                    { key: 'brand', header: "Brand" },
                    { key: 'price', header: "Price" },
                    { key: 'disPrice', header: "Discount Price" },
                ],
                fileName: "Mart Items",
            };
            const table=$('#foodTable').DataTable({
                pageLength: 10, // Number of rows per page
                processing: false, // Show processing indicator
                serverSide: true, // Enable server-side processing
                responsive: true,
                ajax: async function(data,callback,settings) {
                    const start=data.start;
                    const length=data.length;
                    const searchValue=data.search.value.toLowerCase();
                    const orderColumnIndex=data.order[0].column;
                    const orderDirection=data.order[0].dir;
                    @if($id!='')
                    const orderableColumns=(checkDeletePermission)? ['','foodName','price','disPrice','category','brand','','']:['name','price','disPrice','category','brand','','']; // Ensure this matches the actual column names
                    @else
                    const orderableColumns=(checkDeletePermission)? ['','foodName','price','disPrice','restaurant','category','brand','','']:['name','price','disPrice','restaurant','category','brand','','']; // Ensure this matches the actual column names
                    @endif
                    const orderByField=orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                    if(searchValue.length>=3||searchValue.length===0) {
                        $('#data-table_processing').show();
                    }
                    await ref.get().then(async function(querySnapshot) {
                        if(querySnapshot.empty) {
                            $('.food_count').text(0);
                            console.error("No data found in Firestore.");
                            $('#data-table_processing').hide(); // Hide loader
                            callback({
                                draw: data.draw,
                                recordsTotal: 0,
                                recordsFiltered: 0,
                                filteredData: [],
                                data: [] // No data
                            });
                            return;
                        }
                        var restaurantNames={};
                        // Fetch restaurants names (mart vendors only)
                        @if($id=='')
                        const vendorDocs=await database.collection('vendors').get();
                        vendorDocs.forEach(doc => {
                            var vendorData = doc.data();
                            if (vendorData.vType && (vendorData.vType.toLowerCase() === 'mart' || vendorData.vType === 'Mart')) {
                                restaurantNames[doc.id]=vendorData.title;
                            }
                        });
                        @endif
                        var categoryNames={};
                        const categoryDocs=await database.collection('mart_categories').get();
                        categoryDocs.forEach(doc => {
                            categoryNames[doc.id]=doc.data().title;
                        });
                        
                        var brandNames={};
                        const brandDocs=await database.collection('brands').get();
                        brandDocs.forEach(doc => {
                            brandNames[doc.id]=doc.data().name;
                        });
                        let records=[];
                        let filteredRecords=[];
                        await Promise.all(querySnapshot.docs.map(async (doc) => {
                            let childData=doc.data();
                            childData.id=doc.id; // Ensure the document ID is included in the data
                            var finalPrice=0;
                            if(childData.hasOwnProperty('disPrice')&&childData.disPrice!=''&&childData.disPrice!='0') {
                                finalPrice=childData.disPrice;
                            } else {
                                finalPrice=childData.price;
                            }
                            childData.foodName=childData.name;
                            childData.finalPrice=parseInt(finalPrice);
                            childData.restaurant=restaurantNames[childData.vendorID]||'';
                            childData.category=categoryNames[childData.categoryID]||'';
                            childData.brand=brandNames[childData.brandID]||'';
                            if(searchValue) {
                                if(
                                    (childData.name&&childData.name.toString().toLowerCase().includes(searchValue))||
                                    (childData.price&&childData.price.toString().includes(searchValue))||
                                    (childData.disPrice&&childData.disPrice.toString().includes(searchValue))||
                                    (childData.restaurant&&childData.restaurant.toString().toLowerCase().includes(searchValue))||
                                    (childData.category&&childData.category.toString().toLowerCase().includes(searchValue))||
                                    (childData.brand&&childData.brand.toString().toLowerCase().includes(searchValue))||
                                    (childData.price_range&&childData.price_range.toString().toLowerCase().includes(searchValue))||
                                    (childData.best_value_option&&childData.best_value_option.toString().toLowerCase().includes(searchValue))
                                ) {
                                    filteredRecords.push(childData);
                                }
                            } else {
                                filteredRecords.push(childData);
                            }
                        }));
                        filteredRecords.sort((a,b) => {
                            let aValue=a[orderByField];
                            let bValue=b[orderByField];
                            if(orderByField==='price'||orderByField==='disPrice') {
                                aValue=a[orderByField]? parseInt(a[orderByField]):0;
                                bValue=b[orderByField]? parseInt(b[orderByField]):0;
                            } else {
                                aValue=a[orderByField]? a[orderByField].toString().toLowerCase():'';
                                bValue=b[orderByField]? b[orderByField].toString().toLowerCase():''
                            }
                            if(orderDirection==='asc') {
                                return (aValue>bValue)? 1:-1;
                            } else {
                                return (aValue<bValue)? 1:-1;
                            }
                        });
                        const totalRecords=filteredRecords.length;
                        $('.food_count').text(totalRecords);
                        const paginatedRecords=filteredRecords.slice(start,start+length);
                        await Promise.all(paginatedRecords.map(async (childData) => {
                            var getData=await buildHTML(childData);
                            records.push(getData);
                        }));
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: totalRecords, // Total number of records in Firestore
                            recordsFiltered: totalRecords,
                            filteredData: filteredRecords,
                            data: records // The actual data to display in the table
                        });
                    }).catch(function(error) {
                        console.error("Error fetching data from Firestore:",error);
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            filteredData: [],
                            data: [] // No data due to error
                        });
                    });
                },
                order: (checkDeletePermission)? [1,'asc']:[0,'asc'],
                columnDefs: [
                    {
                        orderable: false,
                        targets: (restaurantID=='')? ((checkDeletePermission)? [0,7,8]:[6,7]):((checkDeletePermission)? [0,6,7]:[5,7])
                    },
                    {
                        type: 'formatted-num',
                        targets: (checkDeletePermission)? [2,3]:[3,4]
                    }
                ],
                "language": {
                    "zeroRecords": "{{trans("lang.no_record_found")}}",
                    "emptyTable": "{{trans("lang.no_record_found")}}",
                    "processing": "" // Remove default loader
                },
                dom: 'lfrtipB',
                buttons: [
                    {
                        extend: 'collection',
                        text: '<i class="mdi mdi-cloud-download"></i> Export as',
                        className: 'btn btn-info',
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                text: 'Export Excel',
                                action: function (e, dt, button, config) {
                                    exportData(dt, 'excel',fieldConfig);
                                }
                            },
                            {
                                extend: 'pdfHtml5',
                                text: 'Export PDF',
                                action: function (e, dt, button, config) {
                                    exportData(dt, 'pdf',fieldConfig);
                                }
                            },
                            {
                                extend: 'csvHtml5',
                                text: 'Export CSV',
                                action: function (e, dt, button, config) {
                                    exportData(dt, 'csv',fieldConfig);
                                }
                            }
                        ]
                    }
                ],
                initComplete: function() {
                    $(".dataTables_filter").append($(".dt-buttons").detach());
                    $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete','new-password').val('');
                    $('.dataTables_filter label').contents().filter(function() {
                        return this.nodeType === 3;
                    }).remove();
                }
            });
            $('#search-input').on('input',debounce(function() {
                const searchValue=$(this).val();
                if(searchValue.length>=3) {
                    $('#data-table_processing').show();
                    table.search(searchValue).draw();
                } else if(searchValue.length===0) {
                    $('#data-table_processing').show();
                    table.search('').draw();
                }
            },300));
        });
        function debounce(func,wait) {
            let timeout;
            const context=this;
            return function(...args) {
                clearTimeout(timeout);
                timeout=setTimeout(() => func.apply(context,args),wait);
            };
        }
        async function buildHTML(val) {
            var html=[];
            newdate='';
            var imageHtml='';
            var id=val.id;
            var route1='{{route("mart-items.edit", ":id")}}';
            route1=route1.replace(':id',id);
            <?php if ($id != '') { ?>
                route1=route1+'?eid={{$id}}';
            <?php } ?>
            if(val.photo!=''&&val.photo!=null) {
                imageHtml='<img onerror="this.onerror=null;this.src=\''+placeholderImage+'\'" class="rounded" width="100%" style="width:70px;height:70px;" src="'+val.photo+'" alt="image">';
            } else {
                imageHtml='<img width="100%" style="width:70px;height:70px;" src="'+placeholderImage+'" alt="image">';
            }
            if(checkDeletePermission) {
                html.push('<td class="delete-all"><input type="checkbox" id="is_open_'+id+'" name="record" class="is_open" dataId="'+id+'"><label class="col-3 control-label"\n'+
                    'for="is_open_'+id+'" ></label></td>');
            }
            html.push(imageHtml+'<a href="'+route1+'" >'+val.name+'</a>');
            // Original price column - editable
            if(val.hasOwnProperty('disPrice') && val.disPrice != '' && val.disPrice != '0' && val.disPrice != val.price) {
                // Has discount - show original price with strikethrough
                if(currencyAtRight) {
                    html.push('<span class="editable-price text-muted" style="text-decoration: line-through; cursor: pointer;" data-id="'+val.id+'" data-field="price" data-value="'+val.price+'">'+parseFloat(val.price).toFixed(decimal_degits)+''+currentCurrency+'</span>');
                } else {
                    html.push('<span class="editable-price text-muted" style="text-decoration: line-through; cursor: pointer;" data-id="'+val.id+'" data-field="price" data-value="'+val.price+'">'+currentCurrency+''+parseFloat(val.price).toFixed(decimal_degits)+'</span>');
                }
                // Show discount price in green - editable
                if(currencyAtRight) {
                    html.push('<span class="editable-price text-green" style="cursor: pointer;" data-id="'+val.id+'" data-field="disPrice" data-value="'+val.disPrice+'">'+parseFloat(val.disPrice).toFixed(decimal_degits)+''+currentCurrency+'</span>');
                } else {
                    html.push('<span class="editable-price text-green" style="cursor: pointer;" data-id="'+val.id+'" data-field="disPrice" data-value="'+val.disPrice+'">'+currentCurrency+''+parseFloat(val.disPrice).toFixed(decimal_degits)+'</span>');
                }
            } else {
                // No discount - show regular price - editable
                if(currencyAtRight) {
                    html.push('<span class="editable-price text-green" style="cursor: pointer;" data-id="'+val.id+'" data-field="price" data-value="'+val.price+'">'+parseFloat(val.price).toFixed(decimal_degits)+''+currentCurrency+'</span>');
                } else {
                    html.push('<span class="editable-price text-green" style="cursor: pointer;" data-id="'+val.id+'" data-field="price" data-value="'+val.price+'">'+currentCurrency+''+parseFloat(val.price).toFixed(decimal_degits)+'</span>');
                }
                // Empty cell where discount price would be - editable
                html.push('<span class="editable-price text-muted" style="cursor: pointer;" data-id="'+val.id+'" data-field="disPrice" data-value="0">-</span>');
            }
            <?php if ($id == '') { ?>
            var restaurantroute='{{route("restaurants.view", ":id")}}';
            restaurantroute=restaurantroute.replace(':id',val.vendorID);
            html.push('<a href="'+restaurantroute+'">'+val.restaurant+'</a>');
            <?php } ?>
            var caregoryroute='{{route("categories.edit", ":id")}}';
            caregoryroute=caregoryroute.replace(':id',val.categoryID);
            html.push('<a href="'+caregoryroute+'">'+val.category+'</a>');
            
            // Add brand display
            if(val.brand && val.brand !== '') {
                html.push('<span class="badge badge-info">'+val.brand+'</span>');
            } else {
                html.push('<span class="text-muted">No Brand</span>');
            }

            // Enhanced Options column
            if(val.has_options && val.options && val.options.length > 0) {
                const optionsCount = val.options.length;
                const priceRange = val.price_range || `₹${val.min_price || 0} - ₹${val.max_price || 0}`;
                const bestValue = val.best_value_option ? 'Best Value' : '';
                const savings = val.savings_percentage ? `${val.savings_percentage.toFixed(1)}% off` : '';

                html.push(`<div class="options-info">
                    <span class="badge badge-info">${optionsCount} Options</span>
                    <br><small class="text-muted">${priceRange}</small>
                    ${bestValue ? `<br><small class="text-success">${bestValue}</small>` : ''}
                    ${savings ? `<br><small class="text-danger">${savings}</small>` : ''}
                </div>`);
            } else {
                html.push('<span class="text-muted">No Options</span>');
            }
            if(val.publish) {
                html.push('<label class="switch"><input type="checkbox" checked id="'+val.id+'" name="isActive"><span class="slider round"></span></label>');
            } else {
                html.push('<label class="switch"><input type="checkbox" id="'+val.id+'" name="isActive"><span class="slider round"></span></label>');
            }
            // Add isAvailable toggle
            if(val.isAvailable) {
                html.push('<label class="switch"><input type="checkbox" checked id="isAvailable_'+val.id+'" name="isAvailable"><span class="slider round"></span></label>');
            } else {
                html.push('<label class="switch"><input type="checkbox" id="isAvailable_'+val.id+'" name="isAvailable"><span class="slider round"></span></label>');
            }
            var actionHtml='';
            actionHtml+='<span class="action-btn"><a href="'+route1+'" class="link-td"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>';
            if(checkDeletePermission) {
                console.log('🔍 Adding delete button for item:', val.id, 'Permission check:', checkDeletePermission);
                actionHtml+='<a id="'+val.id+'" name="food-delete" href="javascript:void(0)" class="delete-btn" title="Delete Item"><i class="mdi mdi-delete"></i></a>';
            } else {
                console.log('⚠️ Delete permission not granted for item:', val.id, 'Permission check:', checkDeletePermission);
            }
            actionHtml+='</span>';
            html.push(actionHtml);
            return html;
        }
        async function checkIfImageExists(url,callback) {
            const img=new Image();
            img.src=url;
            if(img.complete) {
                callback(true);
            } else {
                img.onload=() => {
                    callback(true);
                };
                img.onerror=() => {
                    callback(false);
                };
            }
        }
        $(document).on("click","input[name='isActive']",async function(e) {
            var ischeck=$(this).is(':checked');
            var id=this.id;
            try {
                // Get item name for logging
                const itemDoc = await database.collection('mart_items').doc(id).get();
                let itemName = 'Unknown Item';
                if (itemDoc.exists) {
                    itemName = itemDoc.data().name || 'Unknown Item';
                }

                if(ischeck) {
                    await database.collection('mart_items').doc(id).update({
                        'publish': true
                    });

                    // Log activity
                    if (typeof logActivity === 'function') {
                        await logActivity('mart_items', 'published', 'Published mart item: ' + itemName);
                    }
                } else {
                    await database.collection('mart_items').doc(id).update({
                        'publish': false
                    });

                    // Log activity
                    if (typeof logActivity === 'function') {
                        await logActivity('mart_items', 'unpublished', 'Unpublished mart item: ' + itemName);
                    }
                }
            } catch (error) {
                console.error('Error updating publish status:', error);
                // Revert the checkbox state
                $(this).prop('checked', !ischeck);
            }
        });
        // Add isAvailable toggle logic
        $(document).on("click","input[name='isAvailable']",async function(e) {
            var ischeck=$(this).is(':checked');
            var id=this.id.replace('isAvailable_','');
            try {
                // Get item name for logging
                const itemDoc = await database.collection('mart_items').doc(id).get();
                let itemName = 'Unknown Item';
                if (itemDoc.exists) {
                    itemName = itemDoc.data().name || 'Unknown Item';
                }

                if(ischeck) {
                    await database.collection('mart_items').doc(id).update({
                        'isAvailable': true
                    });

                    // Log activity
                    if (typeof logActivity === 'function') {
                        await logActivity('mart_items', 'made_available', 'Made mart item available: ' + itemName);
                    }
                } else {
                    await database.collection('mart_items').doc(id).update({
                        'isAvailable': false
                    });

                    // Log activity
                    if (typeof logActivity === 'function') {
                        await logActivity('mart_items', 'made_unavailable', 'Made mart item unavailable: ' + itemName);
                    }
                }
            } catch (error) {
                console.error('Error updating availability status:', error);
                // Revert the checkbox state
                $(this).prop('checked', !ischeck);
            }
        });
        async function getStoreNameFunction(vendorId) {
            var vendorName='';
            await database.collection('vendors').doc(vendorId).get().then(async function(snapshot) {
                if(snapshot.exists) {
                    var vendorData = snapshot.data();
                    // Check if it's a mart vendor
                    if (vendorData.vType && (vendorData.vType.toLowerCase() === 'mart' || vendorData.vType === 'Mart')) {
                        vendorName = vendorData.title;
                        $('.restaurantTitle').html('{{trans("lang.mart_item_plural")}} - '+vendorName);
                        if(vendorData.dine_in_active==true) {
                            $(".dine_in_future").show();
                        }
                    }
                }
            });
            return vendorName;
        }
        $(document).on("click","a[name='food-delete']",async function(e) {
            e.preventDefault(); // Prevent default link behavior
            console.log('🔍 Delete button clicked for item ID:', this.id);
            
            var id=this.id;
            
            // Add confirmation dialog
            if (!confirm('Are you sure you want to delete this mart item? This action cannot be undone.')) {
                return;
            }
            
            try {
                console.log('🔍 Starting deletion process for item:', id);
                
                // Get item name before deletion for logging
                const itemDoc = await database.collection('mart_items').doc(id).get();
                let itemName = 'Unknown Item';
                if (itemDoc.exists) {
                    itemName = itemDoc.data().name || 'Unknown Item';
                    console.log('🔍 Item found:', itemName);
                } else {
                    console.error('❌ Item not found in database');
                    alert('Item not found in database.');
                    return;
                }

                console.log('🔍 Calling optimized delete for mart item...');
                await deleteMartItemWithImage(id);
                console.log('✅ Document and images deleted successfully');

                // Log activity
                if (typeof logActivity === 'function') {
                    console.log('🔍 Logging activity...');
                    await logActivity('mart_items', 'deleted', 'Deleted mart item: ' + itemName);
                    console.log('✅ Activity logged successfully');
                } else {
                    console.warn('⚠️ logActivity function not available');
                }

                console.log('🔍 Reloading page...');
                window.location.reload();
            } catch (error) {
                console.error('❌ Error deleting item:', error);
                alert('Error deleting item: ' + error.message + '. Please try again.');
            }
        });

        // Optimized delete function for mart items - skips expensive reference checking
        const deleteMartItemWithImage = async (id) => {
            const docRef = database.collection('mart_items').doc(id);
            try {
                const doc = await docRef.get();
                if (!doc.exists) {
                    console.log("No mart item found for deletion");
                    return;
                }

                const data = doc.data();
                
                // Delete the photo directly without reference checking (mart items typically don't share images)
                if (data.photo) {
                    console.log('🗑️ Deleting mart item image:', data.photo);
                    try {
                        // Direct deletion without reference checking for better performance
                        await deleteImageFromBucket(data.photo);
                        console.log('✅ Image deleted successfully');
                    } catch (imageError) {
                        console.warn('⚠️ Error deleting image (continuing with document deletion):', imageError);
                    }
                }

                // Delete the Firestore document
                await docRef.delete();
                console.log("Mart item document deleted successfully.");
                
            } catch (error) {
                console.error("Error deleting mart item:", error);
                throw error;
            }
        };

        $(document.body).on('change','#selected_search',function() {
            if(jQuery(this).val()=='category') {
                var ref_category=database.collection('mart_categories');
                ref_category.get().then(async function(snapshots) {
                    snapshots.docs.forEach((listval) => {
                        var data=listval.data();
                        $('#category_search_dropdown').append($("<option></option").attr("value",data.id).text(data.title));
                    });
                });
                jQuery('#search').hide();
                jQuery('#category_search_dropdown').show();
            } else {
                jQuery('#search').show();
                jQuery('#category_search_dropdown').hide();
            }
        });
        $('#select-all').change(function() {
            var isChecked=$(this).prop('checked');
            $('input[type="checkbox"][name="record"]').prop('checked',isChecked);
        });
        $('#deleteAll').click(async function() {
            if(confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                var deletedItems = [];

                // Loop through all selected records and delete them
                for (const element of $('input[type="checkbox"][name="record"]:checked')) {
                    var id = $(element).attr('dataId');
                    try {
                        // Get item name before deletion for logging
                        const itemDoc = await database.collection('mart_items').doc(id).get();
                        if (itemDoc.exists) {
                            deletedItems.push(itemDoc.data().name || 'Unknown Item');
                        }

                        await deleteMartItemWithImage(id);
                    } catch (error) {
                        console.error('Error deleting item:', error);
                    }
                }

                // Log bulk delete activity
                if (typeof logActivity === 'function' && deletedItems.length > 0) {
                    await logActivity('mart_items', 'bulk_deleted', 'Bulk deleted mart items: ' + deletedItems.join(', '));
                }

                window.location.reload();
            }
        });

        // Inline editing functionality for prices - using backend validation
        $(document).on('click', '.editable-price', function() {
            var $this = $(this);
            var currentValue = $this.data('value');
            var field = $this.data('field');
            var id = $this.data('id');

            // Create input field
            var input = $('<input>', {
                type: 'number',
                step: '0.01',
                min: '0',
                class: 'form-control form-control-sm',
                value: currentValue,
                style: 'width: 80px; display: inline-block;'
            });

            // Replace span with input
            $this.hide();
            $this.after(input);
            input.focus();

            // Handle save on enter or blur
            function saveValue() {
                var newValue = parseFloat(input.val());
                if (isNaN(newValue) || newValue < 0) {
                    newValue = 0;
                }

                // Remove input and show span
                input.remove();
                $this.show();

                // Show loading indicator
                $this.addClass('text-info');
                $this.text('Updating...');

                // Send AJAX request to backend for proper validation and data consistency
                $.ajax({
                    url: '{{ route("mart-items.inlineUpdate", ":id") }}'.replace(':id', id),
                    method: 'PATCH',
                    data: {
                        field: field,
                        value: newValue,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update the data attribute
                            $this.data('value', newValue);

                            // Update the display
                            var displayValue = newValue.toFixed(decimal_degits);
                            if (currencyAtRight) {
                                $this.text(displayValue + currentCurrency);
                            } else {
                                $this.text(currentCurrency + displayValue);
                            }

                            // Show success indicator
                            $this.removeClass('text-info').addClass('text-success');
                            setTimeout(function() {
                                $this.removeClass('text-success');
                            }, 1000);

                            // If there's a message about discount price being reset, show it
                            if (response.message && response.message.includes('discount price was reset')) {
                                // Find and update the discount price cell if it exists
                                var discountCell = $this.closest('tr').find('.editable-price[data-field="disPrice"]');
                                if (discountCell.length > 0) {
                                    discountCell.data('value', 0);
                                    discountCell.text('-');
                                    discountCell.removeClass('text-green').addClass('text-muted');
                                }
                            }
                        } else {
                            // Show error message
                            alert('Update failed: ' + response.message);
                            // Revert to original value
                            var originalValue = currentValue;
                            var displayValue = originalValue.toFixed(decimal_degits);
                            if (currencyAtRight) {
                                $this.text(displayValue + currentCurrency);
                            } else {
                                $this.text(currentCurrency + displayValue);
                            }
                            $this.removeClass('text-info');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = 'Update failed';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        alert(errorMessage);

                        // Revert to original value
                        var originalValue = currentValue;
                        var displayValue = originalValue.toFixed(decimal_degits);
                        if (currencyAtRight) {
                            $this.text(displayValue + currentCurrency);
                        } else {
                            $this.text(currentCurrency + displayValue);
                        }
                        $this.removeClass('text-info');
                    }
                });
            }

            input.on('blur', saveValue);
            input.on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    saveValue();
                }
            });

            // Handle escape key
            input.on('keydown', function(e) {
                if (e.which === 27) { // Escape key
                    input.remove();
                    $this.show();
                }
            });
        });

        // Inline editing functionality for prices - using backend validation
        $(document).on('click', '.editable-price', function() {
            var $this = $(this);
            var currentValue = $this.data('value');
            var field = $this.data('field');
            var id = $this.data('id');

            // Create input field
            var input = $('<input>', {
                type: 'number',
                step: '0.01',
                min: '0',
                class: 'form-control form-control-sm',
                value: currentValue,
                style: 'width: 80px; display: inline-block;'
            });

            // Replace span with input
            $this.hide();
            $this.after(input);
            input.focus();

            // Handle save on enter or blur
            function saveValue() {
                var newValue = parseFloat(input.val());
                if (isNaN(newValue) || newValue < 0) {
                    newValue = 0;
                }

                // Remove input and show span
                input.remove();
                $this.show();

                // Show loading indicator
                $this.addClass('text-info');
                $this.text('Updating...');

                // Send AJAX request to backend for proper validation and data consistency
                $.ajax({
                    url: '{{ route("mart-items.inlineUpdate", ":id") }}'.replace(':id', id),
                    method: 'PATCH',
                    data: {
                        field: field,
                        value: newValue,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update the data attribute
                            $this.data('value', newValue);

                            // Update the display
                            var displayValue = newValue.toFixed(decimal_degits);
                            if (currencyAtRight) {
                                $this.text(displayValue + currentCurrency);
                            } else {
                                $this.text(currentCurrency + displayValue);
                            }

                            // Show success indicator
                            $this.removeClass('text-info').addClass('text-success');
                            setTimeout(function() {
                                $this.removeClass('text-success');
                            }, 1000);

                            // If there's a message about discount price being reset, show it
                            if (response.message && response.message.includes('discount price was reset')) {
                                // Find and update the discount price cell if it exists
                                var discountCell = $this.closest('tr').find('.editable-price[data-field="disPrice"]');
                                if (discountCell.length > 0) {
                                    discountCell.data('value', 0);
                                    discountCell.text('-');
                                    discountCell.removeClass('text-green').addClass('text-muted');
                                }
                            }

                            // Reload the table to ensure data consistency
                            $('#foodTable').DataTable().ajax.reload(null, false);
                        } else {
                            // Show error message
                            alert('Update failed: ' + response.message);
                            // Revert to original value
                            var originalValue = currentValue;
                            var displayValue = originalValue.toFixed(decimal_degits);
                            if (currencyAtRight) {
                                $this.text(displayValue + currentCurrency);
                            } else {
                                $this.text(currentCurrency + displayValue);
                            }
                            $this.removeClass('text-info');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = 'Update failed';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        alert(errorMessage);

                        // Revert to original value
                        var originalValue = currentValue;
                        var displayValue = originalValue.toFixed(decimal_degits);
                        if (currencyAtRight) {
                            $this.text(displayValue + currentCurrency);
                        } else {
                            $this.text(currentCurrency + displayValue);
                        }
                        $this.removeClass('text-info');
                    }
                });
            }

            input.on('blur', saveValue);
            input.on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    saveValue();
                }
            });

            // Handle escape key
            input.on('keydown', function(e) {
                if (e.which === 27) { // Escape key
                    input.remove();
                    $this.show();
                }
            });
        });
    </script>
@endsection
