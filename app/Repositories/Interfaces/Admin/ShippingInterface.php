<?php

namespace App\Repositories\Interfaces\Admin;

interface ShippingInterface {

    public function countries();
    public function getAllCountries();
    public function userCountries();
    public function countriesPaginate($request, $limit);
    public function countryStatusChange($request);

    public function getDivision($id);
    public function getDivisionByCountry($id);
    public function divisions();
    public function divisionsPaginate($request, $limit);
    public function divisionStatusChange($request);
    public function divisionStore($request);
    public function divisionUpdate($request);

    public function getDistrict($id);
    public function getDistrictsByDivision($id);
    public function districts();
    public function districtsPaginate($request, $limit);
    public function districtStatusChange($request);
    public function districtStore($request);
    public function districtUpdate($request);

    public function getupazila($id);
    public function getupazilasBydistrict($id);
    public function upazilas();
    public function upazilasPaginate($request, $limit);
    public function upazilaStatusChange($request);
    public function upazilaStore($request);
    public function upazilaUpdate($request);
    
    public function getarea($id);
    public function getareasByupazila($id);
    public function areas();
    public function areasPaginate($request, $limit);
    public function areaStatusChange($request);
    public function areaStore($request);
    public function areaUpdate($request);

    public function countriesSearch($request);

    public function divisionImport();





}
