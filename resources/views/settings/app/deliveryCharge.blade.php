@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.deliveryCharge')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.deliveryCharge')}}</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div class="error_top"></div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.deliveryCharge')}}</legend>
                        <div class="form-check width-100">
                            <input type="checkbox" class="form-check-inline" id="vendor_can_modify">
                            <label class="col-5 control-label" for="vendor_can_modify">{{ trans('lang.vendor_can_modify')}}</label>
                        </div>
                        <div style="display: none;" class="form-group row width-100 hidden">
                            <label class="col-4 control-label">{{ trans('lang.delivery_charges_per')}} <span
                                    class="global_distance_type"></span></label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="delivery_charges_per_km">
                            </div>
                        </div>
                        <div style="display: none;" class="form-group row width-100 hidden">
                            <label class="col-4 control-label">{{ trans('lang.minimum_delivery_charges')}} </label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="minimum_delivery_charges">
                            </div>
                        </div>
                        <div  style="display: none;" class="form-group row width-100 hidden">
                            <label class="col-4 control-label">{{ trans('lang.minimum_delivery_charges_within')}} <span
                                    class="global_distance_type"></span></label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="minimum_delivery_charges_within_km">
                            </div>
                        </div>

                        <!-- New fields from PDF -->
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Base Delivery Charge (₹)</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="base_delivery_charge" placeholder="23">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Free Delivery Distance (km)</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="free_delivery_distance_km" placeholder="5">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Per KM Charge Above Free Distance (₹)</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="per_km_charge_above_free_distance" placeholder="7">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Item Total Threshold for Free Delivery (₹)</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="item_total_threshold" placeholder="199">
                            </div>
                        </div>

                        <input type="hidden" id="distanceType">
                    </fieldset>
                </div>
            </div>
            <div class="form-group col-12 text-center">
                <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i>
                    {{trans('lang.save')}}</button>
                <a href="{{url('/dashboard')}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
            </div>
        </div>
        @endsection
        @section('scripts')
            <script>
                var database = firebase.firestore();
                var ref_deliverycharge = database.collection('settings').doc("DeliveryCharge");

                $(document).ready(function() {
                    jQuery("#data-table_processing").show();

                    ref_deliverycharge.get().then(async function(snapshots_charge) {
                        var deliveryChargeSettings = snapshots_charge.data();

                        // Create default doc if not exists
                        if (!deliveryChargeSettings) {
                            await database.collection('settings').doc('DeliveryCharge').set({
                                vendor_can_modify: false,
                                delivery_charges_per_km: '',
                                minimum_delivery_charges: '',
                                minimum_delivery_charges_within_km: '',
                                base_delivery_charge: 23,
                                free_delivery_distance_km: 5,
                                per_km_charge_above_free_distance: 7,
                                item_total_threshold: 199
                            });
                            deliveryChargeSettings = {};
                        }

                        jQuery("#data-table_processing").hide();

                        try {
                            // Vendor can modify checkbox
                            if (deliveryChargeSettings.vendor_can_modify) {
                                $("#vendor_can_modify").prop('checked', true);
                            }

                            // Populate only if value exists and is not null/undefined
                            if (deliveryChargeSettings.delivery_charges_per_km !== undefined && deliveryChargeSettings.delivery_charges_per_km !== null && deliveryChargeSettings.delivery_charges_per_km !== '') {
                                $("#delivery_charges_per_km").val(deliveryChargeSettings.delivery_charges_per_km);
                            }
                            if (deliveryChargeSettings.minimum_delivery_charges !== undefined && deliveryChargeSettings.minimum_delivery_charges !== null && deliveryChargeSettings.minimum_delivery_charges !== '') {
                                $("#minimum_delivery_charges").val(deliveryChargeSettings.minimum_delivery_charges);
                            }
                            if (deliveryChargeSettings.minimum_delivery_charges_within_km !== undefined && deliveryChargeSettings.minimum_delivery_charges_within_km !== null && deliveryChargeSettings.minimum_delivery_charges_within_km !== '') {
                                $("#minimum_delivery_charges_within_km").val(deliveryChargeSettings.minimum_delivery_charges_within_km);
                            }

                            // New PDF fields
                            if (deliveryChargeSettings.base_delivery_charge !== undefined && deliveryChargeSettings.base_delivery_charge !== null) {
                                $("#base_delivery_charge").val(deliveryChargeSettings.base_delivery_charge);
                            }
                            if (deliveryChargeSettings.free_delivery_distance_km !== undefined && deliveryChargeSettings.free_delivery_distance_km !== null) {
                                $("#free_delivery_distance_km").val(deliveryChargeSettings.free_delivery_distance_km);
                            }
                            if (deliveryChargeSettings.per_km_charge_above_free_distance !== undefined && deliveryChargeSettings.per_km_charge_above_free_distance !== null) {
                                $("#per_km_charge_above_free_distance").val(deliveryChargeSettings.per_km_charge_above_free_distance);
                            }
                            if (deliveryChargeSettings.item_total_threshold !== undefined && deliveryChargeSettings.item_total_threshold !== null) {
                                $("#item_total_threshold").val(deliveryChargeSettings.item_total_threshold);
                            }
                        } catch(error) {
                            console.error('Error loading delivery charge settings:', error);
                        }
                    });

                    $(".edit-setting-btn").click(function() {
                        var distanceType = $('#distanceType').val();
                        var checkboxValue = $("#vendor_can_modify").is(":checked");

                        var dataToUpdate = {
                            vendor_can_modify: checkboxValue
                        };

                        // Numeric fields only if not empty
                        var delivery_charges_per_km = $("#delivery_charges_per_km").val();
                        if (delivery_charges_per_km !== '') {
                            dataToUpdate.delivery_charges_per_km = parseInt(delivery_charges_per_km);
                        }

                        var minimum_delivery_charges = $("#minimum_delivery_charges").val();
                        if (minimum_delivery_charges !== '') {
                            dataToUpdate.minimum_delivery_charges = parseInt(minimum_delivery_charges);
                        }

                        var minimum_delivery_charges_within_km = $("#minimum_delivery_charges_within_km").val();
                        if (minimum_delivery_charges_within_km !== '') {
                            dataToUpdate.minimum_delivery_charges_within_km = parseInt(minimum_delivery_charges_within_km);
                        }

                        // New PDF fields - only update if not empty
                        var base_delivery_charge = $("#base_delivery_charge").val();
                        if (base_delivery_charge !== '') {
                            dataToUpdate.base_delivery_charge = parseInt(base_delivery_charge);
                        }

                        var free_delivery_distance_km = $("#free_delivery_distance_km").val();
                        if (free_delivery_distance_km !== '') {
                            dataToUpdate.free_delivery_distance_km = parseInt(free_delivery_distance_km);
                        }

                        var per_km_charge_above_free_distance = $("#per_km_charge_above_free_distance").val();
                        if (per_km_charge_above_free_distance !== '') {
                            dataToUpdate.per_km_charge_above_free_distance = parseInt(per_km_charge_above_free_distance);
                        }

                        var item_total_threshold = $("#item_total_threshold").val();
                        if (item_total_threshold !== '') {
                            dataToUpdate.item_total_threshold = parseInt(item_total_threshold);
                        }

                        // Validation for required fields (now only new PDF fields are required)
                        // Old fields are optional and hidden

                        // Keep existing PDF fields intact if not null in Firestore
                        // We'll read existing doc and merge
                        ref_deliverycharge.get().then(doc => {
                            if (doc.exists) {
                                var existing = doc.data();

                                // Preserve fields from PDF if they exist and we're not updating them
                                [
                                    'base_delivery_charge',
                                    'free_delivery_distance_km',
                                    'per_km_charge_above_free_distance',
                                    'item_total_threshold',
                                    'amount'
                                ].forEach(field => {
                                    if (existing[field] !== undefined && existing[field] !== null && dataToUpdate[field] === undefined) {
                                        dataToUpdate[field] = existing[field];
                                    }
                                });

                                database.collection('settings').doc("DeliveryCharge")
                                    .update(dataToUpdate)
                                    .then(function(result) {
                                        window.location.href = '{{ url("settings/app/deliveryCharge")}}';
                                    })
                                    .catch(function(error) {
                                        console.error('Error updating delivery charge settings:', error);
                                        $(".error_top").show();
                                        $(".error_top").html("");
                                        $(".error_top").append("<p>Error updating settings. Please try again.</p>");
                                        window.scrollTo(0,0);
                                    });
                            }
                        });
                    });
                });
            </script>
@endsection
