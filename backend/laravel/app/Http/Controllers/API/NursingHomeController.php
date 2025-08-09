<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NursingHome;
use App\Repositories\NursingHomeRepository;

class NursingHomeController extends Controller {
    protected $nursing_home_repository;

    public function __construct(NursingHomeRepository $nursing_home_repository)
    {
        $this->nursing_home_repository = $nursing_home_repository;
    }

    public function getNursingHomes(Request $request)
    {
        $limit = $request->input('limit');
        $certified = $request->input('certified');

        $nursings = $this->nursing_home_repository->getNursingHomes(['limit' => $limit, 'certified' => $certified]);
        return response()->json($nursings);
    }

    public function getNuringHomePagination(Request $request)
    {
        $limit = $request->input('limit');
        $certified = $request->input('certified');
        $orderby  = $request->input('order_by');
        $order     = $request->input('order');

        $homes = $this->nursing_home_repository->getNuringHomePagination([
            'limit' => $limit,
            'certified' => $certified,
            'orderby' => $orderby,
            'order' => $order
        ]);
        
        return response()->json([
            'data' => $homes->items(),
            'total' => $homes->total(),
            'per_page' => $homes->perPage(),
            'current_page' => $homes->currentPage(),
            'last_page' => $homes->lastPage(),
        ]);
    }

    public function getNursingHome(int $id) {
        $result = $this->nursing_home_repository->getInfo((int) $id);
        return response()->json($result);
    }
}
