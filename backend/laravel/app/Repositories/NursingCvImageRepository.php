<?php

namespace App\Repositories;

use App\Models\NursingCvImage;

class NursingCvImageRepository {
    public function delete(Int $id) {

        $cvImg = NursingCvImage::find($id);

        if (!$cvImg) {
            return null; // หรือ throw exception
        }

        $cvImg->delete();

        return $cvImg;
    }
}
