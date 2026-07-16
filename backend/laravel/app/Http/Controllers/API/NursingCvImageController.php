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

    public function delete($id)
    {
        try {
            $image = \App\Models\CvImage::findOrFail($id);
            
            // ลบไฟล์จริง
            $filePath = public_path($image->path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // ลบ record
            $image->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'ลบไฟล์สำเร็จ'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error deleting CV file: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถลบไฟล์ได้: ' . $e->getMessage()
            ], 500);
        }
    }
}
