<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\API\NursingCvImageRepository;
use Exception;

class NursingCvImageController extends Controller
{
    protected $nursing_cv_image_repo;

    public function __construct(NursingCvImageRepository $nursing_cv_image_repo)
    {
        $this->nursing_cv_image_repo = $nursing_cv_image_repo;
    }

    public function delete(Int $id)
    {
        try {
            $response = $this->nursing_cv_image_repo->delete((int) $id);

            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully.'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
