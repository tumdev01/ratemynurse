<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ProvinceRepository;
use App\Http\Resources\ProvinceResource;

class ProvinceController extends Controller
{
    public function __construct(
        ProvinceRepository $province_repository
    )
    {
        $this->province_repository = $province_repository;
    }
    public function getProvinces(Request $request)
    {
        $provinces = $this->province_repository->getProvinceDropdown($request->all());
        return ProvinceResource::collection($provinces);
    }

    public function getProvinceById(int $id) {
        return $this->province_repository->getProvinceById((int) $id);
    }
}
