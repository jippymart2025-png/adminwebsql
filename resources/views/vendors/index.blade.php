@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">
                @if(request()->is('vendors/approved'))
                @php $type = 'approved'; @endphp
                {{trans('lang.approved_vendors')}}
                @elseif(request()->is('vendors/pending'))
                @php $type = 'pending'; @endphp
                {{trans('lang.approval_pending_vendors')}}
                @else
                @php $type = 'all'; @endphp
                {{trans('lang.all_vendors')}}
                @endif
            </h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.vendor_list')}}</li>
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
                            <span class="icon mr-3"><img src="{{ asset('images/vendor.png') }}"></span>
                            <h3 class="mb-0">{{trans('lang.vendor_list')}}</h3>
                            <span class="counter ml-3 vendor_count"></span>
                        </div>
                        <div class="d-flex top-title-right align-self-center">
                            <div class="select-box pl-3">
                                <select class="form-control vendor_type_selector filteredRecords">
                                    <option value="" selected>{{trans("lang.vendor_type")}}</option>
                                    <option value="restaurant">{{trans("lang.restaurant")}}</option>
                                    <option value="mart">{{trans("lang.mart")}}</option>
                                </select>
                            </div>
                            <div class="select-box pl-3">
                                <select class="form-control status_selector filteredRecords">
                                    <option value="" selected>{{trans("lang.status")}}</option>
                                    <option value="active">{{trans("lang.active")}}</option>
                                    <option value="inactive">{{trans("lang.in_active")}}</option>
                                </select>
                            </div>
                            <div class="select-box pl-3" style="display:block !important;">
                                <select class="form-control zone_selector filteredRecords" style="display:block !important;">
                                    <option value="" selected>{{trans("lang.select_zone")}}</option>
                                </select>
                            </div>
                            <div class="select-box pl-3" style="display:none !important;">
                                <select class="form-control zone_sort_selector filteredRecords" style="display:block !important;">
                                    <option value="" selected>{{trans("lang.sort_by_zone")}}</option>
                                    <option value="asc">{{trans("lang.zone_asc")}}</option>
                                    <option value="desc">{{trans("lang.zone_desc")}}</option>
                                </select>
                            </div>
                            <div class="select-box pl-3">
                            <div id="daterange"><i class="fa fa-calendar"></i>&nbsp;
                                <span></span>&nbsp; <i class="fa fa-caret-down"></i>
                            </div>
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
                    <h3 class="text-dark-2 mb-2 h4">Bulk Import Vendors</h3>
                    <p class="mb-0 text-dark-2">Upload Excel file to import multiple vendors at once</p>
                </div>
                <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">
                        <a href="{{ route('vendors.download-template') }}" class="btn btn-outline-primary rounded-full">
                            <i class="mdi mdi-download mr-2"></i>Download Template
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('vendors.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="importFile" class="control-label">Select Excel File (.xls/.xlsx)</label>
                                <input type="file" name="file" id="importFile" accept=".xls,.xlsx" class="form-control" required>
                                <div class="form-text text-muted">
                                    <i class="mdi mdi-information-outline mr-1"></i>
                                    File should contain: firstName, lastName, email, password, active, profilePictureURL, zoneId, phoneNumber, createdAt
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary rounded-full">
                                <i class="mdi mdi-upload mr-2"></i>Import Vendors
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
                                <h3 class="text-dark-2 mb-2 h4">{{trans('lang.vendor_list')}}</h3>
                                <p class="mb-0 text-dark-2">{{trans('lang.vendors_table_text')}}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a class="btn-primary btn rounded-full" href="{!! route('vendors.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.create_vendor')}}</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="userTable"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <?php if (
                                                ($type == "approved" && in_array('approve.vendors.delete', json_decode(@session('user_permissions'), true))) ||
                                                ($type == "pending" && in_array('pending.vendors.delete', json_decode(@session('user_permissions'), true))) ||
                                                ($type == "all" && in_array('vendors.delete', json_decode(@session('user_permissions'), true)))
                                            ) { ?>
                                                <th class="delete-all"><input type="checkbox" id="is_active">
                                                    <label class="col-3 control-label" for="is_active">
                                                        <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i
                                                                class="mdi mdi-delete"></i> {{trans('lang.all')}}</a>
                                                    </label>
                                                </th>
                                            <?php } ?>
                                            <th>{{trans('lang.vendor_info')}}</th>
                                            <th>{{trans('lang.email')}}</th>
                                            <th>{{trans('lang.phone_number')}}</th>
                                            <th>{{trans('lang.zone')}}</th>
                                            <th>{{trans('lang.vendor_type')}}</th>
                                            <th>{{trans('lang.current_plan')}}</th>
                                            <th>{{trans('lang.expiry_date')}}</th>
                                            <th>{{trans('lang.date')}}</th>
                                            <th>{{trans('lang.document_plural')}}</th>
                                            <th>{{trans('lang.active')}}</th>
                                            <th>{{trans('lang.actions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_list1">
                                    </tbody>
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
    var database=firebase.firestore();
    var type="{{$type}}";
    var user_permissions='<?php echo @session("user_permissions") ?>';
    user_permissions=Object.values(JSON.parse(user_permissions));
    var checkDeletePermission=false;
    if(
        (type=='pending'&&$.inArray('pending.vendors.delete',user_permissions)>=0)||
        (type=='approved'&&$.inArray('approve.vendors.delete',user_permissions)>=0)||
        (type=='all'&&$.inArray('vendors.delete',user_permissions)>=0)
    ) {
        checkDeletePermission=true;
    }
    var ref=database.collection('users').where("role","==","vendor");
    if(type=='pending') {
        ref=database.collection('users').where("role","==","vendor").where("active","==",false);
    } else if(type=='approved') {
        ref=database.collection('users').where("role","==","vendor").where("active","==",true);
    }
    var placeholderImage='';
    var append_list='';
    var initialRef=ref;
    var zones = [];
    var zoneIdToName = {};

    // Load zones
    console.log('🌍 Loading zones...');
    database.collection('zone').where('publish','==',true).orderBy('name','asc').get().then(async function(snapshots) {
        console.log('✅ Zones loaded, found:', snapshots.docs.length);
        
        if (snapshots.empty) {
            console.log('⚠️ No zones found');
        }
        
        snapshots.docs.forEach((listval) => {
            var data = listval.data();
            zones.push(data);
            $('.zone_selector').append($("<option></option>")
                .attr("value", listval.id)
                .text(data.name));
            console.log('📍 Zone:', data.name, 'ID:', listval.id);
        });
        
        // Build zoneId to name map
        zoneIdToName = {};
        snapshots.docs.forEach(function(doc) {
            var data = doc.data();
            zoneIdToName[doc.id] = data.name;
        });
        window.zoneIdToName = zoneIdToName;
        
        console.log('🗺️ Zone map created:', zoneIdToName);
        
        // Initialize DataTable only after zones are loaded
        console.log('🚀 Initializing vendor DataTable...');
        initializeVendorDataTable();
        
    }).catch(function(error) {
        console.error('❌ Error fetching zones:', error);
        console.error('❌ Zone error details:', {
            message: error.message,
            code: error.code
        });
        // Still initialize DataTable even if zones fail
        console.log('🚀 Initializing vendor DataTable (without zones)...');
        initializeVendorDataTable();
    });

    // Move DataTable initialization into a function
    function initializeVendorDataTable() {
        $(document.body).on('click','.redirecttopage',function() {
            var url=$(this).attr('data-url');
            window.location.href=url;
        });
        jQuery("#data-table_processing").show();
        var placeholder=database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData=snapshotsimage.data();
            placeholderImage=placeholderImageData.image;
        })
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
                { key: 'fullName', header: "{{trans('lang.vendor_info')}}" },
                { key: 'email', header: "{{trans('lang.email')}}" },
                { key: 'phoneNumber', header: "{{trans('lang.phone_number')}}" },
                { key: 'activePlanName', header: "{{trans('lang.active_subscription_plan')}}" },
                { key: 'subscriptionExpiryDate', header: "{{trans('lang.plan_expire_at')}}" },
                { key: 'createdAt', header: "{{trans('lang.created_at')}}" },
            ],
            fileName: "{{trans('lang.vendor_list')}}",
        };
        try {
            const table=$('#userTable').DataTable({
            pageLength: 10, // Number of rows per page
            processing: false, // Show processing indicator
            serverSide: true, // Enable server-side processing
            responsive: true,
            searching: true,
            info: true,
            paging: true,
            ajax: function(data,callback,settings) {
                const start=data.start;
                const length=data.length;
                const searchValue=data.search.value.toLowerCase();
                
                // Handle ordering safely - only if order data exists
                let orderColumnIndex = 0;
                let orderDirection = 'desc';
                if (data.order && data.order.length > 0) {
                    orderColumnIndex = data.order[0].column || 0;
                    orderDirection = data.order[0].dir || 'desc';
                }
                
                // Define orderable columns based on whether delete permission exists
                const orderableColumns = (checkDeletePermission) 
                    ? ['','vendorInfo','email','phoneNumber','zone','vType','currentPlan','expiryDate','date','documents','active','actions']
                    : ['vendorInfo','email','phoneNumber','zone','vType','currentPlan','expiryDate','date','documents','active','actions'];
                
                // Safely get the order field, handling cases where column index might be out of bounds
                const orderByField = (orderColumnIndex >= 0 && orderColumnIndex < orderableColumns.length) 
                    ? orderableColumns[orderColumnIndex] 
                    : 'createdAt'; // Default fallback
                if(searchValue.length>=3||searchValue.length===0) {
                    $('#data-table_processing').show();
                }
                // Fetch all vendors (users)
                console.log('🔍 Starting vendor data fetch...');
                ref.orderBy('createdAt','desc').get().then(async function(querySnapshot) {
                    console.log('📊 Found', querySnapshot.docs.length, 'vendor users');
                    
                    if(querySnapshot.empty) {
                        $('.vendor_count').text(0);
                        console.log("No vendor users found in Firestore.");
                        $('#data-table_processing').hide();
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            filteredData: [],
                            data: []
                        });
                        return;
                    }
                    
                    let records = [];
                    let filteredRecords = [];
                    let vendorsData = [];
                    
                    // Process each vendor user
                    querySnapshot.forEach(function(doc) {
                        let childData = doc.data();
                        childData.id = doc.id;
                        childData.fullName = (childData.firstName || '') + ' ' + (childData.lastName || '');
                        childData.zoneId = childData.zoneId || ''; // Get zoneId from user data if available
                        vendorsData.push(childData);
                        console.log('📝 Processed vendor:', childData.fullName, 'ID:', childData.id);
                    });
                    
                    // Try to fetch additional vendor details from vendors collection
                    console.log('🔍 Fetching additional vendor details...');
                    let vendorDetails = {};
                    
                    try {
                        // Get all vendor documents
                        const vendorSnapshot = await database.collection('vendors').get();
                        console.log('📊 Found', vendorSnapshot.docs.length, 'vendor documents');
                        
                        vendorSnapshot.docs.forEach(function(doc) {
                            const vendorData = doc.data();
                            if (vendorData.author) {
                                vendorDetails[vendorData.author] = vendorData;
                                console.log('🏪 Vendor business:', vendorData.title, 'Author:', vendorData.author);
                            }
                        });
                    } catch (error) {
                        console.error('❌ Error fetching vendor details:', error);
                    }
                    
                    // Merge vendor data
                    vendorsData.forEach(function(childData) {
                        // If we have vendor details, use them
                        if (vendorDetails[childData.id]) {
                            childData.zoneId = vendorDetails[childData.id].zoneId || childData.zoneId || '';
                            childData.vendorType = vendorDetails[childData.id].vType || childData.vType || '';
                            childData.vendorTitle = vendorDetails[childData.id].title || '';
                        } else {
                            // Use data from user document
                            childData.zoneId = childData.zoneId || '';
                            childData.vendorType = childData.vType || '';
                            childData.vendorTitle = '';
                        }
                        
                        console.log('🔗 Final vendor data:', {
                            name: childData.fullName,
                            id: childData.id,
                            zoneId: childData.zoneId,
                            vendorType: childData.vendorType
                        });
                    });
                                         // Filtering and search
                     console.log('🔍 Applying filters and search...');
                     vendorsData.forEach(function(childData) {
                         // Apply zone filter
                         let includeInResults = true;
                         if (window.selectedZone && window.selectedZone !== '') {
                             includeInResults = childData.zoneId === window.selectedZone;
                             console.log('📍 Zone filter:', childData.fullName, 'zoneId:', childData.zoneId, 'selected:', window.selectedZone, 'include:', includeInResults);
                         }
                         
                         // Apply vendor type filter
                         if (includeInResults && window.selectedVendorType && window.selectedVendorType !== '') {
                             const vendorType = childData.vendorType || childData.vType || '';
                             includeInResults = vendorType === window.selectedVendorType;
                             console.log('🏪 Vendor type filter:', childData.fullName, 'vendorType:', vendorType, 'selected:', window.selectedVendorType, 'include:', includeInResults);
                         }
                         
                         if (!includeInResults) {
                             return;
                         }
                        
                        // Search logic
                        var date = '';
                        var time = '';
                        if(childData.hasOwnProperty("createdAt")) {
                            try {
                                date = childData.createdAt.toDate().toDateString();
                                time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                            } catch(err) {
                                console.log('⚠️ Error parsing date for vendor:', childData.fullName);
                            }
                        }
                        var createdAt = date + ' ' + time;
                        
                                                 if(searchValue) {
                             const vendorType = childData.vendorType || childData.vType || '';
                             const searchMatches = 
                                 (childData.fullName && childData.fullName.toLowerCase().includes(searchValue)) ||
                                 (createdAt && createdAt.toLowerCase().includes(searchValue)) ||
                                 (childData.expiryDate && childData.expiryDate.toString().toLowerCase().includes(searchValue)) ||
                                 (childData.hasOwnProperty('activePlanName') && childData.activePlanName.toLowerCase().includes(searchValue)) ||
                                 (childData.email && childData.email.toLowerCase().includes(searchValue)) ||
                                 (childData.phoneNumber && childData.phoneNumber.toString().includes(searchValue)) ||
                                 (vendorType && vendorType.toLowerCase().includes(searchValue)) ||
                                 (window.zoneIdToName && childData.zoneId && window.zoneIdToName[childData.zoneId] && window.zoneIdToName[childData.zoneId].toLowerCase().includes(searchValue));
                            
                            if(searchMatches) {
                                filteredRecords.push(childData);
                                console.log('✅ Search match found for:', childData.fullName);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    });
                    
                    console.log('📊 Filtered records count:', filteredRecords.length);
                    // Sort by zone if requested
                    if (window.selectedZoneSort === 'asc') {
                        filteredRecords.sort((a, b) => {
                            let aZone = (window.zoneIdToName && a.zoneId && window.zoneIdToName[a.zoneId]) ? window.zoneIdToName[a.zoneId] : '';
                            let bZone = (window.zoneIdToName && b.zoneId && window.zoneIdToName[b.zoneId]) ? window.zoneIdToName[b.zoneId] : '';
                            return aZone.localeCompare(bZone);
                        });
                    } else if (window.selectedZoneSort === 'desc') {
                        filteredRecords.sort((a, b) => {
                            let aZone = (window.zoneIdToName && a.zoneId && window.zoneIdToName[a.zoneId]) ? window.zoneIdToName[a.zoneId] : '';
                            let bZone = (window.zoneIdToName && b.zoneId && window.zoneIdToName[b.zoneId]) ? window.zoneIdToName[b.zoneId] : '';
                            return bZone.localeCompare(aZone);
                        });
                    }
                    const totalRecords=filteredRecords.length;
                    $('.vendor_count').text(totalRecords);
                    const paginatedRecords=filteredRecords.slice(start,start+length);
                    await Promise.all(paginatedRecords.map(async (childData) => {
                        var getData=await buildHTML(childData);
                        console.log('🔍 Data returned for vendor:', childData.firstName + ' ' + childData.lastName);
                        console.log('🔍 Data length:', getData.length);
                        console.log('🔍 Expected columns:', checkDeletePermission ? 12 : 11);
                        
                        // Ensure the data array has the correct length
                        const expectedColumns = checkDeletePermission ? 12 : 11;
                        if (getData.length !== expectedColumns) {
                            console.error('❌ Column mismatch for vendor:', childData.firstName + ' ' + childData.lastName);
                            console.error('❌ Expected:', expectedColumns, 'Got:', getData.length);
                            // Pad or trim the array to match expected columns
                            while (getData.length < expectedColumns) {
                                getData.push('');
                            }
                            if (getData.length > expectedColumns) {
                                getData = getData.slice(0, expectedColumns);
                            }
                        }
                        
                        records.push(getData);
                    }));
                    $('#data-table_processing').hide(); // Hide loader
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords, // Total number of records in Firestore
                        recordsFiltered: totalRecords, // Number of records after filtering (if any)
                        filteredData: filteredRecords,
                        data: records // The actual data to display in the table
                    });
                }).catch(function(error) {
                    console.error("❌ Error fetching data from Firestore:", error);
                    console.error("❌ Error details:", {
                        message: error.message,
                        code: error.code,
                        stack: error.stack
                    });
                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        filteredData: [],
                        data: []
                    });
                });
            },
            order: [], // Disable ordering temporarily to fix the error
            columnDefs: [
                // Add default renderer for all columns to prevent "unknown parameter" errors
                {
                    targets: '_all',
                    defaultContent: '',
                    orderable: false, // Disable ordering for all columns temporarily
                    render: function(data, type, row, meta) {
                        // Return empty string if data is undefined or null
                        if (data === undefined || data === null) {
                            return '';
                        }
                        return data;
                    }
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
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable error:', error);
                console.error('Error details:', thrown);
                $('#data-table_processing').hide();
            }
        });
        } catch (error) {
            console.error('❌ DataTable initialization error:', error);
            console.error('❌ Error details:', {
                message: error.message,
                stack: error.stack
            });
            $('#data-table_processing').hide();
            // Show a user-friendly error message
            $('#userTable').html('<div class="alert alert-danger">Error loading vendor data. Please refresh the page.</div>');
        }
        function debounce(func,wait) {
            let timeout;
            const context=this;
            return function(...args) {
                clearTimeout(timeout);
                timeout=setTimeout(() => func.apply(context,args),wait);
            };
        }
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
    }
    $('.vendor_type_selector').select2({
        placeholder: '{{trans("lang.vendor_type")}}',
        minimumResultsForSearch: Infinity,
        allowClear: true
    });
    $('.status_selector').select2({
        placeholder: '{{trans("lang.status")}}',
        minimumResultsForSearch: Infinity,
        allowClear: true
    });

    $('.zone_selector').select2({
        placeholder: '{{trans("lang.select_zone")}}',
        minimumResultsForSearch: Infinity,
        allowClear: true
    });
    $('.zone_sort_selector').select2({
        placeholder: '{{trans("lang.sort_by_zone")}}',
        minimumResultsForSearch: Infinity,
        allowClear: true
    });
    $('select').on("select2:unselecting", function(e) {
        var self = $(this);
        setTimeout(function() {
            self.select2('close');
        }, 0);
    });
    function setDate() {
        $('#daterange span').html('{{trans("lang.select_range")}}');
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
        }, function (start, end) {
            $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $('.filteredRecords').trigger('change');
        });
        $('#daterange').on('apply.daterangepicker', function (ev, picker) {
            $('#daterange span').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
            $('.filteredRecords').trigger('change');
        });
        $('#daterange').on('cancel.daterangepicker', function (ev, picker) {
            $('#daterange span').html('{{trans("lang.select_range")}}');
            $('.filteredRecords').trigger('change');
        });
    }
    setDate();
    $('.filteredRecords').change(async function() {
        var status = $('.status_selector').val();
        var vendorType = $('.vendor_type_selector').val();
        var zone = $('.zone_selector').val();
        var zoneSort = $('.zone_sort_selector').val();
        var daterangepicker = $('#daterange').data('daterangepicker');
        var refData = initialRef;
        
                 console.log('🔍 Filter change detected:');
         console.log('  Status:', status);
         console.log('  Vendor Type:', vendorType);
         console.log('  Zone:', zone);
         console.log('  Zone Sort:', zoneSort);
         console.log('  → Stored vendor type filter:', window.selectedVendorType);

        if(status) {
            refData = (status === "active")
                ? refData.where('active','==',true)
                : refData.where('active','==',false);
        }

        // Store vendor type filter for use in ajax function
        window.selectedVendorType = vendorType;

        if ($('#daterange span').html() != '{{trans("lang.select_range")}}' && daterangepicker) {
            var from = moment(daterangepicker.startDate).toDate();
            var to = moment(daterangepicker.endDate).toDate();
            if (from && to) {
                var fromDate = firebase.firestore.Timestamp.fromDate(new Date(from));
                refData = refData.where('createdAt', '>=', fromDate);
                var toDate = firebase.firestore.Timestamp.fromDate(new Date(to));
                refData = refData.where('createdAt', '<=', toDate);
            }
        }

        // Store zone filter for use in ajax function
        window.selectedZone = zone;
        window.selectedZoneSort = zoneSort;
        ref = refData;
        $('#userTable').DataTable().ajax.reload();
    });
    async function buildHTML(listval) {
        var html=[];
        var val=listval;
        newdate='';
        var id=val.id;
        var route1='';
        var route1='{{route("vendor.edit", ":id")}}';
        route1=route1.replace(':id',id);
        var checkIsRestaurant=getUserRestaurantInfo(id);
        var trroute1='{{route("users.walletstransaction", ":id")}}';
        trroute1=trroute1.replace(':id',id);

        // Column 0: Delete checkbox (if permission exists)
        if(checkDeletePermission) {
            html.push('<input type="checkbox" id="is_open_'+id+'" class="is_open" dataId="'+id+'" data-vendorid="'+val.vendorID+'"><label class="col-3 control-label" for="is_open_'+id+'"></label>');
        }

        // Column 1: Vendor Info (image + name)
        if(val.profilePictureURL==''&&val.profilePictureURL==null) {
            imageHtml='<img width="100%" style="width:70px;height:70px;" src="'+placeholderImage+'" alt="image">';
        } else {
           imageHtml='<img onerror="this.onerror=null;this.src=\''+placeholderImage+'\'" class="rounded" width="100%" style="width:70px;height:70px;" src="'+val.profilePictureURL+'" alt="image">';
        }
        if((val.firstName!=""&&val.firstName!=null)||(val.lastName!=""&&val.lastName!=null)) {
            html.push(imageHtml+'<a  href="'+route1+'">'+val.firstName+' '+val.lastName+'</a>');
        }
        else {
            html.push('');
        }

        // Column 2: Email
        if(val.email!=""&&val.email!=null) {
            html.push(val.email);
        }
        else {
            html.push("");
        }

        // Column 3: Phone Number
        if(val.phoneNumber!=""&&val.phoneNumber!=null) {
            html.push(val.phoneNumber);
        }
        else {
            html.push("");
        }

        // Column 4: Zone
        if(val.hasOwnProperty('zoneId') && val.zoneId && window.zoneIdToName && window.zoneIdToName[val.zoneId]) {
            html.push(window.zoneIdToName[val.zoneId]);
        } else {
            html.push('<span class="text-muted">No Zone</span>');
        }

        // Column 5: Vendor Type
        if(val.hasOwnProperty('vendorType') && val.vendorType) {
            html.push(val.vendorType.charAt(0).toUpperCase() + val.vendorType.slice(1));
        } else if(val.hasOwnProperty('vType') && val.vType) {
            html.push(val.vType.charAt(0).toUpperCase() + val.vType.slice(1));
        } else {
            // Default to Restaurant if no vendor type is set
            html.push('<span class="text-primary">Restaurant</span>');
        }

        // Column 6: Current Plan
        if(val.hasOwnProperty('subscription_plan') && val.subscription_plan && val.subscription_plan.name) {
            html.push(val.subscription_plan.name);
        } else {
            html.push('');
        }

        // Column 7: Expiry Date
        if(val.hasOwnProperty('subscriptionExpiryDate')) {
            html.push(val.expiryDate);
        } else {
            html.push('');
        }

        // Column 8: Date
        var date='';
        var time='';
        if(val.hasOwnProperty("createdAt")) {
            try {
                date=val.createdAt.toDate().toDateString();
                time=val.createdAt.toDate().toLocaleTimeString('en-US');
            } catch(err) {
            }
            html.push('<span class="dt-time">'+date+' '+time+'</span>');
        } else {
            html.push('');
        }

        // Column 9: Documents
        document_list_view="{{route('vendors.document', ':id')}}";
        document_list_view=document_list_view.replace(':id',val.id);
        html.push('<a href="'+document_list_view+'"><i class="fa fa-file"></i></a>');

        // Column 10: Active
        if(val.active) {
            html.push('<label class="switch"><input type="checkbox" checked id="'+val.id+'" name="isActive"><span class="slider round"></span></label>');
        } else {
            html.push('<label class="switch"><input type="checkbox" id="'+val.id+'" name="isActive"><span class="slider round"></span></label>');
        }

        // Column 11: Actions
        var action='<span class="action-btn">';
        var planRoute="{{route('vendor.subscriptionPlanHistory',':id')}}";
        planRoute=planRoute.replace(':id',val.id);
        if(val.hasOwnProperty('subscription_plan')) {
            action+='<a id="'+val.id+'"  href="'+planRoute+'"><i class="mdi mdi-crown"></i></a>';
        }
        action+='<a id="'+val.id+'"  href="'+route1+'"><i class="mdi mdi-lead-pencil"></i></a>';
        if(checkDeletePermission) {
            action=action+'<a id="'+val.id+'" data-vendorid="'+val.vendorID+'" class="delete-btn" name="vendor-delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a>';
        }
        action=action+'</span>';
        html.push(action);

        // Ensure we always return exactly the right number of columns
        const expectedColumns = checkDeletePermission ? 12 : 11;
        if (html.length !== expectedColumns) {
            console.error('❌ Column count mismatch! Expected:', expectedColumns, 'Got:', html.length);
            // Pad with empty columns if needed
            while (html.length < expectedColumns) {
                html.push('');
            }
            // Trim if too many
            if (html.length > expectedColumns) {
                html = html.slice(0, expectedColumns);
            }
        }

        // Debug: log the number of columns for each row
        console.log('🔍 buildHTML for vendor:', val.firstName + ' ' + val.lastName);
        console.log('🔍 Number of columns returned:', html.length);
        console.log('🔍 Expected columns:', expectedColumns);
        console.log('🔍 Columns:', html);

        return html;
    }
    async function getUserRestaurantInfo(userId) {
        await database.collection('vendors').where('author','==',userId).get().then(async function(restaurantSnapshots) {
            if(restaurantSnapshots.docs.length>0) {
                var restaurantId=restaurantSnapshots.docs[0].data();
                restaurantId=restaurantId.id;
                var restaurantView='{{route("restaurants.edit", ":id")}}';
                restaurantView=restaurantView.replace(':id',restaurantId);
                $('#userName_'+userId).attr('data-url',restaurantView);
            }
        });
    }
    $("#is_active").click(function() {
        $("#userTable .is_open").prop('checked',$(this).prop('checked'));
    });
    $("#deleteAll").click(async function() {
        if($('#userTable .is_open:checked').length) {
            if(confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                var selectedVendors = [];
                for (let i = 0; i < $('#userTable .is_open:checked').length; i++) {
                    var dataId = $('#userTable .is_open:checked').eq(i).attr('dataId');
                    try {
                        var doc = await database.collection('users').doc(dataId).get();
                        if (doc.exists) {
                            var vendorData = doc.data();
                            selectedVendors.push((vendorData.firstName || '') + ' ' + (vendorData.lastName || 'Unknown'));
                        }
                    } catch (error) {
                        console.error('Error getting vendor name:', error);
                    }
                }

                $('#userTable .is_open:checked').each(function() {
                    var dataId=$(this).attr('dataId');
                    var VendorId=$(this).attr('data-vendorid');
                    deleteDocumentWithImage('users',dataId,'profilePictureURL')
                        .then(() => {
                            return deleteUserData(dataId,VendorId);
                        })
                        .then(async () => {
                            console.log('✅ Bulk vendor deletion completed, now logging activity...');
                            try {
                                if (typeof logActivity === 'function') {
                                    console.log('🔍 Calling logActivity for bulk vendor deletion...');
                                    await logActivity('vendors', 'bulk_deleted', 'Bulk deleted vendors: ' + selectedVendors.join(', '));
                                    console.log('✅ Activity logging completed successfully');
                                } else {
                                    console.error('❌ logActivity function is not available');
                                }
                            } catch (error) {
                                console.error('❌ Error calling logActivity:', error);
                            }
                            setTimeout(function() {
                                window.location.reload();
                            },7000);
                        })
                        .catch((error) => {
                            console.error('Error deleting document or store data:',error);
                        });
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
    async function deleteStoreData(VendorId) {
        await database.collection('vendor_products').where('vendorID','==',VendorId).get().then(async function(snapshots) {
            if(snapshots.docs.length>0) {
                for(const temData of snapshots.docs) {
                    var item_data=temData.data();
                    await deleteDocumentWithImage('vendor_products',item_data.id,'photo','photos');
                }
            }
        });
        await database.collection('foods_review').where('VendorId','==',VendorId).get().then(async function(snapshotsItem) {
            if(snapshotsItem.docs.length>0) {
                for(const temData of snapshotsItem.docs) {
                    var item_data=temData.data();
                    await deleteDocumentWithImage('foods_review',item_data.id,'profile','photos');
                }
            }
        });
        // 🧠 Smart Coupon Deletion - Preserves Global Coupons
        console.log(`🔍 Starting smart coupon deletion for vendor: ${VendorId}`);
        const couponDeletionResult = await smartDeleteCouponsForVendor(VendorId);
        console.log(`📊 Coupon deletion completed: ${couponDeletionResult.deleted} deleted, ${couponDeletionResult.preserved} preserved`);
        await database.collection('favorite_restaurant').where('restaurant_id','==',VendorId).get().then(async function(snapshotsItem) {
            if(snapshotsItem.docs.length>0) {
                snapshotsItem.docs.forEach((temData) => {
                    var item_data=temData.data();
                    database.collection('favorite_restaurant').doc(item_data.id).delete().then(function() {
                    });
                });
            }
        })
        await database.collection('favorite_item').where('store_id','==',VendorId).get().then(async function(snapshotsItem) {
            if(snapshotsItem.docs.length>0) {
                snapshotsItem.docs.forEach((temData) => {
                    var item_data=temData.data();
                    database.collection('favorite_item').doc(item_data.id).delete().then(function() {
                    });
                });
            }
        })
        await database.collection('payouts').where('vendorID','==',VendorId).get().then(async function(snapshotsItem) {
            if(snapshotsItem.docs.length>0) {
                snapshotsItem.docs.forEach((temData) => {
                    var item_data=temData.data();
                    database.collection('payouts').doc(item_data.id).delete().then(function() {
                    });
                });
            }
        });
        await database.collection('booked_table').where('vendorID','==',VendorId).get().then(async function(snapshotsItem) {
            if(snapshotsItem.docs.length>0) {
                snapshotsItem.docs.forEach((temData) => {
                    var item_data=temData.data();
                    database.collection('booked_table').doc(item_data.id).delete().then(function() {
                    });
                });
            }
        });
        await database.collection('story').where('vendorID','==',VendorId).get().then(async function(snapshotsItem) {
            if(snapshotsItem.docs.length>0) {
                for(const temData of snapshotsItem.docs) {
                    var item_data=temData.data();
                    await deleteDocumentWithImage('story',item_data.vendorID,'videoThumbnail','videoUrl');
                }
            }
        });
    }
    async function deleteUserData(userId,VendorId) {
        await database.collection('wallet').where('user_id','==',userId).get().then(async function(snapshotsItem) {
            if(snapshotsItem.docs.length>0) {
                snapshotsItem.docs.forEach((temData) => {
                    var item_data=temData.data();
                    database.collection('wallet').doc(item_data.id).delete().then(function() {
                    });
                });
            }
        });
        //delete user from mysql
        database.collection('settings').doc("Version").get().then(function(snapshot) {
            var settingData=snapshot.data();
            if(settingData&&settingData.storeUrl) {
                var siteurl=settingData.storeUrl+"/api/delete-user";
                var dataObject={"uuid": userId};
                jQuery.ajax({
                    url: siteurl,
                    method: 'POST',
                    contentType: "application/json; charset=utf-8",
                    data: JSON.stringify(dataObject),
                    success: function(data) {
                        console.log('Delete user from sql success:',data);
                    },
                    error: function(error) {
                        console.log('Delete user from sql error:',error.responseJSON.message);
                    }
                });
            }
        });
        //delete user from authentication
        var dataObject={"data": {"uid": userId}};
        var projectId='<?php echo env('FIREBASE_PROJECT_ID') ?>';
        jQuery.ajax({
            url: 'https://us-central1-'+projectId+'.cloudfunctions.net/deleteUser',
            method: 'POST',
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify(dataObject),
            success: function(data) {
                console.log('Delete user success:',data.result);
                database.collection('users').doc(userId).delete().then(function() {
                });
            },
            error: function(xhr,status,error) {
                var responseText=JSON.parse(xhr.responseText);
                console.log('Delete user error:',responseText.error);
            }
        });
        await deleteDocumentWithImage('vendors',VendorId,['authorProfilePic','photo'],['photos','menuPhotos','restaurantMenuPhotos']);
        const getStoreName=deleteStoreData(VendorId);
        setTimeout(function() {
            window.location.reload();
        },7000);
    }
    $(document).on("click","a[name='vendor-delete']",async function(e) {
        var id=this.id;
        var VendorId=$(this).attr('data-vendorid');
        var vendorName = '';
        try {
            var doc = await database.collection('users').doc(id).get();
            if (doc.exists) {
                var vendorData = doc.data();
                vendorName = (vendorData.firstName || '') + ' ' + (vendorData.lastName || 'Unknown');
            }
        } catch (error) {
            console.error('Error getting vendor name:', error);
        }

        deleteDocumentWithImage('users',VendorId,'profilePictureURL')
            .then(() => {
                return deleteUserData(id,VendorId);
            })
            .then(async () => {
                console.log('✅ Vendor deleted successfully, now logging activity...');
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for vendor deletion...');
                        await logActivity('vendors', 'deleted', 'Deleted vendor: ' + vendorName);
                        console.log('✅ Activity logging completed successfully');
                    } else {
                        console.error('❌ logActivity function is not available');
                    }
                } catch (error) {
                    console.error('❌ Error calling logActivity:', error);
                }
                setTimeout(function() {
                    window.location.reload();
                },7000);
            })
            .catch((error) => {
                console.error('Error deleting document or store data:',error);
            });
    });
    $(document).on("click","input[name='isActive']",async function(e) {
        var ischeck=$(this).is(':checked');
        var id=this.id;
        var vendorName = '';
        try {
            var doc = await database.collection('users').doc(id).get();
            if (doc.exists) {
                var vendorData = doc.data();
                vendorName = (vendorData.firstName || '') + ' ' + (vendorData.lastName || 'Unknown');
            }
        } catch (error) {
            console.error('Error getting vendor name:', error);
        }

        if(ischeck) {
            database.collection('users').doc(id).update({'active': true}).then(async function(result) {
                console.log('✅ Vendor activated successfully, now logging activity...');
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for vendor activation...');
                        await logActivity('vendors', 'activated', 'Activated vendor: ' + vendorName);
                        console.log('✅ Activity logging completed successfully');
                    } else {
                        console.error('❌ logActivity function is not available');
                    }
                } catch (error) {
                    console.error('❌ Error calling logActivity:', error);
                }
            });
        } else {
            database.collection('users').doc(id).update({'active': false}).then(async function(result) {
                console.log('✅ Vendor deactivated successfully, now logging activity...');
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for vendor deactivation...');
                        await logActivity('vendors', 'deactivated', 'Deactivated vendor: ' + vendorName);
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
