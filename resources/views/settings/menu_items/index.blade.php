@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.menu_items')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.menu_items_table')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/banner.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.menu_items')}}</h3>
                        <span class="counter ml-3 total_count"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                        <div class="select-box pl-3">
                           
                        </div>
                    </div>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.menu_items')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.banner_items_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">
                        <select id="zoneFilter" class="form-control" style="width: 200px;">
                            <option value="">All Zones</option>
                        </select>
                    </div>
                    <div class="card-header-btn mr-3">
                        <?php if (in_array('banners.create', json_decode(@session('user_permissions'), true))) { ?>
                        <a class="btn-primary btn rounded-full" href="{!! route('setting.banners.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.menu_items_create')}}</a>
                        <?php } ?>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="bannerItemsTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <?php if (in_array('banners.delete', json_decode(@session('user_permissions'), true))) { ?>
                                        <th class="delete-all">
                                            <input type="checkbox" id="select-all">
                                            <label class="col-3 control-label" for="select-all">
                                                <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                    <i class="mdi mdi-delete"></i> {{trans('lang.all')}}
                                                </a>
                                            </label>
                                        </th>
                                        <?php } ?>
                                        <th>{{trans('lang.title')}}</th>
                                        <th>{{trans('lang.banner_position')}}</th>
                                        <th>Zone</th>
                                        <th>{{trans('lang.publish')}}</th>
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
    var refData = database.collection('menu_items').orderBy('title');
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    var zoneFilter = '';
    var zonesMap = {};
    
    placeholder.get().then(async function(snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })
    
    // Load zones for filter
    function loadZones() {
        $('#zoneFilter').html('<option value="">All Zones</option>');
        database.collection('zone').where('publish', '==', true).orderBy('name', 'asc').get().then(function(snapshots) {
            snapshots.docs.forEach(function(doc) {
                var data = doc.data();
                zonesMap[doc.id] = data.name;
                $('#zoneFilter').append($("<option></option>")
                    .attr("value", doc.id)
                    .text(data.name));
            });
        }).catch(function(error) {
            console.error('Error loading zones:', error);
        });
    }
    var user_permissions = '<?php echo @session("user_permissions")?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('banners.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
    $(document).ready(function() {
        // Load zones first
        loadZones();
        
        // Zone filter change handler
        $('#zoneFilter').on('change', function() {
            zoneFilter = $(this).val();
            table.ajax.reload();
        });
        
        jQuery("#data-table_processing").show();
        const table = $('#bannerItemsTable').DataTable({
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
                const orderableColumns =(checkDeletePermission) ? ['',  'title', 'position', 'zoneTitle', '', ''] : [ 'title', 'position', 'zoneTitle', '', '']; // Ensure this matches the actual column names
                const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }
                refData.get().then(async function (querySnapshot) {
                    if (querySnapshot.empty) {
                        $('.total_count').text(0); 
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
                    querySnapshot.forEach(function (doc) {
                        let childData = doc.data();
                        childData.id = doc.id; // Ensure the document ID is included in the data
                        
                        // Apply zone filter
                        if (zoneFilter && childData.zoneId !== zoneFilter) {
                            return; // Skip this record if zone filter doesn't match
                        }
                        
                        if (searchValue) {
                            if (
                                (childData.title && childData.title.toString().toLowerCase().includes(searchValue)) ||
                                (childData.position && childData.position.toString().toLowerCase().includes(searchValue)) ||
                                (childData.zoneTitle && childData.zoneTitle.toString().toLowerCase().includes(searchValue))
                            ) {
                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    });
                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase() : '';
                        let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase() : '';
                        if (orderDirection === 'asc') {
                            return (aValue > bValue) ? 1 : -1;
                        } else {
                            return (aValue < bValue) ? 1 : -1;
                        }
                    });
                    const totalRecords = filteredRecords.length;
                    $('.total_count').text(totalRecords); 
                    const paginatedRecords = filteredRecords.slice(start, start + length);
                    paginatedRecords.forEach(function (childData) {
                        var route1 = '{{route("setting.banners.edit", ":id")}}';
                        route1 = route1.replace(':id', childData.id);
                        var imageHtml=childData.photo == '' || childData.photo == null ? '<img alt="" width="100%" style="width:70px;height:70px;" src="' + placeholderImage + '" alt="image">' : '<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="" width="100%" style="width:70px;height:70px;" src="' + childData.photo + '" alt="image">'
                        records.push([
                            checkDeletePermission ? '<td class="delete-all"><input type="checkbox" name="record" id="is_open_' + childData.id + '" class="is_open" data-id="' + childData.id + '" style="width:30px;"><label class="col-3 control-label"\n' + 'for="is_open_' + childData.id + '" ></label></td>' : '',
                            imageHtml+'<a href="' + route1 + '">' + childData.title + '</a>',
                            childData.position,
                            childData.zoneTitle || 'No Zone',
                            childData.is_publish ? '<label class="switch"><input type="checkbox" checked id="' + childData.id + '" name="isSwitch"><span class="slider round"></span></label>' : '<label class="switch"><input type="checkbox" id="' + childData.id + '" name="isSwitch"><span class="slider round"></span></label>',
                            '<span class="action-btn"><?php if (in_array('banners.edit', json_decode(@session('user_permissions'), true))) { ?><a href="' + route1 + '"><i class="mdi mdi-lead-pencil" title="Edit"></i></a><?php } ?><?php if (in_array('banners.delete', json_decode(@session('user_permissions'), true))) { ?> <a id="' + childData.id + '" name="vendor-delete" class="delete-btn" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a><?php } ?></span>'
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
            order: (checkDeletePermission) ? [1, 'asc'] : [0, 'asc'],
            columnDefs: [
                { targets: (checkDeletePermission) ? [0, 4, 5] : [3, 4], orderable: false }
            ],
            language: {
                zeroRecords: '{{trans("lang.no_record_found")}}',
                emptyTable: '{{trans("lang.no_record_found")}}',
                "processing": '',
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
    })
    /* toggal publish action code start*/
    $(document).on("click", "input[name='isSwitch']", async function(e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        
        // Get banner item title for logging
        var bannerTitle = '';
        try {
            var doc = await database.collection('menu_items').doc(id).get();
            if (doc.exists) {
                bannerTitle = doc.data().title;
            }
        } catch (error) {
            console.error('Error getting banner title:', error);
        }
        
        if (ischeck) {
            database.collection('menu_items').doc(id).update({
                'is_publish': true
            }).then(async function(result) {
                await logActivity('banner_items', 'published', 'Published banner item: ' + bannerTitle);
            });
        } else {
            database.collection('menu_items').doc(id).update({
                'is_publish': false
            }).then(async function(result) {
                await logActivity('banner_items', 'unpublished', 'Unpublished banner item: ' + bannerTitle);
            });
        }
    });
    /*toggal publish action code end*/
    $('#select-all').change(function() {
        var isChecked = $(this).prop('checked');
        $('input[type="checkbox"][name="record"]').prop('checked', isChecked);
    });
    $('#deleteAll').click(async function() {
        if ($('#bannerItemsTable .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                
                // Get all selected banner titles for logging
                var selectedTitles = [];
                for (var i = 0; i < $('input[type="checkbox"][name="record"]:checked').length; i++) {
                    var id = $('input[type="checkbox"][name="record"]:checked').eq(i).attr('data-id');
                    try {
                        var doc = await database.collection('menu_items').doc(id).get();
                        if (doc.exists) {
                            selectedTitles.push(doc.data().title);
                        }
                    } catch (error) {
                        console.error('Error getting banner title:', error);
                    }
                }
                
                // Loop through all selected records and delete them
                $('input[type="checkbox"][name="record"]:checked').each(async function() {
                    var id = $(this).attr('data-id');
                    // Perform deletion of record with id
                    await deleteDocumentWithImage('menu_items',id,'photo');
                });
                
                // Log bulk delete activity
                await logActivity('banner_items', 'deleted', 'Bulk deleted banner items: ' + selectedTitles.join(', '));
                
                // Reload the page or refresh the data as needed
                window.location.reload();
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
    $(document).on("click", "a[name='vendor-delete']", async function(e) {
        var id = this.id;
        
        // Get banner item title for logging
        var bannerTitle = '';
        try {
            var doc = await database.collection('menu_items').doc(id).get();
            if (doc.exists) {
                bannerTitle = doc.data().title;
            }
        } catch (error) {
            console.error('Error getting banner title:', error);
        }
        
        await deleteDocumentWithImage('menu_items',id,'photo');
        await logActivity('banner_items', 'deleted', 'Deleted banner item: ' + bannerTitle);
        window.location.reload();
    });
</script>
@endsection
