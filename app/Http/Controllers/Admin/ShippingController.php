<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Shipping\DistrictRequest;
use App\Http\Requests\Admin\Shipping\DivisionRequest;
use App\Http\Requests\Admin\Shipping\UpazilaRequest;
use App\Http\Requests\Admin\Shipping\AreaRequest;
use App\Http\Requests\Admin\ShippingCommissionRequest;
use App\Models\District;
use App\Models\Division;
use App\Models\Upazila;
use App\Models\Area;
use App\Repositories\Admin\Addon\ShippingClassRepository;
use App\Repositories\Interfaces\Admin\SettingInterface;
use App\Repositories\Interfaces\Admin\ShippingInterface;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShippingController extends Controller
{
    protected $settings;
    protected $shipping;

    public function __construct(SettingInterface $settings, ShippingInterface $shipping)
    {
        $this->settings = $settings;
        $this->shipping = $shipping;
    }

    public function configuration()
    {
        return view('admin.shipping.configuration');
    }
    public function configurationSave(ShippingCommissionRequest $request)
    {
        if($request->shipping_fee_flat_rate):
            $request['shipping_fee_flat_rate'] = priceFormatUpdate($request->shipping_fee_flat_rate,settingHelper('default_currency'));
        endif;
        if($request->shipping_fee_default_rate):
            $request['shipping_fee_default_rate'] = priceFormatUpdate($request->shipping_fee_default_rate,settingHelper('default_currency'));
        endif;
        if (isDemoServer()):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;
        DB::beginTransaction();
        try {
            $this->settings->update($request);
            Toastr::success(__('Updated Successfully'));
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    public function countries(Request $request)
    {
        $countries = $this->shipping->countriesPaginate($request,get_pagination('pagination'));

        return view('admin.shipping.countries', compact('countries'));
    }
    public function countryStatusChange(Request $request)
    {
        if (isDemoServer()):
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;
        DB::beginTransaction();
        try {
            $this->shipping->countryStatusChange($request['data']);
            $response['message']    = __('Updated Successfully');
            $response['title']      = __('Success');
            $response['status']     = 'success';
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function divisions(Request $request)
    {
        $countries = $this->shipping->countries()->where('status', 1)->get();
        $divisions = $this->shipping->divisionsPaginate($request, get_pagination('index_form_paginate'));

        return view('admin.shipping.divisions', compact('divisions','countries'));
    }
    public function divisionStore(DivisionRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->shipping->divisionStore($request);
            Toastr::success(__('Created Successfully'));
            DB::commit();
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    public function divisionEdit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $division= $this->shipping->getDivision($id);
            $countries   = $this->shipping->countries()->where('status', 1)->get();
            $r           = $request->server('HTTP_REFERER');
            DB::commit();
            return view('admin.shipping.division-edit',compact('division','countries','r'));
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    public function divisionUpdate (DivisionRequest $request)
    {
        if (isDemoServer()):
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;
        DB::beginTransaction();
        try {
            $this->shipping->divisionUpdate($request);
            Toastr::success(__('Updated Successfully'));
            return redirect($request->r);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    public function divisionStatusChange(Request $request)
    {
        if (isDemoServer()):
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;
        DB::beginTransaction();
        try {
            $this->shipping->divisionStatusChange($request['data']);
            $response['message']    = __('Updated Successfully');
            $response['title']      = __('Success');
            $response['status']     = 'success';
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function Districts(Request $request)
    {

        $data           = [
            'districts'    => $this->shipping->districtsPaginate($request, get_pagination('index_form_paginate')),
        ];
        
        $countries = $this->shipping->countries()->where('status', 1)->get();
        $divisions = $this->shipping->divisions()->where('status', 1)->get();
        $districts  = $this->shipping->districtsPaginate($request, get_pagination('index_form_paginate'));

        return view('admin.shipping.districts', compact('countries','divisions','districts'));
    }
    public function districtStore(DistrictRequest $request)
    {
        try {
            $this->shipping->DistrictStore($request);
            Toastr::success(__('Created Successfully'));
            return back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    public function districtEdit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $district=$this->shipping->getDistrict($id);
            $r       = $request->server('HTTP_REFERER');

            $data = [
                'district'      => $district,
                'r'         => $r,
            ];
            DB::commit();
            return view('admin.shipping.district-edit',$data);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    public function districtUpdate (DistrictRequest $request)
    {
        if (isDemoServer()):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;
        DB::beginTransaction();
        try {
            $this->shipping->districtUpdate($request);
            Toastr::success(__('Updated Successfully'));
            DB::commit();
            return redirect($request->r);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    
    public function districtStatusChange(Request $request)
    {
        if (isDemoServer()):
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;
        DB::beginTransaction();
        try {
            $this->shipping->districtStatusChange($request['data']);
            $response['message']    = __('Updated Successfully');
            $response['title']      = __('Success');
            $response['status']     = 'success';
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    public function getDivisionByCountryAjax(Request $request){
        $data['divisions'] = $this->shipping->divisions()->where("country_id",$request->country_id)->where('status', 1)->get(["name","id"]);
        return response()->json($data);
    }
    public function getDistrictByDivisionAjax(Request $request){
        $data['districts'] = $this->shipping->districts()->where("division_id",$request->division_id)->where('status', 1)->get(["name","id"]);

        return response()->json($data);
    }

    public function divisionImport(){
        DB::beginTransaction();
        try {
            $this->shipping->divisionImport();
            Toastr::success(__('Division imported successfully'));
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function importDistrict(): \Illuminate\Http\RedirectResponse
    {
        $max_exec_time = ini_get('max_execution_time');
        $memory_limit = ini_get('memory_limit');

        if ($max_exec_time < 600)
        {
            Toastr::error(__('max_error_msg'));
            return back();
        }
		
		
        try {
            District::truncate();
            $path = base_path('public/sql/districts.sql');
			
			$db = [
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'host' => env('DB_HOST'),
            'database' => env('DB_DATABASE')
        ];
  
//            exec("mysql --user={$db['username']} --password={$db['password']} --host={$db['host']} --database {$db['database']} < $path");
			DB::unprepared(file_get_contents($path));
            Toastr::success(__('Imported Successf   ully'),__('Success'));
			
            return back();
        } catch (\Exception $e) {
            Toastr::error(__('please set max_allowed_packet=500M in mysql database'));
			
            return back();
        }
    }

    public function getDivisionByAjax(Request $request)
    {
        $term           = trim($request->q);
        if (empty($term)) {
            return \Response::json([]);
        }

        $divisions = $this->shipping->divisions()
            ->where('name', 'like', '%'.$term.'%')
            ->limit(20)
            ->get();

        $formatted_user   = [];

        foreach ($divisions as $division) {
            $formatted_user[] = ['id' => $division->id, 'text' => $division->name];
        }

        return \Response::json($formatted_user);
    }
    
    public function upazilas(Request $request)
    {

        $countries = $this->shipping->countries()->where('status', 1)->get();
        $districts = $this->shipping->districts()->where('status', 1)->get();
        $upazilas  = $this->shipping->upazilasPaginate($request, get_pagination('index_form_paginate'));

        return view('admin.shipping.upazilas', compact('countries','districts' , 'upazilas'));
    }
    
    public function upazilaStore(UpazilaRequest $request)
    {
        if($request->district_id != null)
       {
            DB::beginTransaction();
            try {
                $this->shipping->upazilaStore($request);
                Toastr::success(__('Created Successfully'));
                DB::commit();
                return back();
            } catch (\Exception $e) {
                DB::rollBack();
                Toastr::error($e->getMessage());
                return redirect()->back();
            }
       }
    }
    
    public function upazilaStatusChange(Request $request)
    {
        if (isDemoServer()):
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;
        DB::beginTransaction();
        try {
            $this->shipping->upazilaStatusChange($request['data']);
            $response['message']    = __('Updated Successfully');
            $response['title']      = __('Success');
            $response['status']     = 'success';
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    
      public function upazilaEdit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $upazila = $this->shipping->getUpazila($id);
            $r       = $request->server('HTTP_REFERER');

            $data = [
                'upazila'      => $upazila,
                'r'         => $r,
            ];
            DB::commit();
            return view('admin.shipping.upazila-edit',$data);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    
    public function upazilaUpdate (UpazilaRequest $request)
    {
        if (isDemoServer()):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;
        DB::beginTransaction();
        try {
            $this->shipping->upazilaUpdate($request);
            Toastr::success(__('Updated Successfully'));
            DB::commit();
            return redirect($request->r);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    
    public function areas(Request $request)
    {

        $countries = $this->shipping->countries()->where('status', 1)->get();
        $districts = $this->shipping->districts()->where('status', 1)->get();
        $upazilas = $this->shipping->upazilas()->where('status', 1)->get();
        $areas  = $this->shipping->areasPaginate($request, get_pagination('index_form_paginate'));
        
        return view('admin.shipping.areas', compact('countries','districts' , 'upazilas','areas'));
    }
    
    public function areaStore(AreaRequest $request)
    {
        if($request->upazila_id != null)
       {
            DB::beginTransaction();
            try {
                $this->shipping->areaStore($request);
                Toastr::success(__('Created Successfully'));
                DB::commit();
                return back();
            } catch (\Exception $e) {
                DB::rollBack();
                Toastr::error($e->getMessage());
                return redirect()->back();
            }
       }
    }
    
    public function areaStatusChange(Request $request)
    {
        if (isDemoServer()):
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;
        DB::beginTransaction();
        try {
            $this->shipping->areaStatusChange($request['data']);
            $response['message']    = __('Updated Successfully');
            $response['title']      = __('Success');
            $response['status']     = 'success';
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    
    public function areaEdit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $area = $this->shipping->getArea($id);
            $r       = $request->server('HTTP_REFERER');

            $data = [
                'area'      => $area,
                'r'         => $r,
            ];
            DB::commit();
            return view('admin.shipping.area-edit',$data);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    
    public function areaUpdate (AreaRequest $request)
    {
        if (isDemoServer()):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;
        DB::beginTransaction();
        try {
            $this->shipping->areaUpdate($request);
            Toastr::success(__('Updated Successfully'));
            DB::commit();
            return redirect($request->r);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
}
