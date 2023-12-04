@extends('admin.partials.master')
@section('title')
    {{ __('Division') }}
@endsection
@section('shipping_active')
    active
@endsection
@section('available-divisions')
    active
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Division Update') }}</h2>

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
                            <h4>{{ __('Division Update') }}</h4>

                        </div>
                        <div class="card-body card-body-paddding">
                            <form action="{{ route('division.update') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <div class="modal-body modal-padding-bottom">

                                    <div class="form-group" >
                                        <label for="country_id_c">{{ __('Country') }}</label>
                                        <select class="form-control select2" name="country_id" id ="country_id_c" required>
                                            <option value="">{{ __('Select Country') }}</option>
                                            @foreach($countries as $key => $country)
                                                <option {{ $country->id == $division->country_id ? "selected" : "" }} value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('country_id'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('country_id') }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="form-group align-items-center">
                                        <label for="name" class="form-control-label">{{ __('Name') }}</label>
                                        <input type="text" name="name" placeholder="{{__('Enter division name')}}" value="{{ $division->name }}" class="form-control" id="name" required />
                                        <input type="hidden" name="id" value="{{ $division->id }}" required />
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


