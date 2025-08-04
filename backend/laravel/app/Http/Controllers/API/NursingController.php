<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nursing;
use App\Repositories\NursingRepository;

class NursingController extends Controller {
    protected $nursing_repository;

    public function __construct(NursingRepository $nursing_repository)
    {
        $this->nursing_repository = $nursing_repository;
    }

    public function getNursing(Request $request)
    {
        $limit = $request->input('limit');
        $certified = $request->input('certified') ?? false;

        $nursings = $this->nursing_repository->getNursing(['limit' => $limit, 'certified' => $certified]);
        return response()->json($nursings);
    }
}
