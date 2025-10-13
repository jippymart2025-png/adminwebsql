@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Mart Banner Items</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">Dashboard</a></li>
                <li class="breadcrumb-item active">Mart Banner Items</li>
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
                        <h3 class="mb-0">Mart Banner Items</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">Mart Banner Items</h3>
                    <p class="mb-0 text-dark-2">Manage mart banner items for the application</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">
                        <a class="btn-primary btn rounded-full" href="{!! route('mart.banners.create') !!}"><i class="mdi mdi-plus mr-2"></i>Create New Banner</a>
                     </div>
                   </div>
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="martBannersTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <?php if (in_array('mart_banners.delete', json_decode(@session('user_permissions'), true))) { ?>
                                        <th class="delete-all">
                                            <input type="checkbox" id="select-all">
                                            <label class="col-3 control-label" for="select-all">
                                                <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                    <i class="mdi mdi-delete"></i> All
                                                </a>
                                            </label>
                                        </th>
                                        <?php } ?>
                                        <th>Title</th>
                                        <th>Position</th>
                                        <th>Zone</th>
                                        <th>Order</th>
                                        <th>Publish</th>
                                        <th>Actions</th>
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
<!-- Load toastr library -->
<script src="{{ asset('js/toastr.js') }}"></script>

<script type="text/javascript">
    var database = firebase.firestore();
    var refData = database.collection('mart_banners').orderBy('set_order');
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function(snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })

    var zoneNames = {};
    // Load zones for display
    database.collection('zone').where('publish', '==', true).get().then(async function(snapshots) {
        snapshots.docs.forEach(doc => {
            zoneNames[doc.id] = doc.data().name;
        });
    });
    var user_permissions = '<?php echo @session("user_permissions")?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('mart_banners.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
    $(document).ready(function() {
        jQuery("#data-table_processing").show();
        const table = $('#martBannersTable').DataTable({
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
                const orderableColumns = (checkDeletePermission) ? ['', 'title', 'position', 'zone', 'set_order', '', ''] : ['title', 'position', 'zone', 'set_order', '', '']; // Ensure this matches the actual column names
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
                        childData.zone = zoneNames[childData.zoneId] || '';
                        if (searchValue) {
                            if (
                                (childData.title && childData.title.toString().toLowerCase().includes(searchValue)) ||
                                (childData.position && childData.position.toString().toLowerCase().includes(searchValue)) ||
                                (childData.zone && childData.zone.toString().toLowerCase().includes(searchValue))
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
                        var route1 = '{{route("mart.banners.edit", ":id")}}';
                        route1 = route1.replace(':id', childData.id);
                        var imageHtml = childData.photo == '' || childData.photo == null ? '<img alt="" width="100%" style="width:70px;height:70px;" src="' + placeholderImage + '" alt="image">' : '<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="" width="100%" style="width:70px;height:70px;" src="' + childData.photo + '" alt="image">'
                        records.push([
                            checkDeletePermission ? '<td class="delete-all"><input type="checkbox" name="record" id="is_open_' + childData.id + '" class="is_open" data-id="' + childData.id + '" style="width:30px;"><label class="col-3 control-label" for="is_open_' + childData.id + '" ></label></td>' : '',
                            imageHtml + '<a href="' + route1 + '">' + (childData.title || '') + '</a>',
                            childData.position || '',
                            childData.zone ? '<span class="badge badge-info">' + childData.zone + '</span>' : '<span class="text-muted">No Zone</span>',
                            childData.set_order || 0,
                            childData.is_publish ? '<label class="switch"><input type="checkbox" checked id="' + childData.id + '" name="isSwitch"><span class="slider round"></span></label>' : '<label class="switch"><input type="checkbox" id="' + childData.id + '" name="isSwitch"><span class="slider round"></span></label>',
                            '<span class="action-btn"><a href="' + route1 + '"><i class="mdi mdi-lead-pencil" title="Edit"></i></a><?php if (in_array('mart_banners.delete', json_decode(@session('user_permissions'), true))) { ?> <a id="'+childData.id+'" name="mart-banner-delete" class="delete-btn" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a><?php } ?></span>'
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
                { targets: (checkDeletePermission) ? [0, 5, 6] : [4, 5], orderable: false }
            ],
            language: {
                zeroRecords: 'No records found',
                emptyTable: 'No records found',
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

    /* Toggle publish action code start */
    $(document).on("click", "input[name='isSwitch']", async function(e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;

        // Get banner item title for logging
        var bannerTitle = '';
        try {
            var doc = await database.collection('mart_banners').doc(id).get();
            if (doc.exists) {
                bannerTitle = doc.data().title;
            }
        } catch (error) {
            console.error('Error getting banner title:', error);
        }

        if (ischeck) {
            database.collection('mart_banners').doc(id).update({
                'is_publish': true
            }).then(async function(result) {
                toastr.success('Banner published successfully');
                // Log activity for banner item publish
                logActivity('mart_banner_items', 'published', 'Published mart banner item: ' + bannerTitle);
            }).catch(function(error) {
                toastr.error('Error publishing banner: ' + error.message);
            });
        } else {
            database.collection('mart_banners').doc(id).update({
                'is_publish': false
            }).then(async function(result) {
                toastr.success('Banner unpublished successfully');
                // Log activity for banner item unpublish
                logActivity('mart_banner_items', 'unpublished', 'Unpublished mart banner item: ' + bannerTitle);
            }).catch(function(error) {
                toastr.error('Error unpublishing banner: ' + error.message);
            });
        }
    });
    /* Toggle publish action code end */

    // Handle select all
    $('#select-all').on('change', function() {
        $('.select-item').prop('checked', $(this).is(':checked'));
    });

    // Handle bulk delete
    $('#deleteAll').on('click', function() {
        var selectedIds = [];
        $('.select-item:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            toastr.warning('Please select items to delete');
            return;
        }

        if (confirm('Are you sure you want to delete ' + selectedIds.length + ' selected banner items?')) {
            // Get all selected banner titles for logging
            var selectedTitles = [];
            var promises = selectedIds.map(function(id) {
                return database.collection('mart_banners').doc(id).get();
            });

            Promise.all(promises).then(function(docs) {
                docs.forEach(function(doc) {
                    if (doc.exists) {
                        selectedTitles.push(doc.data().title);
                    }
                });

                // Delete selected items
                var deletePromises = selectedIds.map(function(id) {
                    return database.collection('mart_banners').doc(id).delete();
                });

                Promise.all(deletePromises).then(function() {
                    toastr.success('Selected banner items deleted successfully');
                    table.ajax.reload();

                    // Log activity for bulk delete
                    logActivity('mart_banner_items', 'deleted', 'Bulk deleted mart banner items: ' + selectedTitles.join(', '));
                }).catch(function(error) {
                    toastr.error('Error deleting banner items: ' + error.message);
                });
            }).catch(function(error) {
                console.error('Error getting banner titles:', error);
            });
        }
    });

    // Handle individual delete
    $(document).on('click', 'a[name="mart-banner-delete"]', function() {
        var id = this.id;

        // Get banner item title for logging
        var bannerTitle = '';
        try {
            database.collection('mart_banners').doc(id).get().then(function(doc) {
                if (doc.exists) {
                    bannerTitle = doc.data().title;
                }
            });
        } catch (error) {
            console.error('Error getting banner title:', error);
        }

        if (confirm('Are you sure you want to delete this banner item?')) {
            database.collection('mart_banners').doc(id).delete().then(function() {
                toastr.success('Banner item deleted successfully');
                table.ajax.reload();

                // Log activity for banner item delete
                logActivity('mart_banner_items', 'deleted', 'Deleted mart banner item: ' + bannerTitle);
            }).catch(function(error) {
                toastr.error('Error deleting banner item: ' + error.message);
            });
        }
    });

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

<!-- Add CSS for the publish toggle switch -->
<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: #2196F3;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }
</style>
@endsection
