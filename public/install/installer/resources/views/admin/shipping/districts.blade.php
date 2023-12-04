@extends('admin.partials.master')
<style>
    .modal-backdrop {
        display: none !important;
    }
</style>
@section('title')
    {{ __('Districts') }}
@endsection
@section('shipping_active')
    active
@endsection
@section('available-district')
    active
@endsection
@php
    $a       = null;
    $q              = null;
    if(isset($_GET['a'])){
        $a          = $_GET['a'];
    }
    if(isset($_GET['q'])){
        $q          = $_GET['q'];
    }
@endphp
@section('main-content')
    <section class="section district_section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Districts') }}</h2>
                    <p class="section-lead">
                        {{ __('You have total') . ' ' . $districts->total() . ' ' . __('District') }}
                    </p>
                </div>
                @if(hasPermission('district_import_create'))
                    <div class="buttons add-button">
                        <a href="#" class="btn btn-icon icon-left btn-outline-primary modal_class_remove"
                           data-toggle="modal" data-target="#district_import">
                            <i class='bx bx-plus'></i>{{ __('import_districts') }}
                        </a>
                    </div>
                @endif
            </div>
            <div class="row">
                <div class="{{ hasPermission('district_create') ? 'col-8 col-md-8 col-lg-8' : 'col-8 col-md-8 col-lg-8 middle' }}">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Districts') }}</h4>
                            <div class="card-header-form">
                                <form class="form-inline" id="sorting">
                                    <div class="form-group">
                                        <select class="division-by-ajax form-control sorting select2" name="a" id="a"
                                                required>
                                            <option value="">{{ __('Select Division') }}</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="q"
                                               value="{{ $q != null ? $q : "" }}" placeholder="{{ __('Search') }}">
                                        <div class="input-group-btn">
                                            <button class="btn btn-outline-primary"><i class="bx bx-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-md district_table_font">
                                    <tbody>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Division') }}</th>
                                        <th>{{ __('Country') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        @if(addon_is_activated('ramdhani'))
                                            <th>{{ __('COD') }}</th>
                                        @endif
                                        <th>{{ __('Cost') }}</th>
                                        @if(hasPermission('district_update') || hasPermission('district_delete'))
                                            <th>{{ __('Option') }}</th>
                                        @endif
                                    </tr>
                                    @foreach($districts as $key => $value)
                                        <tr id="{{ $key }}">
                                            <td> {{ $districts->firstItem() + $key }} </td>
                                            <td> {{ $value->name }} </td>
                                            <td> {{ @$value->division->name }} </td>
                                            <td> {{ @$value->country->name }} </td>
                                            <td>
                                                <label class="custom-switch mt-2 {{ hasPermission('district_update') ? '' : 'cursor-not-allowed' }}">
                                                    <input type="checkbox" name="custom-switch-checkbox"
                                                           value="district-status-change/{{$value->id}}"
                                                           {{ hasPermission('district_update') ? '' : 'disabled' }}
                                                           {{ $value->status == 1 ? 'checked' : '' }} class="{{ hasPermission('district_update') ? 'status-change' : '' }} custom-switch-input">
                                                    <span class="custom-switch-indicator"></span>
                                                </label>
                                            </td>
                                            @if(addon_is_activated('ramdhani'))
                                                <td>
                                                    <label class="custom-switch mt-2 {{ hasPermission('district_update') ? '' : 'cursor-not-allowed' }}">
                                                        <input type="checkbox" name="custom-switch-checkbox"
                                                               value="district-cod-change/{{$value->id}}"
                                                               {{ hasPermission('district_update') ? '' : 'disabled' }}
                                                               {{ $value->is_cod == 1 ? 'checked' : '' }} class="{{ hasPermission('district_update') ? 'status-change' : '' }} custom-switch-input">
                                                        <span class="custom-switch-indicator"></span>
                                                    </label>
                                                </td>
                                            @endif
                                            <td> {{ get_price($value->cost) }} </td>
                                            <td>
                                                @if(hasPermission('district_update'))
                                                    <a href="{{ route('district.edit', $value->id) }}"
                                                       class="btn btn-outline-secondary btn-circle"
                                                       data-toggle="tooltip" title=""
                                                       data-original-title="{{ __('Edit') }}"><i class="bx bx-edit"></i>
                                                    </a>
                                                @endif
                                                @if(hasPermission('city_delete'))
                                                    <a href="javascript:void(0)"
                                                       onclick="delete_row('delete/districts/', {{ $value->id }})"
                                                       class="btn btn-outline-danger btn-circle" data-toggle="tooltip"
                                                       title="" data-original-title="{{ __('Delete') }}">
                                                        <i class='bx bx-trash'></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <nav class="d-inline-block">
                                {{ $districts->appends(Request::except('page'))->links('pagination::bootstrap-4') }}
                            </nav>
                        </div>
                    </div>
                </div>
                @if(hasPermission('district_create'))
                    <div class="col-4 col-md-4 col-lg-4">
                        <div class="card">
                            <div class="card-header input-title">
                                <h4>{{ __('Add District') }}</h4>
                            </div>
                            <div class="card-body card-body-paddding">
                                <form method="POST" action="{{ route('district.store') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="division_id">{{ __('Division') }}</label>
                                        <select class="division-by-ajax form-control select2" name="division_id" id="division_id"
                                                required>
                                            <option value="">{{ __('Select Division') }}</option>
                                        </select>
                                        @if ($errors->has('division_id'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('district_id') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="name">{{ __('Name') }}</label>
                                        <input type="text" name="name" id="name" placeholder="{{__('Enter district name')}}"
                                               value="{{ old('name') }}" class="form-control" required>
                                        @if ($errors->has('name'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('name') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="cost">{{ __('Cost') }}</label>
                                        <input type="number" step="any" name="cost" id="cost"
                                               placeholder="{{ __('Cost on this district') }}" value="{{ old('cost') }}"
                                               class="form-control" required>
                                        @if ($errors->has('cost'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('cost') }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="form-group text-right">
                                        <button type="submit" class="btn btn-outline-primary" tabindex="4">
                                            {{ __('Save') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="modal fade" id="district_import" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">{{ __('import_districts') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    @php
                        $condition_1 = ini_get('max_execution_time') >= 600;
                        $condition_2 = (int)filter_var(ini_get('memory_limit'), FILTER_SANITIZE_NUMBER_INT) >= 2 ;
                    @endphp

                    <div class="modal-body">
                        <p class="text-capitalize">{{ __('note') }} : {!! __('district_import_msg') !!} :</p>
                        <div class="d-flex">
                            <p class="text-success warning_txt"><i class="bx bxs-check-circle"></i></p>
                            <p>{{ __('district_delete_msg') }}</p>
                        </div>
                        <div class="d-flex">
                            <p class="warning_txt"><i
                                        class="bx {{ $condition_1 ? 'bxs-check-circle text-success' : 'bxs-x-circle text-danger' }}"></i>
                            </p>
                            <p>{!! __('max_msg') !!} </p>
                        </div>
                        <div class="d-flex">
                            <p class="warning_txt"><i
                                        class="bx {{ $condition_2 ? 'bxs-check-circle text-success' : 'bxs-x-circle text-danger' }}"></i>
                            </p>
                            <p>{!! __('memory_limit_msg') !!} </p>
                        </div>
                        <div class="modal-footer modal-padding-bottom text-center">
                            <a href="{{ route('import.district') }}"
                               class="btn btn-icon icon-left btn-outline-primary {{ !$condition_1 || !$condition_2 ? 'disable_btn' : '' }}">
                                <i class='bx bx-check'></i>{{ __('confirm') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@include('admin.common.delete-ajax')

@push('script')
    <script type="text/javascript" src="{{ static_asset('admin/js/ajax-live-search.js') }}"></script>
@endpush