<?php

namespace App\Repositories\Admin;

use App\Models\District;
use App\Models\Country;
use App\Models\Division;
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
        return division::with('country')->orderBy('name');
    }
    public function getDivisionByCountry($id)
    {
        return Division::where('status',1)->where('country_id',$id)->orderBy('name')->get();
    }
    public function getDivision($id)
    {
        return Division::with('country')->find($id);
    }
    public function divisionsPaginate($request ,$limit)
    {
        return $this->division()
            ->when($request->a != null, function ($query) use ($request){
                $query->where('country_id',$request->a);
            })
            ->when($request->q != null, function ($query) use ($request){
                $query->where('name', 'like', '%'.$request->q.'%');
            })
            ->paginate($limit);
    
    
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
            $division              = Division::find($request->id);
            $division->name        = $request->name;
            $division->country_id  = $request->country_id;
            $division->save();
            return true;
    }

    //district
    public function districts()
    {
        return District::with('country','division')->orderBy('name');
    }
    public function getDistrict($id)
    {
        return District::with('country','division')->orderBy('name')->find($id);
    }

    public function getDistrictsByDivision($id)
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
    public function districtStatusChange($request)
    {
        $country            = district::find($request['id']);
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

    public function districtStore($request)
    {
        $division             = division::find($request->division_id);
        $district              = new District();
        $district->name        = $request->name;
        $district->division_id    = $request->division_id;
        $district->country_id  = $division->country_id;
        $district->cost        = priceFormatUpdate($request->cost,settingHelper('default_currency'));
        $district->save();
        return true;
    }
    public function districtUpdate($request)
    {
        $division             = Division::find($request->division_id);
        $district              = District::find($request->id);
        $district->name        = $request->name;
        $district->division_id    = $request->division_id;
        $district->country_id  = $division->country_id;
        $district->cost        = priceFormatUpdate($request->cost,settingHelper('default_currency'));
        $district->save();
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
        Division::truncate();
        $path   = base_path('public/sql/divisions.sql');
        $sql    = file_get_contents($path);
        \Illuminate\Support\Facades\DB::unprepared($sql);
        return true;
    }

}
