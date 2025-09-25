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

    public function create()
    {
        return view('pages.job.create');
    }

    public function store(Request $request, JobRepository $repo)
    {
        try {
            $repo->store($request->all());

            return redirect()
                ->route('job.index')
                ->with('success', 'บันทึกสำเร็จ');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}