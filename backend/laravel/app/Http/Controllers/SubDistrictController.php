<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubDistrictResource;
use App\Repositories\SubDistrictRepository;
use Illuminate\Http\Request;

class SubDistrictController extends Controller
{
    /**
     * @var SubDistrictRepository
     */
    protected $sub_district_repository;

    /**
     * SubDistrictController constructor.
     *
     * @param SubDistrictRepository $sub_district_repository
     */
    public function __construct(SubDistrictRepository $sub_district_repository)
    {
        $this->sub_district_repository = $sub_district_repository;
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getSubDistricts()
    {
        $sub_districts = $this->sub_district_repository->getSubDistricts();

        return SubDistrictResource::collection($sub_districts);
    }

    /**
     * @param $province_id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getSubDistrictsByProvinceId($province_id)
    {
        $sub_districts = $this->sub_district_repository->getSubDistrictsByProvinceId($province_id);

        return SubDistrictResource::collection($sub_districts);
    }

    /**
     * @param $district_id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getSubDistrictsByDistrictId($district_id, Request $request)
    {
        $sub_districts = $this->sub_district_repository->getSubDistrictsByDistrictId($district_id, $request->all());

        return SubDistrictResource::collection($sub_districts);
    }
}
