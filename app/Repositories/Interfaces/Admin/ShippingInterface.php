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

    public function countriesSearch($request);

    public function divisionImport();





}
