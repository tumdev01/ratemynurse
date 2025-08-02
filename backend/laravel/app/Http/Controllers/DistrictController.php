<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\DistrictResource;
use App\Repositories\DistrictRepository;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    /**
     * @var DistrictRepository
     */
    protected $district_repository;

    /**
     * DistrictController constructor.
     *
     * @param DistrictRepository $district_repository
     */
    public function __construct(DistrictRepository $district_repository)
    {
        $this->district_repository = $district_repository;
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getDistricts()
    {
        $districts = $this->district_repository->getDistricts();

        return DistrictResource::collection($districts);
    }

    /**
     * @param $province_id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getDistrictsByProvinceId($province_id, Request $request)
    {
        $districts = $this->district_repository->getDistrictsByProvinceId($province_id, $request->all());

        return DistrictResource::collection($districts);
    }
}

