<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobInterviewApplyRequest;
use App\Models\Nursing;
use App\Models\NursingHome;
use App\Repositories\JobInterviewRepository;
use Illuminate\Http\Request;

class JobInterviewController extends Controller
{
    protected $repo;

    public function __construct(JobInterviewRepository $repo)
    {
        $this->repo = $repo;
    }

    public function applyNursingJob(JobInterviewApplyRequest $request)
    {
        $user = $request->user();

        $classMap = [
            'NURSING'      => Nursing::class,
            'NURSING_HOME' => NursingHome::class,
        ];

        $interview = $this->repo->applyJob([
            'job_id'         => $request->job_id,
            'user_id'        => $user->id,
            'class_type'     => $classMap[$request->type] ?? null,
            'profile_id'     => $request->profile_id,
            'type'           => $request->type,
            'description'    => $request->message,
            'price'          => $request->price,
            'start_date'     => $request->start_date,
            'attach_profile' => $request->attach_profile ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'นำเสนองานสำเร็จ',
            'data'    => $interview,
        ]);
    }

    public function getInterviews(Request $request)
    {
        $user = $request->user();

        $result = $this->repo->getInterviews($user->id, $request->all());

        return response()->json([
            'success' => true,
            'data'    => [
                'results'    => $result->items(),
                'pagination' => [
                    'total'        => $result->total(),
                    'current_page' => $result->currentPage(),
                    'last_page'    => $result->lastPage(),
                    'from'         => $result->firstItem(),
                    'to'           => $result->lastItem(),
                ],
            ],
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $result = $this->repo->destroy($id, $request->user()->id);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['code']);
    }
}