@extends('admin.partials.master')
@section('installed_plugin')
    active
@endsection
@section('plugin_utility')
    active
@endsection
@section('title')
    {{ __('Available Addons') }}
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Installed Addons') }}</h2>
                    <p class="section-lead">
                        {{ __('You have total') . ' '.$plugins->total().' ' . __('Plugin installed') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-xs-12 col-md-7">
                <div class="card">
                    <form action="">
                        <div class="card-header input-title">
                            <h4>{{ __('Installed Addons') }}</h4>
                        </div>
                    </form>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-md">
                                <tbody>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Image') }}</th>
                                    <th>{{ __('Version') }}</th>
                                        <th>{{ __('Status') }}</th>
                                </tr>
                                @foreach ($plugins as $plugin)
                                    <tr id="row_{{ $plugin->plugin_identifier }}" class="table-data-row">
                                        <input type="hidden" value="{{$plugin->plugin_identifier}}" id="id">
                                        <td><a href="{{route('admin.plugin.activate',$plugin->plugin_identifier)}}">Activate</a><br><a href="{{route('admin.plugin.deactivate',$plugin->plugin_identifier)}}">De-Activate</a></td>
                                        <td>{{ $plugin->name }}</td>
                                        <td>
                                            @if (file_exists(app()->path().'/Plugins/'.$plugin->directory.'/screenshot.png'))
                                            <img
                                                    src="{{ static_asset('../app/Plugins/'.$plugin->directory.'/screenshot.png') }}"
                                                    alt="{{ @$plugin->name }}" width="40"
                                                    class="mr-3 rounded">
                                            @else
                                            <img src="{{ static_asset('images/default/default-image-40x40.png') }}"
                                                alt="{{ @$plugin->name }}"
                                                class="mr-3 rounded">
                                            @endif
                                        </td>
                                        <td>{{ $plugin->version }}</td>
                                        
                                            <td>
                                                <label class="custom-switch mt-2">
                                                    <input type="checkbox" name="custom-switch-checkbox"
                                                           value="addon-status-change/{{$plugin->plugin_identifier}}"
                                                           {{ $plugin->status == 1 ? 'checked' : '' }} class="status-change custom-switch-input">
                                                    <span class="custom-switch-indicator"></span>
                                                </label>
                                            </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <nav class="d-inline-block">
                        </nav>
                    </div>
                </div>
            </div>
            <div class="col-sm-xs-12 col-md-5">
                <div class="card">
                    <div class="card-header input-title">
                        <h4>{{ __('Install/Update') }}</h4>
                    </div>
                    <div class="card-body card-body-paddding">
                        @if(Session::has('response'))
                            <div class="mb-2">
                                @foreach(Session::get('response') as $error)
                                    <div class="invalid-feedback">
                                        * {{ $error }}
                                    </div>
                                @endforeach
                            </div>
                        @endisset
                        <form method="POST" action="" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="purchase_code">{{ __('Purchase code') }} *</label>
                                <input type="text" name="purchase_code" id="purchase_code"
                                       placeholder="{{ __('Enter your purchase code')  }}"
                                       value="{{ old('purchase_code') }}" class="form-control" required>
                                @if ($errors->has('purchase_code'))
                                    <div class="invalid-feedback">
                                        <p>{{ $errors->first('purchase_code') }}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="file">{{ __('Addon') }} <small>{{ __('(Zip File)') }}</small></label>
                                <div class="form-group">
                                    <input type="file" class="custom-file-input image_pick file-select"
                                           accept=".zip,.rar,.7zip" name="plugin_zip_file" id="customFile"/>
                                    @if ($errors->has('plugin_zip_file'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('plugin_zip_file') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @if(hasPermission('plugin_update'))
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-outline-primary" tabindex="4">
                                        {{ __('Save') }}
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection