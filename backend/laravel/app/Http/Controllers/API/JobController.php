<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JobController extends Controller {
    public function jobFilters(Request $request)
    {
        dd($request->all());
    }
}