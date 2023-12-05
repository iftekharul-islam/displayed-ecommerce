<?php

namespace App\Repositories\Admin;

use App\Models\District;
use App\Models\Country;
use App\Models\Division;
use App\Models\Upazila;
use App\Models\Area;
use App\Repositories\Interfaces\Admin\ShippingInterface;

class ShippingRepository implements ShippingInterface
{
    public function countries()
    {
        return Country::orderBy('name');
    }
    public function getAllCountries()
    {
        return Country::with('flag')->where('status',1)->get();
    }

    public function userCountries()
    {
        return Country::where('status',1)->selectRaw('name,id')->orderBy('name')->get();
    }

    public function countriesPaginate($request, $limit)
    {
        return $this->countries()
            ->when($request->q != null, function ($query) use ($request){
                $query->where('name', 'like', '%'.$request->q.'%');
                $query->orwhere('iso3', 'like', '%'.$request->q.'%');
            })
            ->paginate($limit);
    }
    public function countryStatusChange($request)
    {
            $country            = Country::find($request['id']);
            $country->status    = $request['status'];
            $country->save();
            return true;
    }

    //division
    public function divisions()
    {
        return Division::with('country')->orderBy('name');
    }
    public function getdivisionByCountry($id)
    {
        return Division::where('status',1)->where('country_id',$id)->orderBy('name')->get();
    }
    public function getdivision($id)
    {
        return Division::with('country')->find($id);
    }
    public function divisionsPaginate($request ,$limit)
    {
        return $this->divisions()
            ->when($request->a != null, function ($query) use ($request){
                $query->where('country_id',$request->a);
            })
            ->when($request->q != null, function ($query) use ($request){
                $query->where('name', 'like', '%'.$request->q.'%');
            })
            ->paginate($limit);
    }
    public function divisionStatusChange($request)
    {
            $division            = Division::find($request['id']);
            $division->status    = $request['status'];
            $division->save();
            return true;
    }
    public function divisionStore($request)
    {
            $division              = new Division();
            $division->name        = $request->name;
            $division->country_id  = $request->country_id;
            $division->save();
            return true;
    }
    public function divisionUpdate($request)
    {
        Division::where('id',$request->id)->update(['name'=> $request->name, 'country_id' =>$request->country_id ]);
            // $division              = Division::find($request->id);
            
            // $division->name        = $request->name;
            // $division->country_id  = $request->country_id;
            // dd($division->save());
            // $division->save();
            return true;
    }

    //District
    public function districts()
    {
        return District::with('country','division')->orderBy('name');
    }
    public function getDistrict($id)
    {
        return District::with('country','division')->orderBy('name')->find($id);
    }

    public function getdistrictsBydivision($id)
    {
        return District::where('status',1)->where('division_id',$id)->orderBy('name')->get();
    }

    public function districtsPaginate($request, $limit)
    {
        return $this->districts()
            ->when($request->a != null, function ($query) use ($request){
                $query->where('division_id',$request->a);
            })
            ->when($request->q != null, function ($query) use ($request){
                $query->where('name', 'like', '%'.$request->q.'%');
            })
            ->paginate($limit);
    }
    public function DistrictStatusChange($request)
    {
        $country            = District::find($request['id']);
        $country->status    = $request['status'];
        $country->save();
        return true;
    }

    public function codStatusChange($request): bool
    {
        $country            = District::find($request['id']);
        $country->is_cod    = $request['status'];
        $country->save();
        return true;
    }

    public function DistrictStore($request)
    {
        $division             = Division::find($request->division_id);
        $District              = new District();
        $District->name        = $request->name;
        $District->division_id    = $request->division_id;
        $District->country_id  = $division->country_id;
        $District->cost        = priceFormatUpdate($request->cost,settingHelper('default_currency'));
        $District->save();
        return true;
    }
    public function DistrictUpdate($request)
    {
        $division             = Division::find($request->division_id);
        $District              = District::find($request->id);
        $District->name        = $request->name;
        $District->division_id    = $request->division_id;
        $District->country_id  = $division->country_id;
        $District->cost        = priceFormatUpdate($request->cost,settingHelper('default_currency'));
        $District->save();
        return true;
    }
    
      //Upazila
    public function upazilas()
    {
        return Upazila::with('country','division','district')->orderBy('name');
    }
    public function getUpazila($id)
    {
        return Upazila::with('country','division','district')->orderBy('name')->find($id);
    }

    public function getupazilasBydistrict($id)
    {
        return Upazila::where('status',1)->where('district_id',$id)->orderBy('name')->get();
    }

    public function upazilasPaginate($request, $limit)
    {

        return $this->upazilas()
            ->when($request->a != null, function ($query) use ($request){
                $query->where('district_id',$request->a);
            })
            ->when($request->q != null, function ($query) use ($request){
                $query->where('name', 'like', '%'.$request->q.'%');
            })
            ->paginate($limit);
    }
    public function upazilaStatusChange($request)
    {
        $upazila            = Upazila::find($request['id']);
        $upazila->status    = $request['status'];
        $upazila->save();
        return true;
    }
    
    public function upazilaStore($request)
    {
        $district             = District::find($request->district_id);
        $Upazila              = new Upazila();
        $Upazila->name        = $request->name;
        $Upazila->country_id  = $district->country_id;
        $Upazila->division_id = $district->division_id;
        $Upazila->district_id = $request->district_id;

        $Upazila->save();
        return true;
    }
    
    public function upazilaUpdate($request)
    {
        $Upazila              = Upazila::find($request->id);
        $Upazila->name        = $request->name;
        $Upazila->division_id = $request->division_id;
        $Upazila->district_id = $request->district_id;
        $Upazila->country_id  = $request->country_id;
        $Upazila->save();
        return true;
    }

    //Area
    public function areas()
    {
        return Area::with('country','division','district','upazila')->orderBy('name');
    }
    public function getArea($id)
    {
        return Area::with('country','division','district','upazila')->orderBy('name')->find($id);
    }

    public function getareasByupazila($id)
    {
        return Area::where('status',1)->where('upazila_id',$id)->orderBy('name')->get();
    }

    public function areasPaginate($request, $limit)
    {

        return $this->areas()
            ->when($request->a != null, function ($query) use ($request){
                $query->where('upazila_id',$request->a);
            })
            ->when($request->q != null, function ($query) use ($request){
                $query->where('name', 'like', '%'.$request->q.'%');
            })
            ->paginate($limit);
    }
    public function areaStatusChange($request)
    {
        $area            = Area::find($request['id']);
        $area->status    = $request['status'];
        $area->save();
        return true;
    }
    
    public function areaStore($request)
    {
        $upazila             = Upazila::find($request->upazila_id);
        $Area                 = new Area();
        $Area->name           = $request->name;
        $Area->country_id     = $upazila->country_id;
        $Area->division_id    = $upazila->division_id;
        $Area->district_id    = $upazila->district_id;
        $Area->upazila_id     = $request->upazila_id;
        $Area->save();
        return true;
    }
    
    public function areaUpdate($request)
    {
        $Area              = Area::find($request->id);
        $Area->name        = $request->name;
        $Area->division_id = $request->division_id;
        $Area->district_id = $request->district_id;
        $Area->country_id  = $request->country_id;
        $Area->upazila_id  = $request->upazila_id;
        $Area->save();
        return true;
    }

    public function countriesSearch($request)
    {
        return $this->countries()
            ->when($request->key != null, function ($query) use ($request){
                $query->where('name', 'like', '%'.$request->key.'%');
                $query->orWhere('iso3', 'like', '%'.$request->key.'%');
            })->get();
    }

    public function divisionImport()
    {
        division::truncate();
        $path   = base_path('public/sql/divisions.sql');
        $sql    = file_get_contents($path);
        \Illuminate\Support\Facades\DB::unprepared($sql);
        return true;
    }

}
