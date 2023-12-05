@extends('admin.partials.master')
@section('title')
    {{ __('Areas') }}
@endsection
@section('shipping_active')
    active
@endsection
@section('available-areas')
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
                    <h2 class="section-title">{{ __('Areas') }}</h2>
                    <p class="section-lead">
                        {{ __('You have total') . ' ' . $areas->total() . ' ' . __('Areas') }}
                    </p>
                </div>
                @if(hasPermission('area_import_create'))
                <div class="mt-4">
                    <a href="javascript:void(0)" class="btn btn-outline-primary currency-add-btn modal-menu"
                       data-title="{{__('Import Areas')}}"
                       data-url="{{ route('edit-info', ['page_name' => 'import-areas']) }}" data-toggle="modal"
                       data-target="#common-modal">
                        <i class="bx bx-plus"></i>{{ __('Import Area') }}
                    </a>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="{{ hasPermission('area_create') ? 'col-7 col-md-7 col-lg-7' : 'col-7 col-md-7 col-lg-8 middle' }}">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Areas') }}</h4>
                            <div class="card-header-form">
                                <form class="form-inline" id="sorting">
                                    <div class="form-group">
                                        <select class="form-control select2 sorting" name="a">
                                            <option value="">{{ __('Filter By Upazilas/Thanas') }}</option>
                                            @foreach($upazilas as $key => $upazila)
                                                <option {{ $a != null ? ($upazila->id == $a ? "selected" : "" ) :''}} value="{{ $upazila->id }}">{{ $upazila->name }}</option>
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
                                        <th>{{ __('Thana/Upazila') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        @if(hasPermission('area_update') || hasPermission('area_delete'))
                                        <th>{{ __('Option') }}</th>
                                        @endif
                                    </tr>
                                    @foreach($areas as $key => $value)
                                        <tr id="{{ $areas->firstItem() + $key }}">
                                            <td> {{$areas->firstItem() + $key }} </td>
                                            <td> {{ $value->name }} </td>
                                            <td> {{ $value->country->name }} </td>
                                            <td> {{ $value->division->name }} </td>
                                            <td> {{ $value->district->name }} </td>
                                            <td> {{ $value->upazila->name }} </td>
                                            <td> <label class="custom-switch mt-2 {{ hasPermission('area_update') ? '' : 'cursor-not-allowed' }}">
                                                    <input type="checkbox" name="custom-switch-checkbox" value="area-status-change/{{$value->id}}"
                                                            {{ hasPermission('area_update') ? '' : 'enable' }}
                                                           {{ $value->status == 1 ? 'checked' : '' }} class="{{ hasPermission('division_update') ? 'status-change' : '' }} custom-switch-input">
                                                    <span class="custom-switch-indicator"></span>
                                                </label>
                                            </td>
                                            <td>
                                                @if(hasPermission('area_update'))
                                                <a href="{{ route('area.edit', $value->id) }}" class="btn btn-outline-secondary btn-circle"
                                                    data-toggle="tooltip" title=""
                                                    data-original-title="{{ __('Edit') }}"><i class="bx bx-edit"></i>
                                                 </a>
                                                @endif
                                                @if(hasPermission('area_delete'))
                                                  <a href="javascript:void(0)"
                                                    onclick="delete_row('delete/areas/', {{ $value->id }})"
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
                                {{ $areas->appends(Request::except('page'))->links('pagination::bootstrap-4') }}
                            </nav>
                        </div>
                    </div>
                </div>
            @if(hasPermission('area_create'))
                <div class="col-5 col-md-5 col-lg-5">
                    <div class="card" >
                            <div class="card-header input-title">
                                <h4>{{ __('Add Area') }}</h4>
                            </div>
                            <div class="card-body card-body-paddding">
                                <form method="POST" action="{{ route('area.store') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group" >
                                        <label for="country_id">{{ __('Thana/Upazila') }}</label>
                                        <select class="form-control select2" name="upazila_id" id ="upazila_id" required>
                                            <option value="">{{ __('Select Thana/Upazila') }}</option>
                                            @foreach($upazilas as $key => $upazila)
                                                <option {{ old('upazila_id') ? ($upazila->id == old('upazila_id') ? "selected" : "" ) :''}} value="{{ $upazila->id }}">{{ $upazila->name }}</option>
                                            @endforeach

                                        </select>
                                        @if ($errors->has('upazila_id'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('upazila_id') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="code">{{ __('Name') }}</label>
                                        <input type="text" name="name" id="name" placeholder="{{ __('Enter Area name') }}" value="{{ old('name') }}" class="form-control" required>
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


