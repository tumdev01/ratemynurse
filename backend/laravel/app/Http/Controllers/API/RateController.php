<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RateCreateRequest;

class RateController extends Controller {

    public function create(RateCreateRequest $request)
    {
        dd($request);
    }

}