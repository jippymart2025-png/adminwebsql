@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Menu Periods</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item">Menu Periods</li>
                <li class="breadcrumb-item active">Menu Periods Table</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/restaurant.png') }}"></span>
                        <h3 class="mb-0">Menu Periods</h3>
                        <span class="counter ml-3 menu_period_count"></span>
                    </div>
                </div>
            </div>
        </div> 
        <div class="row">
            <div class="col-12">
                <div class="card border">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card card-box-with-icon bg--1">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                       <div class="card-box-with-content">
                                        <h4 class="text-dark-2 mb-1 h4 menu_period_count">00</h4>
                                        <p class="mb-0 small text-dark-2">Total Menu Periods</p>
                                       </div>
                                        <span class="box-icon ab"><img src="{{ asset('images/restaurant_icon.png') }}"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       </div>
    
    @if(session('success'))
        <div class="alert alert-success">{!! session('success') !!}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{!! session('error') !!}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{!! $error !!}</li>
                @endforeach
            </ul>
        </div>
    @endif
       <div class="table-list">
        <div class="row">
            <div class="col-12">
                <div class="card border">
                    <div class="card-header d-flex justify-content-between align-items-center border-0">
                    <div class="card-header-title">
                        <h3 class="text-dark-2 mb-2 h4">Menu Periods Table</h3>
                        <p class="mb-0 text-dark-2">Manage meal time periods for restaurants</p>
                    </div>
                    <div class="card-header-right d-flex align-items-center">
                        <div class="card-header-btn mr-3"> 
                        <a href="{!! route('menu-periods.create') !!}" class="btn-primary btn rounded-full"><i class="mdi mdi-plus mr-2"></i>Create Menu Period</a>
                        </div>
                    </div>                
                    </div>
                    <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="menuPeriodsTable"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> All</a></label></th>
                                            <th class="text-center">Menu Period Info</th>
                                            <th class="text-center">From Time</th>
                                            <th class="text-center">To Time</th>
                                            <th class="text-center">Date</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_menu_periods"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="data-table_processing" class="data-table-processing" style="display: none">Processing...</div>
@endsection
@section('scripts')
<style>
.table-responsive {
    overflow-x: auto;
}
#menuPeriodsTable {
    width: 100% !important;
}
#menuPeriodsTable td {
    white-space: nowrap;
    vertical-align: middle;
}
#menuPeriodsTable .delete-all {
    width: 80px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}
#menuPeriodsTable .delete-all input[type="checkbox"] {
    margin: 0;
}
#menuPeriodsTable .delete-all .expand-row {
    margin: 0;
}
#menuPeriodsTable th:nth-child(2) {
    width: 200px;
}
#menuPeriodsTable th:nth-child(3) {
    width: 150px;
}
#menuPeriodsTable th:nth-child(4) {
    width: 150px;
}
#menuPeriodsTable th:nth-child(5) {
    width: 150px;
}
#menuPeriodsTable th:nth-child(6) {
    width: 100px;
}
.action-btn {
    white-space: nowrap;
}
</style>
<script type="text/javascript">
    var database = firebase.firestore();
    var refData = database.collection('mealTimes');
    var selectedMenuPeriods = new Set();
    
    var append_list = '';
    var user_permissions = '<?php echo @session("user_permissions") ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('menu-periods.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
    
    $(document).ready(function () {
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
                { key: 'id', header: "ID" },
                { key: 'label', header: "Label" },
                { key: 'from', header: "From Time" },
                { key: 'to', header: "To Time" },
                { key: 'createdAt', header: "{{trans('lang.created_at')}}" },
            ],
            fileName: "Menu Periods",
        };
        var table = $('#menuPeriodsTable').DataTable({
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
                const orderableColumns = ['', 'label', 'from', 'to', 'createdAt', ''];
                const orderByField = orderableColumns[orderColumnIndex];
                
                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }
                
                database.collection('mealTimes').orderBy('label').get().then(async function(querySnapshot) {
                    if (querySnapshot.empty) {
                        $('.menu_period_count').text(0);
                        $('#data-table_processing').hide();
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
                    
                    querySnapshot.forEach(function(doc) {
                        var d = doc.data();
                        d.id = doc.id;
                        
                        if (searchValue) {
                            var date = '';
                            var time = '';
                            if (d.hasOwnProperty("createdAt")) {
                                try {
                                    date = d.createdAt.toDate().toDateString();
                                    time = d.createdAt.toDate().toLocaleTimeString('en-US');
                                } catch (err) {}
                            }
                            var createdAt = date + ' ' + time;
                            if (
                                (d.label && d.label.toString().toLowerCase().includes(searchValue)) ||
                                (d.from && d.from.toString().toLowerCase().includes(searchValue)) ||
                                (d.to && d.to.toString().toLowerCase().includes(searchValue)) ||
                                (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1)
                            ) {
                                filteredRecords.push(d);
                            }
                        } else {
                            filteredRecords.push(d);
                        }
                    });
                    
                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase() : '';
                        let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase() : '';
                        
                        if (orderByField === 'createdAt') {
                            try {
                                aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                                bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                            } catch (err) {}
                        }
                        
                        if (orderDirection === 'asc') {
                            return (aValue > bValue) ? 1 : -1;
                        } else {
                            return (aValue < bValue) ? 1 : -1;
                        }
                    });
                    
                    const totalRecords = filteredRecords.length;
                    $('.menu_period_count').text(totalRecords);
                    
                    const paginatedRecords = filteredRecords.slice(start, start + length);
                    
                    await Promise.all(paginatedRecords.map(async (menuPeriodData) => {
                        var getData = await buildHTML(menuPeriodData);
                        records.push(getData);
                    }));
                    
                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords,
                        recordsFiltered: totalRecords,
                        data: records
                    });
                });
            },
            order: [1, 'asc'],
            columnDefs: [
                {orderable: false, targets: [0, 5]}
            ],
            "language": {
                "zeroRecords": "No record found",
                "emptyTable": "No record found",
                "processing": ""
            }
        });
    });
    
    function formatExpandRow(data) {
        return `
            <div class="p-2">
                <strong>Label:</strong> <span class="text-monospace">${data.label || ''}</span><br>
                <strong>From Time:</strong> <span class="text-monospace">${data.from || ''}</span><br>
                <strong>To Time:</strong> <span class="text-monospace">${data.to || ''}</span><br>
                <strong>Publish:</strong> <span class="text-monospace">${data.publish ? 'Yes' : 'No'}</span>
            </div>
        `;
    }

    async function buildHTML(val) {
        var html = [];
        var id = val.id;
        var route1 = '{{route("menu-periods.edit",":id")}}';
        route1 = route1.replace(':id', id);
        
        // Checkbox column with expand button - same structure as media
        html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label" for="is_open_' + id + '" ></label><button class="expand-row" data-id="' + id + '" tabindex="-1" style="width: 18px; height: 18px; border-radius: 50%; background-color: #28a745; border: 2px solid #ffffff; display: inline-flex; align-items: center; justify-content: center; padding: 0; margin-left: 5px; position: relative; z-index: 1;"><i class="fa fa-plus" style="color: white; font-size: 8px;"></i></button></td>');
        
        // Label column - same structure as media
        var labelInfo = '';
        if(val.label != " " && val.label != "null" && val.label != null && val.label != ""){
            labelInfo += '<a href="' + route1 + '">' + val.label + '</a>';
        }else{
            labelInfo += 'UNKNOWN';
        }
        html.push(labelInfo);
        
        // From Time column
        html.push('<span class="badge badge-info">' + (val.from || 'N/A') + '</span>');
        
        // To Time column
        html.push('<span class="badge badge-success">' + (val.to || 'N/A') + '</span>');
        
        // Date column
        var date = '';
        var time = '';
        if (val.hasOwnProperty("createdAt")) {
            try {
                date = val.createdAt.toDate().toDateString();
                time = val.createdAt.toDate().toLocaleTimeString('en-US');
            } catch (err) {
            }
            html.push('<span class="dt-time">' + date + ' ' + time + '</span>');
        } else {
            html.push('');
        }
        
        // Actions column - same structure as media
        var actionHtml = '<span class="action-btn">';
        actionHtml += '<a href="' + route1 + '"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>';
        actionHtml += '<a id="' + id + '" name="menu-period-delete" href="javascript:void(0)" class="delete-btn"><i class="mdi mdi-delete" title="Delete"></i></a>';
        actionHtml += '</span>';
        html.push(actionHtml);
        
        return html;
    }
    
    // Select all logic
    $("#is_active").click(function () {
        $("#menuPeriodsTable .is_open").prop('checked', $(this).prop('checked'));
    });

    // Row checkbox logic
    $('#menuPeriodsTable tbody').on('change', '.is_open', function () {
        var id = $(this).attr('dataId');
        if (this.checked) {
            selectedMenuPeriods.add(id);
        } else {
            selectedMenuPeriods.delete(id);
        }
        $('#is_active').prop('checked', $('.is_open:checked').length === $('.is_open').length);
    });

    // Expand/collapse row
    $('#menuPeriodsTable tbody').on('click', '.expand-row', function (e) {
        e.preventDefault();
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        var id = $(this).data('id');
        var icon = $(this).find('i');
        
        // Get the menu period data for this row
        database.collection('mealTimes').doc(id).get().then(function(doc) {
            if (doc.exists) {
                var menuPeriodData = doc.data();
                if (row.child.isShown()) {
                    row.child.hide();
                    icon.removeClass('fa-minus text-danger').addClass('fa-plus text-success');
                    $(this).css('background-color', '#28a745');
                } else {
                    row.child(formatExpandRow(menuPeriodData)).show();
                    icon.removeClass('fa-plus text-success').addClass('fa-minus text-danger');
                    $(this).css('background-color', '#dc3545');
                }
            }
        });
    });

    // Single delete
    $('#menuPeriodsTable tbody').on('click', '.delete-btn', async function () {
        var id = $(this).attr('id');
        if (confirm('Are you sure you want to delete this menu period?')) {
            jQuery('#data-table_processing').show();
            
            // Get menu period name for logging
            var menuPeriodName = '';
            try {
                var doc = await database.collection('mealTimes').doc(id).get();
                if (doc.exists) {
                    menuPeriodName = doc.data().label;
                }
            } catch (error) {
                console.error('Error getting menu period name:', error);
            }
            
            database.collection('mealTimes').doc(id).delete().then(async function () {
                await logActivity('menu-periods', 'deleted', 'Deleted menu period: ' + menuPeriodName);
                selectedMenuPeriods.delete(id);
                table.ajax.reload();
                jQuery('#data-table_processing').hide();
            });
        }
    });

    // Bulk delete
    $("#deleteAll").click(async function () {
        if ($('#menuPeriodsTable .is_open:checked').length) {
            if (confirm("Delete selected menu periods?")) {
                jQuery('#data-table_processing').show();
                
                // Get all selected menu period names for logging
                var selectedNames = [];
                for (var i = 0; i < $('#menuPeriodsTable .is_open:checked').length; i++) {
                    var id = $('#menuPeriodsTable .is_open:checked').eq(i).attr('dataId');
                    try {
                        var doc = await database.collection('mealTimes').doc(id).get();
                        if (doc.exists) {
                            selectedNames.push(doc.data().label);
                        }
                    } catch (error) {
                        console.error('Error getting menu period name:', error);
                    }
                }
                
                $('#menuPeriodsTable .is_open:checked').each(function () {
                    var id = $(this).attr('dataId');
                    database.collection('mealTimes').doc(id).delete();
                    selectedMenuPeriods.delete(id);
                });
                
                // Log bulk delete activity
                await logActivity('menu-periods', 'deleted', 'Bulk deleted menu periods: ' + selectedNames.join(', '));
                
                setTimeout(function () {
                    table.ajax.reload();
                    jQuery('#data-table_processing').hide();
                }, 500);
            }
        } else {
            alert("Select at least one menu period to delete.");
        }
    });
</script>
@endsection
