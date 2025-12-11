<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\NursingCvImageResource;
class NursingCvResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'graducated' => $this->graducated,
            'edu_ins' => $this->edu_ins,
            'graducated_year' => $this->graducated_year,
            'gpa' => $this->gpa,
            'cert_no' => $this->cert_no,
            'cert_date' => $this->cert_date,
            'cert_expire' => $this->cert_expire,
            'cert_etc' => $this->cert_etc,
            'extra_courses' => $this->extra_courses,
            'current_workplace' => $this->current_workplace,
            'department' => $this->department,
            'position' => $this->position,
            'exp' => $this->exp,
            'work_type' => $this->work_type,
            'extra_shirft' => $this->extra_shirft,
            'languages' => $this->languages,

            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($img) {
                    return [
                        'id' => $img->id,
                        'cv_id' => $img->cv_id,
                        'name' => $img->name,
                        'path' => $img->path,
                        'filetype' => $img->filetype,
                        'full_path' => $img->full_path,
                    ];
                });
            }),
        ];
    }
}
