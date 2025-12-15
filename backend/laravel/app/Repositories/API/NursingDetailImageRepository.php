<?php

namespace App\Repositories\API;

use App\Models\NursingDetailImage;

class NursingDetailImageRepository {
    public function delete(Int $id) {

        $detailImage = NursingDetailImage::find($id);

        if (!$detailImage) {
            return null; // หรือ throw exception
        }

        $detailImage->delete();

        return $detailImage;
    }
}
