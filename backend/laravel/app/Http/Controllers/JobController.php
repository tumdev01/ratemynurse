<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\JobRepository;

class JobController extends Controller 
{
    public function index()
    {
        return view('pages.job.index');
    }

    public function jobPagination(Request $request, JobRepository $repo)
    {
        $filters = $request->only(['province','orderby','order']);
        return $repo->getJobDataTable($filters);
    }
}