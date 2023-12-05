@extends('admin.partials.master')
@section('title')
    {{ __('Upazila/Thana Update') }}
@endsection
@section('shipping_active')
    active
@endsection
@section('available-upazilas')
    active
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Upazila Update') }}</h2>

                </div>
                <div class="buttons add-button">
                    <a href="{{ old('r') ? old('r') : (@$r ? $r : url()->previous() )}}" class="btn btn-icon icon-left btn-outline-primary"><i
                            class="bx bx-arrow-back"></i>{{ __('Back') }}</a>
                </div>
            </div>
            <div class="row">
                <div class="col-7 col-md-7 col-lg-7 middle">
                    <div class="card" >
                        <div class="card-header input-title">
                            <h4>{{ __('Upazila Update') }}</h4>

                        </div>
                        <div class="card-body card-body-paddding">
                            <form action="{{ route('upazila.update') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <div class="modal-body modal-padding-bottom">
                                    
                                     <div class="form-group" >
                                        <label for="division_id">{{ __('Country') }}</label>
                                        <select class="division-by-ajax form-control sorting select2" name="country_id" id="country_id" required>
                                            <option value="">{{ __('Select Country') }}</option>
                                                <option value="{{ $upazila->country_id }}"
                                                        selected>{{ $upazila->country->name }}</option>
                                        </select>
                                        @if ($errors->has('country_id'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('country_id') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="form-group" >
                                        <label for="division_id">{{ __('Division') }}</label>
                                        <select class="division-by-ajax form-control sorting select2" name="division_id" id="division_id" required>
                                            <option value="">{{ __('Select Division') }}</option>
                                                <option value="{{ $upazila->division_id }}"
                                                        selected>{{ $upazila->division->name }}</option>
                                        </select>
                                        @if ($errors->has('division_id'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('division_id') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                      <div class="form-group" >
                                        <label for="division_id">{{ __('District') }}</label>
                                        <select class="division-by-ajax form-control sorting select2" name="district_id" id="district_id" required>
                                            <option value="">{{ __('Select District') }}</option>
                                                <option value="{{ $upazila->district_id }}"
                                                        selected>{{ $upazila->district->name }}</option>
                                        </select>
                                        @if ($errors->has('district_id'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('district_id') }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="form-group align-items-center">
                                        <label for="name" class="form-control-label">{{ __('Name') }}</label>
                                        <input type="text" name="name" placeholder="{{__('Enter Upazila/Than name')}}" value="{{ old('name') ? old('name') : $upazila->name }}" class="form-control" id="name" required />
                                        <input type="hidden" name="id" value="{{ $upazila->id }}" required />
                                        <input type="hidden" value="{{ old('r') ? old('r') : (@$r ? $r : url()->previous() )}}" name="r">
                                        @if ($errors->has('name'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('name') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer modal-padding-bottom">
                                    <button type="submit" class="btn btn-outline-primary">{{ __('Update') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('script')
    <script type="text/javascript" src="{{ static_asset('admin/js/ajax-live-search.js') }}"></script>
@endpush

