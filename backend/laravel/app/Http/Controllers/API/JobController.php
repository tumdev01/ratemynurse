<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\JobRepository;
use App\Http\Requests\JobCreateRequest;
use Illuminate\Database\QueryException;

class JobController extends Controller {
    protected $job_repository;
    public function __construct(JobRepository $job_repository)
    {
        $this->job_repository = $job_repository;
    }
    public function jobFilters(Request $request)
    {
        dd($request->all());
    }

    public function store(JobCreateRequest $request, JobRepository $repo)
    {
        try {
            $data = $request->validated(); // รวม user_id แล้ว
            $result = $repo->store($data);
            return response()->json([
                'status'  => 'success',
                'message' => 'บันทึกสำเร็จ',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getJobList(Request $request)
    {
        try {
            $jobs = $this->job_repository->getJobPagination($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'success',
                'results' => $jobs->items(), // หรือ $jobs->toArray()['data']
                'pagination' => [
                    'current_page' => $jobs->currentPage(),
                    'per_page'     => $jobs->perPage(),
                    'total'        => $jobs->total(),
                    'last_page'    => $jobs->lastPage(),
                    'next'         => $jobs->nextPageUrl(),
                    'prev'         => $jobs->previousPageUrl(),
                    'from'         => $jobs->firstItem(),
                    'to'           => $jobs->lastItem(),
                ],
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getJob(Int $id)
    {
        $job = $this->job_repository->getJob((int) $id);
        return response()->json([
            'success' => true,
            'message' => 'success',
            'results' => $job
        ], 200);
    }

    public function close(Int $id, Request $request)
    {
        $owner = $request->user();
        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $job = $this->job_repository->getJob((int) $id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        if ((int) $job->user_id !== (int) $owner->id) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        $job->status = 'CLOSED';

        if ($job->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Job closed successfully'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to close job'
        ], 500);
    }

}