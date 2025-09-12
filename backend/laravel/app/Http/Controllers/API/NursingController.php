<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nursing;
use App\Repositories\NursingRepository;
use App\Enums\ExpertiseType;
use App\Enums\ZoneType;
use App\Repositories\ProvinceRepository;

class NursingController extends Controller {
    protected $nursing_repository;
    protected $province_repository;
    public function __construct(NursingRepository $nursing_repository, ProvinceRepository $province_repository)
    {
        $this->nursing_repository = $nursing_repository;
        $this->province_repository= $province_repository;
    }

    public function getNursing(Request $request)
    {
        $limit = $request->input('limit');
        $certified = $request->input('certified') ?? false;

        $nursings = $this->nursing_repository->getNursing(['limit' => $limit, 'certified' => $certified]);
        return response()->json($nursings);
    }

    public function getNursingPagination(Request $request)
    {
        $limit = $request->input('limit', 10);
        $certified = $request->input('certified');
        $order = $request->input('order', 'desc');
        $orderby = $request->input('orderby', 'created_at');

        $nursings = $this->nursing_repository->getNursingPagination([
            'limit' => $limit,
            'certified' => $certified,
            'orderby' => $orderby,
            'order' => $order
        ]);
        return response()->json([
            'data' => $nursings->items(),
            'total' => $nursings->total(),
            'per_page' => $nursings->perPage(),
            'current_page' => $nursings->currentPage(),
            'last_page' => $nursings->lastPage(),
        ]);
    }

    public function getFilterElements()
    {
        $expertise = ExpertiseType::list();

        return response()->json([
            'expertises' => $expertise
        ]);
    }

    public function getLocations()
    {
        $provinces = $this->province_repository->getProvinceDropdown();
        $provincesGroupBy = $provinces->groupBy('zone');
        $result = $provincesGroupBy->toArray();
        return response()->json([
            'data' => $result
        ]);
    }

    public function getNursingByLocation(Request $request) {
        dd($request->all());
    }

    public function getNursingById(Int $id)
    {
        $result = $this->nursing_repository->getNursingById((int) $id);
        return response()->json($result);
    }

}
