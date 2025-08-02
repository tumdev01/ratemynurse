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

        $nursings = $this->nursing_home_repository->getNursingHomes(['limit' => $limit]);
        return response()->json($nursings);
    }
}
