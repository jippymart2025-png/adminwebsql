@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Edit Menu Period</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{{route('menu-periods')}}">Menu Periods</a></li>
                <li class="breadcrumb-item active">Edit Menu Period</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="cat-edite-page max-width-box">
            <div class="card pb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                        <li role="presentation" class="nav-item">
                            <a href="#menu_period_information" aria-controls="description" role="tab" data-toggle="tab"
                               class="nav-link active">Menu Period Information</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="error_top" style="display:none"></div>
                    <div class="success_top" style="display:none"></div>
                    <div class="row restaurant_payout_create" role="tabpanel">
                        <div class="restaurant_payout_create-inner tab-content">
                            <div role="tabpanel" class="tab-pane active" id="menu_period_information">
                                <fieldset>
                                    <legend>Edit Menu Period</legend>
                                    <div class="form-group row width-100">
                                        <label class="col-3 control-label">Label</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control menu-period-label" placeholder="e.g., Breakfast, Lunch, Dinner">
                                            <div class="form-text text-muted">Enter a descriptive label for this meal time period</div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-100">
                                        <label class="col-3 control-label">From Time</label>
                                        <div class="col-7">
                                            <input type="time" class="form-control menu-period-from" required>
                                            <div class="form-text text-muted">Start time for this meal period</div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-100">
                                        <label class="col-3 control-label">To Time</label>
                                        <div class="col-7">
                                            <input type="time" class="form-control menu-period-to" required>
                                            <div class="form-text text-muted">End time for this meal period</div>
                                        </div>
                                    </div>
                                    <div class="form-check row width-100">
                                        <input type="checkbox" class="menu_period_publish" id="menu_period_publish">
                                        <label class="col-3 control-label" for="menu_period_publish">Publish</label>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary save-setting-btn"><i class="fa fa-save"></i>
                        {{trans('lang.save')}}
                    </button>
                    <a href="{{ route('menu-periods') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    var database = firebase.firestore();
    var ref = database.collection('mealTimes');
    var id_menu_period = "{{ $id ?? '' }}";
    var menu_period_length = 1;
    
    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        ref.get().then(async function (snapshots) {
            menu_period_length = snapshots.size + 1;
            jQuery("#data-table_processing").hide();
        })
        
        // Load existing data
        if (id_menu_period) {
            ref.doc(id_menu_period).get().then(function(doc) {
                if (doc.exists) {
                    var data = doc.data();
                    $(".menu-period-label").val(data.label || '');
                    $(".menu-period-from").val(data.from || '');
                    $(".menu-period-to").val(data.to || '');
                    $("#menu_period_publish").prop('checked', data.publish || false);
                }
            }).catch(function(error) {
                console.error("Error loading menu period:", error);
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>Error loading menu period data</p>");
            });
        }
        
        $(".save-setting-btn").click(async function () {
            var label = $(".menu-period-label").val();
            var from = $(".menu-period-from").val();
            var to = $(".menu-period-to").val();
            var publish = $("#menu_period_publish").is(":checked");
            
            if (label == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>Please enter a label for the menu period</p>");
                window.scrollTo(0, 0);
                return false;
            }
            
            if (from == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>Please enter a start time</p>");
                window.scrollTo(0, 0);
                return false;
            }
            
            if (to == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>Please enter an end time</p>");
                window.scrollTo(0, 0);
                return false;
            }
            
            if (from >= to) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>End time must be after start time</p>");
                window.scrollTo(0, 0);
                return false;
            }
            
            jQuery("#data-table_processing").show();
            database.collection('mealTimes').doc(id_menu_period).update({
                'label': label,
                'from': from,
                'to': to,
                'publish': publish,
                'updatedAt': firebase.firestore.FieldValue.serverTimestamp(),
            }).then(async function (result) {
                console.log('✅ Menu period updated successfully, now logging activity...');
                
                // Log the activity with error handling and await the Promise
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for menu period update...');
                        await logActivity('menu-periods', 'updated', 'Updated menu period: ' + label);
                        console.log('✅ Activity logging completed successfully');
                    } else {
                        console.error('❌ logActivity function is not available');
                    }
                } catch (error) {
                    console.error('❌ Error calling logActivity:', error);
                }
                
                jQuery("#data-table_processing").hide();
                window.location.href = '{{ route("menu-periods")}}';
            }).catch(function (error) {
                jQuery("#data-table_processing").hide();
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>Error updating menu period: " + error.message + "</p>");
            });
        });
    });
</script>
@endsection
