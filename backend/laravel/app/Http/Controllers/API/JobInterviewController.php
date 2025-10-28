<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JobInterviewController extends Controller 
{
    public function __construct()
    {

    }
    
    public function applyNursingJob(Request $request)
    {
        dd($request->all());
    }
}