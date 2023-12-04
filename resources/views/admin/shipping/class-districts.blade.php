@extends('admin.partials.master')
@section('title')
    {{ __('Districts') }}
@endsection
@section('shipping_active')
    active
@endsection
@section('shipping-classes')
    active
@endsection
@section('main-content')
    <section class="section district_section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __("Class Districts",['name' => $class->name]) }}</h2>
                    <p class="section-lead">
                        {{ __('You have total') . ' ' . $districts->total() . ' ' . __('District') }}
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="'col-8 col-md-8 col-lg-8 middle">
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-md district_table_font">
                                    <tbody>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Division') }}</th>
                                        <th>{{ __('Country') }}</th>
                                        <th>{{ __('Cost') }}</th>
                                        @if(hasPermission('district_update') || hasPermission('district_delete'))
                                            <th>{{ __('Option') }}</th>
                                        @endif
                                    </tr>
                                    @foreach($districts as $key => $value)
                                        <tr id="{{ $key }}">
                                            <td> {{ $districts->firstItem() + $key }} </td>
                                            <td> {{ $value->district->name }} </td>
                                            <td> {{ @$value->district->division->name }} </td>
                                            <td> {{ @$value->district->country->name }} </td>
                                            <td> {{ get_price($value->cost) }} </td>
                                            <td>
                                                @if(hasPermission('district_update'))
                                                    <a href="{{ route('class.district.edit', $value->id) }}"
                                                       class="btn btn-outline-secondary btn-circle"
                                                       data-toggle="tooltip" title=""
                                                       data-original-title="{{ __('Edit') }}"><i class="bx bx-edit"></i>
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
            </div>
        </div>
    </section>
@endsection

@include('admin.common.delete-ajax')

@push('script')
    <script type="text/javascript" src="{{ static_asset('admin/js/ajax-live-search.js') }}"></script>
@endpush