<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NursingCvImageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'path'     => $this->path,
            'name'     => $this->name,
            'filetype' => $this->filetype,
        ];
    }
}
