<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RateCreateRequest;
use App\Repositories\RateRepository;

class RateController extends Controller {

    protected $rate_repository;

    public function __construct(RateRepository $rate_repository) {
        $this->rate_repository = $rate_repository;
    }

    public function create(RateCreateRequest $request)
    {
        return $this->rate_repository->create($request->all());
    }

}