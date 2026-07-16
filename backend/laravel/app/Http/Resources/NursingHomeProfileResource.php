<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NursingHomeProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'description' => $this->description,
            'youtube_url' => $this->youtube_url,
            'home_service_type' => $this->home_service_type,
            'additional_service_type' => $this->additional_service_type,
            'center_highlights' => $this->center_highlights,
            'building_no' => $this->building_no,
            'total_room' => $this->total_room,
            'private_room_no' => $this->private_room_no,
            'duo_room_no' => $this->duo_room_no,
            'shared_room_three_beds' => $this->shared_room_three_beds,
            'max_serve_no' => $this->max_serve_no,
            'area' => $this->area,
            'etc_service'=> $this->etc_service,
            'facilities' => $this->facilities,
            'special_facilities' => $this->special_facilities,
            'ambulance' => $this->ambulance,
            'ambulance_amount' => $this->ambulance_amount,
            'van_shuttle' => $this->van_shuttle,
            'special_medical_equipment' => $this->special_medical_equipment,
            'main_phone' => $this->main_phone,
            'res_phone'  => $this->res_phone,
            'facebook'   => $this->facebook,
            'lineid'     => $this->lineid,
            'website'    => $this->website,
            'address'    => $this->address,
            'province'   => $this->whenLoaded('province', function () {
                return [
                    'id' => $this->province->id,
                    'name' => $this->province->name
                ];
            }),
            'district'   => $this->whenLoaded('district', function() {
                return [
                    'id' => $this->district->id,
                    'name' => $this->district->name
                ];
            }),
            'subDistrict' => $this->whenLoaded('subDistrict', function() {
                return [
                    'id' => $this->subDistrict->id,
                    'name' => $this->subDistrict->name
                ];
            }),
            'zipcode'   => $this->zipcode,
            'license_no'=> $this->license_no,
            'license_start_date' => $this->license_start_date,
            'license_exp_date'   => $this->license_exp_date,
            'license_by'         => $this->license_by,
            'certificates'       => $this->certificates,
            'hospital_no'        => $this->hospital_no,
            'cost_per_day'       => $this->cost_per_day,
            'cost_per_month'     => $this->cost_per_month,
            'deposit'            => $this->deposit,
            'registration_fee'   => $this->registration_fee,
            'special_food_expenses'  => $this->special_food_expenses,
            'physical_therapy_fee'   => $this->physical_therapy_fee,
            'delivery_fee'       => $this->delivery_fee,
            'laundry_service'    => $this->laundry_service,
            'social_security'    => (int) $this->social_security,
            'private_health_insurance'  => (int) $this->private_health_insurance,
            'installment'        => (int) $this->installment,
            'payment_methods'    => $this->payment_methods,
            'cover_image' => $this->whenLoaded('coverImage', function () {
                if (!$this->coverImage) return null;
                return [
                    'id' => $this->coverImage->id,
                    'full_path' => $this->coverImage->full_path,
                ];
            }),
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn($img) => [
                    'id' => $img->id,
                    'full_path' => $img->full_path,
                ]);
            }),
            'licenses' => $this->whenLoaded('licenses', function () {
                return $this->licenses->map(fn($lic) => [
                    'id' => $lic->id,
                    'filename' => $lic->filename,
                    'filetype' => $lic->filetype,
                    'full_path' => $lic->full_path,
                ]);
            }),
            'staffs' => $this->whenLoaded('staffs', function () {
                return $this->staffs->map(fn($s) => [
                    'id'             => $s->id,
                    'name'           => $s->name,
                    'responsibility' => $s->responsibility,
                    'image'          => $s->image,
                    'full_path'      => $s->full_path,
                ]);
            }),
        ];
    }
}