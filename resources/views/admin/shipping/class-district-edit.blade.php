@extends('admin.partials.master')
@section('title')
    {{ __('District Update') }}
@endsection
@section('shipping_active')
    active
@endsection
@section('shipping-classes')
    active
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('District Update') }}</h2>

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
                            <h4>{{ __('District Update') }}</h4>

                        </div>
                        <div class="card-body card-body-paddding">
                            <form action="{{ route('class.district.update') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <div class="modal-body modal-padding-bottom">
                                    <input type="hidden" name="id" value="{{ $district->id }}" />
                                    <div class="form-group align-items-center">
                                        <label for="cost" class="form-control-label">{{ __('Cost') }}</label>
                                        <input type="number" step="any" name="cost" placeholder="Cost on this district" value="{{ old('cost') ? old('cost') : priceFormatUpdate($district->cost,settingHelper('default_currency'),'*') }}" class="form-control" id="cost" required />
                                        @if ($errors->has('cost'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('cost') }}</p>
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
