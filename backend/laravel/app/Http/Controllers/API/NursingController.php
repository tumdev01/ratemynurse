<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nursing;
use App\Repositories\NursingRepository;
use App\Enums\ExpertiseType;
use App\Enums\ZoneType;
use App\Repositories\ProvinceRepository;
use App\Http\Requests\NursingCreateRequest;
use App\Repositories\API\NursingApiRepository;

class NursingController extends Controller {
    protected $nursing_repository;
    protected $province_repository;
    protected $nursing_api_repository;
    public function __construct(NursingRepository $nursing_repository, ProvinceRepository $province_repository, NursingApiRepository $nursing_api_repository)
    {
        $this->nursing_repository = $nursing_repository;
        $this->province_repository= $province_repository;
        $this->nursing_api_repository = $nursing_api_repository;
    }

    public function store(NursingCreateRequest $request)
    {
        try {
            $nursing = $this->nursing_api_repository->createNurse($request->all());

            // Check if creation succeeded
            if (!$nursing || !$nursing->exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถบันทึกผู้ใช้ได้',
                ], 500);
            }

            // Check if token can be created
            if (!method_exists($nursing, 'createToken')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create token for user',
                ], 500);
            }

            $token = $nursing->createToken('api-token')->plainTextToken;

            // Check if token creation succeeded
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate access token',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $nursing,
                    'access_token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getNursing(Request $request)
    {
        $limit = $request->input('limit');
        $certified = $request->input('certified') ?? false;

        $nursings = $this->nursing_repository->getNursing(['limit' => $limit, 'certified' => $certified]);
        return response()->json($nursings);
    }

    public function getNursingPagination(Request $request)
    {
        $limit = $request->input('limit', 10);
        $certified = $request->input('certified');
        $order = $request->input('order', 'desc');
        $orderby = $request->input('orderby', 'created_at');

        $nursings = $this->nursing_repository->getNursingPagination([
            'limit' => $limit,
            'certified' => $certified,
            'orderby' => $orderby,
            'order' => $order
        ]);
        return response()->json([
            'data' => $nursings->items(),
            'total' => $nursings->total(),
            'per_page' => $nursings->perPage(),
            'current_page' => $nursings->currentPage(),
            'last_page' => $nursings->lastPage(),
        ]);
    }

    public function getFilterElements()
    {
        $expertise = ExpertiseType::list();

        return response()->json([
            'expertises' => $expertise
        ]);
    }

    public function getLocations()
    {
        $provinces = $this->province_repository->getProvinceDropdown();
        $provincesGroupBy = $provinces->groupBy('zone');
        $result = $provincesGroupBy->toArray();
        return response()->json([
            'data' => $result
        ]);
    }

    public function getNursingByLocation(Request $request) {
        dd($request->all());
    }

    public function getNursingById(Int $id)
    {
        $result = $this->nursing_repository->getNursingById((int) $id);
        return response()->json($result);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed'
            ], 403);
        }

        try {
            $result = $this->nursing_api_repository->updateProfile($request->all(), $user->id);

            return response()->json([
                'success' => true,
                'message' => $request->has('id') ? 'Profile updated successfully' : 'Profile created successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
