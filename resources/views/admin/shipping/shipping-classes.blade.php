@extends('admin.partials.master')
@section('title')
    {{ __('Shipping Class') }}
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
                    <h2 class="section-title">{{ __('Shipping Class') }}</h2>
                    <p class="section-lead">
                        {{ __('You have total') . ' ' . $classes->total() . ' ' . __('Country') }}
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-7 col-md-7 col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md">
                                        <tbody>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                        @foreach($classes as $key => $value)
                                            <tr id="{{ $key }}">
                                                <td> {{ $classes->firstItem() + $key  }} </td>
                                                <td> {{ $value->name }} </td>
                                                <td>
                                                    <label class="custom-switch mt-2 {{ hasPermission('country_update') ? '' : 'cursor-not-allowed' }}">
                                                        <input type="checkbox" name="custom-switch-checkbox"
                                                               value="shipping-class-status-change/{{$value->id}}"
                                                               {{ hasPermission('country_update') ? '' : 'disabled' }}
                                                               {{ $value->status == 1 ? 'checked' : '' }} class="{{ hasPermission('country_update') ? 'status-change' : '' }} custom-switch-input">
                                                        <span class="custom-switch-indicator"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    @if(addon_is_activated('ramdhani'))
                                                        <a href="{{ route('shipping-classes.show', $value->id) }}"
                                                           class="btn btn-outline-secondary btn-circle"
                                                           data-toggle="tooltip" title=""
                                                           data-original-title="{{ __('View') }}"><i
                                                                    class="bx bxs-show"></i>
                                                        </a>
                                                    @endif
                                                    @if(hasPermission(true))
                                                        <a href="{{ route('shipping-classes.edit', $value->id) }}"
                                                           class="btn btn-outline-secondary btn-circle"
                                                           data-toggle="tooltip" title=""
                                                           data-original-title="{{ __('Edit') }}"><i
                                                                    class="bx bx-edit"></i>
                                                        </a>
                                                    @endif
                                                    @if(hasPermission(true))
                                                        <a href="javascript:void(0)"
                                                           onclick="delete_row('delete/shipping-class/', {{ $value->id }})"
                                                           class="btn btn-outline-danger btn-circle"
                                                           data-toggle="tooltip"
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
                                    {{ $classes->appends(Request::except('page'))->links('pagination::bootstrap-4') }}
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                @if(hasPermission('true'))
                    <div class="col-5 col-md-5 col-lg-5">
                        <div class="card">
                            <div class="card-header input-title">
                                @php
                                    $title = isset($class) ? __('Edit Shipping Class') : __('Add Shipping Class');
                                    $route = isset($class) ? route('shipping-classes.update', $class->id) : route('shipping-classes.store');
                                @endphp
                                <h4>{{ $title }}</h4>
                            </div>
                            <div class="card-body card-body-paddding">
                                <form method="POST" action="{{ $route }}">
                                    @csrf
                                    @isset($class)
                                        @method('PUT')
                                    @endisset
                                    <div class="form-group">
                                        <label for="code">{{ __('Name') }}</label>
                                        <input type="text" name="name" id="name"
                                               placeholder="{{ __('Enter class name') }}"
                                               value="{{ old('name',@$class->name) }}"
                                               class="form-control">
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
