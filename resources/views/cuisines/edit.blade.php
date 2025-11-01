@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{trans('lang.cuisines_plural')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a
                                href="{!! route('cuisines') !!}">{{trans('lang.cuisines_plural')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.cuisines_edit')}}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="cat-edite-page max-width-box">
                <div class="card  pb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li role="presentation" class="nav-item">
                                <a href="#cuisines_information" aria-controls="description" role="tab" data-toggle="tab"
                                   class="nav-link active">{{trans('lang.cuisines_information')}}</a>
                            </li>
                            <li role="presentation" class="nav-item">
                                <a href="#review_attributes" aria-controls="review_attributes" role="tab"
                                   data-toggle="tab"
                                   class="nav-link">{{trans('lang.reviewattribute_plural')}}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="error_top" style="display:none"></div>
                        <div class="row restaurant_payout_create" role="tabpanel">
                            <div class="restaurant_payout_create-inner tab-content">
                                <div role="tabpanel" class="tab-pane active" id="cuisines_information">
                                    <fieldset>
                                        <legend>{{trans('lang.cuisines_edit')}}</legend>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">{{trans('lang.cuisines_name')}}</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control cat-name">
                                                <div class="form-text text-muted">{{ trans("lang.cuisines_name_help") }} </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label ">{{trans('lang.cuisines_description')}}</label>
                                            <div class="col-7">
                                <textarea rows="7" class="cuisines_description form-control"
                                          id="cuisines_description"></textarea>
                                                <div class="form-text text-muted">{{ trans("lang.cuisines_description_help") }}</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">{{trans('lang.cuisines_image')}}</label>
                                            <div class="col-7">
                                                <input type="file" id="cuisines_image">
                                                <div class="placeholder_img_thumb cat_image"></div>
                                                <div id="uploding_image"></div>
                                                <div class="form-text text-muted w-50">{{ trans("lang.cuisines_image_help") }}</div>
                                            </div>
                                        </div>
                                       <div class="form-check row width-100">
                                        <input type="checkbox" class="item_publish" id="item_publish">
                                        <label class="col-3 control-label"
                                               for="item_publish">{{trans('lang.item_publish')}}</label>
                                       </div>
                                        <div class="form-check row width-100" id="show_in_home">
                                            <input type="checkbox" id="show_in_homepage">
                                            <label class="col-3 control-label" for="show_in_homepage">{{trans('lang.show_in_home')}}</label>
                                            <div class="form-text text-muted w-50">{{trans('lang.show_in_home_desc')}}<span id="forsection"></span></div>
                                        </div>
                                    </fieldset>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="review_attributes">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="button" class="btn btn-primary edit-setting-btn"><i
                                    class="fa fa-save"></i> {{trans('lang.save')}}</button>
                        <a href="{!! route('cuisines') !!}" class="btn btn-default"><i
                                    class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
var id = "<?php echo $id;?>";
var cuisine = {!! json_encode($cuisine ?? null) !!};
$(document).ready(function(){
    if(!cuisine){
        $(".error_top").show().html('<p>Error: Cuisine not found.</p>');
    } else {
        $(".cat-name").val(cuisine.title || '');
        $(".cuisines_description").val(cuisine.description || '');
        if(cuisine.photo){
            $(".cat_image").append('<img class="rounded" style="width:50px" src="'+cuisine.photo+'" alt="image">');
        }
        if(parseInt(cuisine.publish)) $("#item_publish").prop('checked', true);
        if(parseInt(cuisine.show_in_homepage)) $("#show_in_homepage").prop('checked', true);
    }
    $(".edit-setting-btn").on('click', function(){
        var title = $(".cat-name").val();
        var description = $(".cuisines_description").val();
        var item_publish = $("#item_publish").is(":checked");
        var show_in_homepage = $("#show_in_homepage").is(":checked");
        var review_attributes = [];
        $('#review_attributes input').each(function(){ if($(this).is(':checked')) review_attributes.push($(this).val()); });
        if(!title){
            $(".error_top").show().html("<p>{{trans('lang.enter_cat_title_error')}}</p>");
            window.scrollTo(0,0); return;
        }
        var fd = new FormData();
        fd.append('title', title);
        fd.append('description', description);
        fd.append('publish', item_publish ? 1 : 0);
        fd.append('show_in_homepage', show_in_homepage ? 1 : 0);
        review_attributes.forEach(function(v){ fd.append('review_attributes[]', v); });
        if($('#cuisines_image')[0] && $('#cuisines_image')[0].files[0]){ fd.append('photo', $('#cuisines_image')[0].files[0]); }
        $.ajax({
            url: '{{ url('/cuisines') }}' + '/' + id,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: fd,
            processData: false,
            contentType: false
        }).done(function(){ window.location.href='{{ route('cuisines') }}'; })
          .fail(function(xhr){
            var msg = 'Failed to update';
            if(xhr.responseJSON && xhr.responseJSON.message){ msg = xhr.responseJSON.message; }
            $(".error_top").show().html('<p>'+msg+'</p>');
          });
    });
});
</script>
@endsection
