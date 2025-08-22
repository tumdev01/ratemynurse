<?php

namespace App\Http\Controllers;
use App\Repositories\NursingHomeRepository;
use Illuminate\Http\Request;

class NursingHomeController extends Controller {
    protected $nursing_home_repository;

    public function __construct(NursingHomeRepository $nursing_home_repository)
    {
        $this->nursing_home_repository = $nursing_home_repository;
    }
    public function index() {
        return view('pages.nursinghome.index');
    }

    public function getNursingHomePagination(Request $request, NursingHomeRepository $repo) {
        $filters = $request->only(['certified','province','orderby','order']);
        return $this->nursing_home_repository->getNursingHomeDataTable($filters);
    }
}