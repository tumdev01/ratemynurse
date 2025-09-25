<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\JobRepository;
use App\Models\Job;

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

    public function edit(Int $id)
    {
        dd($id);
        $job = Job::where('id', $id)->first();
        return view('pages.job.edit', compact('job'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:OPEN,CLOSED,EXPIRED',
        ]);

        $job = Job::findOrFail($id);
        $job->status = $request->status;
        $job->save();

        return response()->json([
            'success' => true,
            'message' => "อัพเดทสถานะงาน #{$id} สำเร็จ",
        ]);
    }

}