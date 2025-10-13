  @extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.surge_rules')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.surge_rules')}}</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div class="error_top"></div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.surge_rules')}}</legend>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Bad Weather</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="bad_weather" placeholder="15">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Rain</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="rain" placeholder="20">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">Summer</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="summer" placeholder="10">
                            </div>
                        </div>
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
                var ref_surge_rules = database.collection('surge_rules').doc('surge_settings');

                $(document).ready(function() {
                    jQuery("#data-table_processing").show();

                    ref_surge_rules.get().then(async function(snapshot) {
                        var surgeRules = snapshot.data();

                        // Create default doc if not exists
                        if (!surgeRules) {
                            await ref_surge_rules.set({
                                bad_weather: 15,
                                rain: 20,
                                summer: 10,
                                created_at: firebase.firestore.FieldValue.serverTimestamp(),
                                updated_at: firebase.firestore.FieldValue.serverTimestamp()
                            });
                            surgeRules = { bad_weather: 15, rain: 20, summer: 10 };
                        }

                        jQuery("#data-table_processing").hide();

                        try {
                            // Populate form fields
                            $("#bad_weather").val(surgeRules.bad_weather ?? '');
                            $("#rain").val(surgeRules.rain ?? '');
                            $("#summer").val(surgeRules.summer ?? '');
                        } catch (error) {
                            console.error('Error loading surge rules:', error);
                        }
                    });

                    $(".edit-setting-btn").click(function() {
                        var badWeather = $("#bad_weather").val();
                        var rain = $("#rain").val();
                        var summer = $("#summer").val();

                        // Validation
                        if (!badWeather) {
                            $(".error_top").show().html("<p>Please enter bad weather.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }
                        if (!rain) {
                            $(".error_top").show().html("<p>Please enter rain charge.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }
                        if (!summer) {
                            $(".error_top").show().html("<p>Please enter per KM charge in summer.</p>");
                            window.scrollTo(0, 0);
                            return;
                        }

                        var dataToUpdate = {
                            bad_weather: parseInt(badWeather),
                            rain: parseInt(rain),
                            summer: parseInt(summer),
                            updated_at: firebase.firestore.FieldValue.serverTimestamp()
                        };

                        ref_surge_rules.update(dataToUpdate, { merge: true })
                            .then(function() {
                                $(".error_top").hide();
                                // alert('Surge rules updated successfully!');
                                window.location.href = '{{ url("settings/app/surgeRules")}}';
                            })
                            .catch(function(error) {
                                console.error('Error updating surge rules:', error);
                                $(".error_top").show().html("<p>Error updating rules. Please try again.</p>");
                                window.scrollTo(0, 0);
                            });
                    });
                });
            </script>
@endsection
