@extends('layouts.app')
@section('content')
<style>
    /* .description-cell {
        position: relative;
    }
    .description-cell .text-wrap {
        transition: all 0.3s ease;
    }
    .expand-description {
        padding: 0;
        font-size: 0.8rem;
        color: #007bff;
        text-decoration: none;
    }
    .expand-description:hover {
        color: #0056b3;
        text-decoration: none;
    } */
    .avatar-sm {
        width: 32px;
        height: 32px;
    }
    .avatar-title {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }
    .dt-buttons {
        margin-left: 1rem;
    }
</style>
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Activity Logs</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">Activity Logs</li>
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

    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><i class="mdi mdi-history"></i></span>
                            <h3 class="mb-0">Activity Logs</h3>
                            <span class="counter ml-3" id="logs-count">0</span>
                            <span id="selected-count" class="badge badge-pill badge-primary ml-2 align-self-center" style="display: none;">0 selected</span>
                        </div>
                        <div class="d-flex top-title-right align-self-center">
                            <div class="select-box mr-3">
                                <select id="module-filter" class="form-control">
                                    <option value="">All Modules</option>
                                    <option value="foods">Foods</option>
                                    <option value="orders">Orders</option>
                                    <option value="users">Users/Customers</option>
                                    <option value="vendors">Owners/Vendors</option>
                                    <option value="drivers">Drivers</option>
                                    <option value="categories">Categories</option>
                                    <option value="restaurants">Restaurants</option>
                                    <option value="settings">Settings</option>
                                    <option value="coupons">Coupons</option>
                                    <option value="subscription_plans">Subscription Plans</option>
                                    <option value="notifications">Notifications</option>
                                    <option value="drivers">Drivers</option>
                                    <option value="customers">Customers</option>
                                    <option value="payments">Payments</option>
                                    <option value="reports">Reports</option>
                                    <option value="attributes">Attributes</option>
                                    <option value="documents">Documents</option>
                                    <option value="gift_cards">Gift Cards</option>
                                     <option value="promotions">Promotions</option>
                                     <option value="banner_items">Banner Items</option>
                                     <option value="cms_pages">CMS Pages</option>
                                     <option value="email_templates">Email Templates</option>
                                     <option value="on_boarding">On Boarding</option>
                                     <option value="media">Media</option>
                                     <option value="settings">Settings</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card border">
                    <div class="card-header d-flex justify-content-between align-items-center border-0">
                        <div class="card-header-title">
                            <h3 class="text-dark-2 mb-2 h4">Activity Logs</h3>
                            <p class="mb-0 text-dark-2">Track all user activities across the system</p>
                        </div>
                        <div class="card-header-right d-flex align-items-center">
                            <div class="card-header-btn mr-3">
                                <button class="btn btn-outline-primary rounded-full" id="refresh-logs">
                                    <i class="mdi mdi-refresh mr-2"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive m-t-10">
                            <table id="activityLogsTable"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">
                                <thead>
                                    <tr>                                 
                                        <th>User ID</th>
                                        <th>User Name</th>
                                        <th>User Type</th>
                                        <th>Role</th>
                                        <th>Module</th>
                                        <th>Action</th>
                                        <th>Description</th>
                                        <!-- <th>IP Address</th> -->
                                        <th>Timestamp</th>
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

<script>
// Wait for jQuery to be available
$(document).ready(function() {
    // Firebase configuration
    const firebaseConfig = {
        apiKey: "AIzaSyAf_lICoxPh8qKE1QnVkmQYTFJXKkYmRXU",
        authDomain: "jippymart-27c08.firebaseapp.com",
        projectId: "jippymart-27c08",
        storageBucket: "jippymart-27c08.firebasestorage.app",
        messagingSenderId: "592427852800",
        appId: "1:592427852800:web:f74df8ceb2a4b597d1a4e5",
        measurementId: "G-ZYBQYPZWCF"
    };

    // Initialize Firebase only if not already initialized
    let db;
    try {
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
        db = firebase.firestore();
        console.log('Firebase initialized successfully');
    } catch (error) {
        console.error('Firebase initialization error:', error);
        return;
    }

    let currentModule = '';
    let initialRef = db.collection('activity_logs').orderBy('created_at', 'desc');
    
    // Module filter change
    $('#module-filter').on('change', function() {
        currentModule = $(this).val();
        $('#activityLogsTable').DataTable().ajax.reload();
    });
    
    // Refresh button
    $('#refresh-logs').on('click', function() {
        $('#activityLogsTable').DataTable().ajax.reload();
    });

    // Initialize DataTable with server-side processing
    const table = $('#activityLogsTable').DataTable({
        pageLength: 10,
        processing: false,
        serverSide: true,
        responsive: true,
        ajax: async function(data, callback, settings) {
            const start = data.start;
            const length = data.length;
            const searchValue = data.search.value.toLowerCase();
            const orderColumnIndex = data.order[0].column;
            const orderDirection = data.order[0].dir;

                         const orderableColumns = ['user_name', 'user_type', 'role', 'module', 'action', 'description', 'ip_address', 'timestamp'];
             const orderByField = orderableColumns[orderColumnIndex];

            let ref = initialRef;
        if (currentModule) {
                ref = ref.where('module', '==', currentModule);
            }

            await ref.get().then(async function(querySnapshot) {
                if (querySnapshot.empty) {
                    $('#logs-count').text(0);
                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: []
                    });
                return;
            }
            
                let records = [];
                let filteredRecords = [];

                await Promise.all(querySnapshot.docs.map(async (doc) => {
                    let logData = doc.data();
                    logData.id = doc.id;

                    if (searchValue) {
                        if (
                            (logData.user_id && logData.user_id.toString().toLowerCase().includes(searchValue)) ||
                            (logData.user_name && logData.user_name.toString().toLowerCase().includes(searchValue)) ||
                            (logData.user_type && logData.user_type.toString().toLowerCase().includes(searchValue)) ||
                            (logData.role && logData.role.toString().toLowerCase().includes(searchValue)) ||
                            (logData.module && logData.module.toString().toLowerCase().includes(searchValue)) ||
                            (logData.action && logData.action.toString().toLowerCase().includes(searchValue)) ||
                            (logData.description && logData.description.toString().toLowerCase().includes(searchValue)) ||
                            (logData.ip_address && logData.ip_address.toString().toLowerCase().includes(searchValue))
                        ) {
                            filteredRecords.push(logData);
                        }
                    } else {
                        filteredRecords.push(logData);
                    }
                }));

                // Sort records
                filteredRecords.sort((a, b) => {
                    let aValue = a[orderByField];
                    let bValue = b[orderByField];

                    if (orderByField === 'timestamp') {
                        aValue = a.created_at ? a.created_at.toDate() : new Date(0);
                        bValue = b.created_at ? b.created_at.toDate() : new Date(0);
                    } else {
                        aValue = aValue ? aValue.toString().toLowerCase() : '';
                        bValue = bValue ? bValue.toString().toLowerCase() : '';
                    }

                    if (orderDirection === 'asc') {
                        return aValue > bValue ? 1 : -1;
                    } else {
                        return aValue < bValue ? 1 : -1;
                    }
                });

                const totalRecords = filteredRecords.length;
                $('#logs-count').text(totalRecords);
                const paginatedRecords = filteredRecords.slice(start, start + length);

                await Promise.all(paginatedRecords.map(async (logData) => {
                    const rowData = await buildLogRow(logData);
                    records.push(rowData);
                }));

                callback({
                    draw: data.draw,
                    recordsTotal: totalRecords,
                    recordsFiltered: totalRecords,
                    data: records
                });
            }).catch(function(error) {
                console.error("Error fetching activity logs:", error);
                callback({
                    draw: data.draw,
                    recordsTotal: 0,
                    recordsFiltered: 0,
                    data: []
                });
            });
        },
                 order: [7, 'desc'], // Sort by timestamp descending
         columnDefs: [
             {
                 orderable: false,
                 targets: [] // No non-orderable columns
             }
         ],
        "language": {
            "zeroRecords": "{{trans('lang.no_record_found')}}",
            "emptyTable": "{{trans('lang.no_record_found')}}",
            "processing": ""
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
                        title: 'Activity Logs',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Export PDF',
                        title: 'Activity Logs',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: 'Export CSV',
                        title: 'Activity Logs',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    }
                ]
            }
        ],
        initComplete: function() {
            $(".dataTables_filter").append($(".dt-buttons").detach());
            $('.dataTables_filter input').attr('placeholder', 'Search logs...').attr('autocomplete','new-password').val('');
            $('.dataTables_filter label').contents().filter(function() {
                return this.nodeType === 3;
            }).remove();
        }
    });

    async function buildLogRow(logData) {
        const timestamp = logData.created_at ? new Date(logData.created_at.toDate()).toLocaleString() : 'N/A';

        return [
            // User ID column
            `<div class="d-flex align-items-center">
            <div class="bg-light rounded-circle">
                 <div class="font-weight-bold">${logData.user_id}</div>
                            </div>
            </div>`,

            `<div class="d-flex align-items-center">
               <span class="avatar-sm mr-3">
                                        <i class="mdi mdi-account"></i>
                <span class="font-weight-bold">${logData.user_name || 'Unknown User'}</span>
               </span>
                </div>`,

            // User Type column
            `<span class="badge badge-${getUserTypeBadge(logData.user_type)}">${logData.user_type}</span>`,

            // Role column
            `<span class="badge badge-info">${logData.role}</span>`,

            // Module column
            `<span class="badge badge-secondary">${logData.module}</span>`,

            // Action column
            `<span class="badge badge-${getActionBadge(logData.action)}">${logData.action}</span>`,

            // Description column with content
                `<span class="text-wrap" style="max-width: 500px;">
                    ${logData.description}
                </span>`,

            // IP Address column
            // `<small class="font-weight-semibold">${logData.ip_address}</small>`,

            // Timestamp column
            `<span class="font-weight-semibold">${timestamp}</span>`
        ];
    }

    function getUserTypeBadge(userType) {
        switch(userType) {
            case 'admin': return 'primary';
            case 'merchant': return 'success';
            case 'driver': return 'warning';
            case 'customer': return 'info';
            default: return 'secondary';
        }
    }

    function getActionBadge(action) {
        switch(action) {
            case 'created': return 'success';
            case 'updated': return 'warning';
            case 'deleted': return 'danger';
            case 'viewed': return 'info';
            default: return 'secondary';
        }
    }

    // Handle expandable description
    $(document).on('click', '.expand-description', function() {
        const button = $(this);
        const description = button.data('description');
        const cell = button.closest('.description-cell');
        const textDiv = cell.find('.text-wrap');

        if (button.find('i').hasClass('mdi-plus')) {
            // Expand
            textDiv.css({
                'max-width': 'none',
                'overflow': 'visible',
                'text-overflow': 'unset'
            });
            button.find('i').removeClass('mdi-plus').addClass('mdi-minus');
            button.html('<i class="mdi mdi-minus"></i> Show Less');
        } else {
            // Collapse
            textDiv.css({
                'max-width': '200px',
                'overflow': 'hidden',
                'text-overflow': 'ellipsis'
            });
            button.find('i').removeClass('mdi-minus').addClass('mdi-plus');
            button.html('<i class="mdi mdi-plus"></i> Show More');
        }
    });
});
</script>
@endsection
