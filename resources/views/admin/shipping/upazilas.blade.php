@extends('admin.partials.master')
@section('title')
    {{ __('Upazilas') }}
@endsection
@section('shipping_active')
    active
@endsection
@section('available-upazilas')
    active
@endsection
@php
    $a              = null;
    $q              = null;
    if(isset($_GET['a'])){
        $a          = $_GET['a'];
    }
    if(isset($_GET['q'])){
        $q          = $_GET['q'];
    }

@endphp
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Thanas/Upazilas') }}</h2>
                    <p class="section-lead">
                        {{ __('You have total') . ' ' . $upazilas->total() . ' ' . __('Thanas/Upazilas') }}
                    </p>
                </div>
                @if(hasPermission('upazila_import_create'))
                <div class="mt-4">
                    <a href="javascript:void(0)" class="btn btn-outline-primary currency-add-btn modal-menu"
                       data-title="{{__('Import Divisions')}}"
                       data-url="{{ route('edit-info', ['page_name' => 'import-divisions']) }}" data-toggle="modal"
                       data-target="#common-modal">
                        <i class="bx bx-plus"></i>{{ __('Import Thanas/Upazilas') }}
                    </a>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="{{ hasPermission('upazila_create') ? 'col-7 col-md-7 col-lg-7' : 'col-7 col-md-7 col-lg-8 middle' }}">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Thanas/Upazilas') }}</h4>
                            <div class="card-header-form">
                                <form class="form-inline" id="sorting">
                                    <div class="form-group">
                                        <select class="form-control select2 sorting" name="a">
                                            <option value="">{{ __('Filter By Districts') }}</option>
                                            @foreach($districts as $key => $district)
                                                <option {{ $a != null ? ($district->id == $a ? "selected" : "" ) :''}} value="{{ $district->id }}">{{ $district->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="q" value="{{ $q != null ? $q : "" }}" placeholder="{{ __('Search') }}">
                                        <div class="input-group-btn">
                                            <button class="btn btn-outline-primary"><i class="bx bx-search"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-md">
                                    <tbody>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Country') }}</th>
                                        <th>{{ __('Division') }}</th>
                                        <th>{{ __('District') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        @if(hasPermission('upazila_update') || hasPermission('upazila_delete'))
                                        <th>{{ __('Option') }}</th>
                                        @endif
                                    </tr>
                                    @foreach($upazilas as $key => $value)
                                        <tr id="{{ $upazilas->firstItem() + $key }}">
                                            <td> {{$upazilas->firstItem() + $key }} </td>
                                            <td> {{ $value->name }} </td>
                                            <td> {{ $value->country->name }} </td>
                                            <td> {{ $value->division->name }} </td>
                                            <td> {{ $value->district->name }} </td>
                                            <td> <label class="custom-switch mt-2 {{ hasPermission('upazila_update') ? '' : 'cursor-not-allowed' }}">
                                                    <input type="checkbox" name="custom-switch-checkbox" value="upazila-status-change/{{$value->id}}"
                                                            {{ hasPermission('upazila_update') ? '' : 'enable' }}
                                                           {{ $value->status == 1 ? 'checked' : '' }} class="{{ hasPermission('upazila_update') ? 'status-change' : '' }} custom-switch-input">
                                                    <span class="custom-switch-indicator"></span>
                                                </label>
                                            </td>
                                            <td>
                                                @if(hasPermission('upazila_update'))
                                                <a href="{{ route('upazila.edit', $value->id) }}" class="btn btn-outline-secondary btn-circle"
                                                    data-toggle="tooltip" title=""
                                                    data-original-title="{{ __('Edit') }}"><i class="bx bx-edit"></i>
                                                 </a>
                                                @endif
                                                @if(hasPermission('upazila_delete'))
                                                  <a href="javascript:void(0)"
                                                    onclick="delete_row('delete/upazilas/', {{ $value->id }})"
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
                                {{ $upazilas->appends(Request::except('page'))->links('pagination::bootstrap-4') }}
                            </nav>
                        </div>
                    </div>
                </div>
            @if(hasPermission('upazila_create'))
                <div class="col-5 col-md-5 col-lg-5">
                    <div class="card" >
                            <div class="card-header input-title">
                                <h4>{{ __('Add Thana/Upazila') }}</h4>
                            </div>
                            <div class="card-body card-body-paddding">
                                <form method="POST" action="{{ route('upazila.store') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group" >
                                        <label for="district_id">{{ __('District') }}</label>
                                        <select class="form-control select2" name="district_id" id ="district_id" required>
                                            <option value="">{{ __('Select District') }}</option>
                                            @foreach($districts as $key => $district)
                                                <option {{ old('district_id') ? ($district->id == old('district_id') ? "selected" : "" ) :''}} value="{{ $district->id }}">{{ $district->name }}</option>
                                            @endforeach

                                        </select>
                                        @if ($errors->has('district_id'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('district_id') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="code">{{ __('Name') }}</label>
                                        <input type="text" name="name" id="name" placeholder="{{ __('Enter Thana/Upazila name') }}" value="{{ old('name') }}" class="form-control" required>
                                        @if ($errors->has('name'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('name') }}</p>
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
    </section>
@endsection

@include('admin.common.delete-ajax')
@include('admin.common.common-modal')


