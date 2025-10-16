@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.zone_bonus_settings')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{{url('/settings')}}">{{trans('lang.settings')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.zone_bonus_settings')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="error_top" style="display:none"></div>
                        <div class="row">
                            <div class="col-md-6">
                                <h4>{{trans('lang.zone_bonus_configuration')}}</h4>
                                <p class="text-muted">{{trans('lang.zone_bonus_configuration_help')}}</p>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addZoneBonusModal">
                                    <i class="fa fa-plus"></i> {{trans('lang.add_zone_bonus')}}
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive mt-4">
                            <table class="table table-striped" id="zoneBonusTable">
                                <thead>
                                    <tr>
                                        <th>{{trans('lang.zone_name')}}</th>
                                        <th>{{trans('lang.required_orders')}}</th>
                                        <th>{{trans('lang.bonus_amount')}}</th>
                                        <th>{{trans('lang.status')}}</th>
                                        <th>{{trans('lang.created_at')}}</th>
                                        <th>{{trans('lang.actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody id="zoneBonusTableBody">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Zone Bonus Modal -->
<div class="modal fade" id="addZoneBonusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{trans('lang.add_zone_bonus')}}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addZoneBonusForm">
                    <div class="form-group">
                        <label>{{trans('lang.select_zone')}}</label>
                        <select class="form-control" id="zoneSelect" required>
                            <option value="">{{trans('lang.select_zone')}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{trans('lang.required_orders_for_bonus')}}</label>
                        <input type="number" class="form-control" id="requiredOrders" min="1" max="50" required>
                        <small class="form-text text-muted">{{trans('lang.required_orders_help')}}</small>
                    </div>
                    <div class="form-group">
                        <label>{{trans('lang.bonus_amount')}}</label>
                        <input type="number" class="form-control" id="bonusAmount" min="1" step="0.01" required>
                        <small class="form-text text-muted">{{trans('lang.bonus_amount_help')}}</small>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="isActive" checked>
                            <label class="form-check-label" for="isActive">{{trans('lang.active')}}</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans('lang.cancel')}}</button>
                <button type="button" class="btn btn-primary" id="saveZoneBonus">{{trans('lang.save')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Zone Bonus Modal -->
<div class="modal fade" id="editZoneBonusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{trans('lang.edit_zone_bonus')}}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editZoneBonusForm">
                    <input type="hidden" id="editZoneBonusId">
                    <div class="form-group">
                        <label>{{trans('lang.zone_name')}}</label>
                        <input type="text" class="form-control" id="editZoneName" readonly>
                    </div>
                    <div class="form-group">
                        <label>{{trans('lang.required_orders_for_bonus')}}</label>
                        <input type="number" class="form-control" id="editRequiredOrders" min="1" max="50" required>
                    </div>
                    <div class="form-group">
                        <label>{{trans('lang.bonus_amount')}}</label>
                        <input type="number" class="form-control" id="editBonusAmount" min="1" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="editIsActive">
                            <label class="form-check-label" for="editIsActive">{{trans('lang.active')}}</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans('lang.cancel')}}</button>
                <button type="button" class="btn btn-primary" id="updateZoneBonus">{{trans('lang.update')}}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
var database = firebase.firestore();
var zones = [];
var zoneBonusSettings = [];

$(document).ready(function() {
    loadZones();
    loadZoneBonusSettings();
    
    // Save zone bonus
    $('#saveZoneBonus').click(function() {
        saveZoneBonus();
    });
    
    // Update zone bonus
    $('#updateZoneBonus').click(function() {
        updateZoneBonus();
    });
});

// Load zones for dropdown
function loadZones() {
    database.collection('zone').where('publish', '==', true).orderBy('name', 'asc').get()
        .then(function(snapshots) {
            snapshots.docs.forEach(function(doc) {
                var data = doc.data();
                zones.push(data);
                $('#zoneSelect').append($('<option></option>')
                    .attr('value', data.id)
                    .text(data.name));
            });
        })
        .catch(function(error) {
            console.error('Error loading zones:', error);
            showError('Error loading zones');
        });
}

// Load zone bonus settings
function loadZoneBonusSettings() {
    database.collection('zone_bonus_settings').orderBy('zoneName', 'asc').get()
        .then(function(snapshots) {
            zoneBonusSettings = [];
            $('#zoneBonusTableBody').empty();
            
            snapshots.docs.forEach(function(doc) {
                var data = doc.data();
                data.id = doc.id;
                zoneBonusSettings.push(data);
                addZoneBonusRow(data);
            });
        })
        .catch(function(error) {
            console.error('Error loading zone bonus settings:', error);
            showError('Error loading zone bonus settings');
        });
}

// Add zone bonus row to table
function addZoneBonusRow(data) {
    var statusBadge = data.isActive ? 
        '<span class="badge badge-success">Active</span>' : 
        '<span class="badge badge-secondary">Inactive</span>';
    
    var row = '<tr data-id="' + data.id + '">' +
        '<td>' + data.zoneName + '</td>' +
        '<td>' + data.requiredOrdersForBonus + '</td>' +
        '<td>₹' + data.bonusAmount + '</td>' +
        '<td>' + statusBadge + '</td>' +
        '<td>' + formatDate(data.createdAt) + '</td>' +
        '<td>' +
            '<button class="btn btn-sm btn-primary edit-zone-bonus" data-id="' + data.id + '">' +
                '<i class="fa fa-edit"></i> Edit' +
            '</button> ' +
            '<button class="btn btn-sm btn-danger delete-zone-bonus" data-id="' + data.id + '">' +
                '<i class="fa fa-trash"></i> Delete' +
            '</button>' +
        '</td>' +
    '</tr>';
    
    $('#zoneBonusTableBody').append(row);
}

// Save zone bonus
function saveZoneBonus() {
    var zoneId = $('#zoneSelect').val();
    var requiredOrders = parseInt($('#requiredOrders').val());
    var bonusAmount = parseFloat($('#bonusAmount').val());
    var isActive = $('#isActive').is(':checked');
    
    if (!zoneId || !requiredOrders || !bonusAmount) {
        showError('Please fill all required fields');
        return;
    }
    
    // Check if zone already has bonus settings
    var existingSetting = zoneBonusSettings.find(function(setting) {
        return setting.zoneId === zoneId;
    });
    
    if (existingSetting) {
        showError('Bonus settings already exist for this zone');
        return;
    }
    
    // Get zone name
    var zone = zones.find(function(z) {
        return z.id === zoneId;
    });
    
    if (!zone) {
        showError('Zone not found');
        return;
    }
    
    var bonusId = database.collection('temp').doc().id;
    var bonusData = {
        id: bonusId,
        zoneId: zoneId,
        zoneName: zone.name,
        requiredOrdersForBonus: requiredOrders,
        bonusAmount: bonusAmount,
        isActive: isActive,
        createdAt: firebase.firestore.FieldValue.serverTimestamp(),
        updatedAt: firebase.firestore.FieldValue.serverTimestamp()
    };
    
    database.collection('zone_bonus_settings').doc(bonusId).set(bonusData)
        .then(function() {
            $('#addZoneBonusModal').modal('hide');
            $('#addZoneBonusForm')[0].reset();
            loadZoneBonusSettings();
            showSuccess('Zone bonus settings saved successfully');
        })
        .catch(function(error) {
            console.error('Error saving zone bonus:', error);
            showError('Error saving zone bonus settings');
        });
}

// Edit zone bonus
$(document).on('click', '.edit-zone-bonus', function() {
    var id = $(this).data('id');
    var setting = zoneBonusSettings.find(function(s) {
        return s.id === id;
    });
    
    if (setting) {
        $('#editZoneBonusId').val(setting.id);
        $('#editZoneName').val(setting.zoneName);
        $('#editRequiredOrders').val(setting.requiredOrdersForBonus);
        $('#editBonusAmount').val(setting.bonusAmount);
        $('#editIsActive').prop('checked', setting.isActive);
        $('#editZoneBonusModal').modal('show');
    }
});

// Update zone bonus
function updateZoneBonus() {
    var id = $('#editZoneBonusId').val();
    var requiredOrders = parseInt($('#editRequiredOrders').val());
    var bonusAmount = parseFloat($('#editBonusAmount').val());
    var isActive = $('#editIsActive').is(':checked');
    
    if (!requiredOrders || !bonusAmount) {
        showError('Please fill all required fields');
        return;
    }
    
    var updateData = {
        requiredOrdersForBonus: requiredOrders,
        bonusAmount: bonusAmount,
        isActive: isActive,
        updatedAt: firebase.firestore.FieldValue.serverTimestamp()
    };
    
    database.collection('zone_bonus_settings').doc(id).update(updateData)
        .then(function() {
            $('#editZoneBonusModal').modal('hide');
            loadZoneBonusSettings();
            showSuccess('Zone bonus settings updated successfully');
        })
        .catch(function(error) {
            console.error('Error updating zone bonus:', error);
            showError('Error updating zone bonus settings');
        });
}

// Delete zone bonus
$(document).on('click', '.delete-zone-bonus', function() {
    var id = $(this).data('id');
    var setting = zoneBonusSettings.find(function(s) {
        return s.id === id;
    });
    
    if (confirm('Are you sure you want to delete bonus settings for ' + setting.zoneName + '?')) {
        database.collection('zone_bonus_settings').doc(id).delete()
            .then(function() {
                loadZoneBonusSettings();
                showSuccess('Zone bonus settings deleted successfully');
            })
            .catch(function(error) {
                console.error('Error deleting zone bonus:', error);
                showError('Error deleting zone bonus settings');
            });
    }
});

// Utility functions
function formatDate(timestamp) {
    if (!timestamp) return 'N/A';
    var date = timestamp.toDate ? timestamp.toDate() : new Date(timestamp);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function showError(message) {
    $('.error_top').show().html('<div class="alert alert-danger">' + message + '</div>');
    setTimeout(function() {
        $('.error_top').hide();
    }, 5000);
}

function showSuccess(message) {
    $('.error_top').show().html('<div class="alert alert-success">' + message + '</div>');
    setTimeout(function() {
        $('.error_top').hide();
    }, 3000);
}
</script>
@endsection


