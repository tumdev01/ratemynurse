<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NursingHomeProfile extends Model
{
    use HasFactory,SoftDeletes;
    use \App\Traits\HasSubscriptions;

    protected $table = 'nursing_home_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'description',
        'main_phone',
        'res_phone',
        'facebook',
        'website',
        'address',
        'license_no',
        'license_start_date',
        'license_exp_date',
        'license_by',
        'certificates',
        'hospital_no',
        'manager_name',
        'graduated',
        'graduated_paper',
        'exp_year',
        'manager_phone',
        'manager_email',
        'assist_name',
        'assist_no',
        'assist_expert',
        'assist_phone',
        'home_service_type',
        'etc_service',
        'additional_service_type',
        'building_no',
        'total_room',
        'private_room_no',
        'duo_room_no',
        'shared_room_three_beds',
        'max_serve_no',
        'area',
        'special_facilities',
        'facilities',
        'ambulance',
        'ambulance_amount',
        'van_shuttle',
        'special_medical_equipment',
        'total_staff',
        'total_fulltime_nurse',
        'total_parttime_nurse',
        'total_nursing_assistant',
        'total_regular_doctor',
        'total_physical_therapist',
        'total_pharmacist',
        'total_nutritionist',
        'total_social_worker',
        'total_general_employees',
        'total_security_officer',
        'cost_per_day',
        'cost_per_month',
        'deposit',
        'registration_fee',
        'special_food_expenses',
        'physical_therapy_fee',
        'delivery_fee',
        'laundry_service',
        'social_security',
        'private_health_insurance',
        'installment',
        'payment_methods',
        'center_highlights',
        'patients_target',
        'visiting_time',
        'patient_admission_policy',
        'emergency_contact_information',
        'additional_notes',
        'province_id',
        'district_id',
        'sub_district_id',
        'zipcode',
        'certified',
        'youtube_url',
        'map',
        'map_embed',
        'map_show',
        'coords',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'name'    => 'string',
        'description'=> 'string',
        'main-phone'=> 'string',
        'res-phone'=> 'string',
        'facebook'=> 'string',
        'website'=> 'string',
        'address'=> 'string',
        'license_no'=> 'string',
        'license_start_date' => 'date',
        'license_exp_date' => 'date',
        'license_by'=> 'string',
        'certificates'=> 'string',
        'hospital_no'=> 'string',
        'manager_name'=> 'string',
        'graduated'=> 'string',
        'graduated_paper'=> 'string',
        'exp_year'=> 'string',
        'manager_phone'=> 'string',
        'manager_email'=> 'string',
        'assist_name'=> 'string',
        'assist_no' => 'integer',
        'assist_expert'=> 'string',
        'assist_phone'=> 'string',
        'building_no' => 'integer',
        'total_room' => 'integer',
        'private_room_no' => 'integer',
        'duo_room_no' => 'integer',
        'shared_room_three_beds' => 'integer',
        'max_serve_no' => 'integer',
        'area' => 'double',
        'nurse_station' => 'boolean',
        'emergency_room' => 'boolean',
        'examination_room' => 'boolean',
        'medicine_room' => 'boolean',
        'kitchen_cafeteria' => 'boolean',
        'dining_room' => 'boolean',
        'activity_room' => 'boolean',
        'physical_therapy_room' => 'boolean',
        'meeting_room' => 'boolean',
        'office_room' => 'boolean',
        'laundry_room' => 'boolean',
        'elevator' => 'boolean',
        'wheelchair_ramp' => 'boolean',
        'bathroom_grab_bar' => 'boolean',
        'emergency_bell' => 'boolean',
        'camera' => 'boolean',
        'fire_extinguishing_system' => 'boolean',
        'backup_generator' => 'boolean',
        'air_conditioner' => 'boolean',
        'garden_area' => 'boolean',
        'parking' => 'boolean',
        'wifi_internet' => 'boolean',
        'central_television' => 'boolean',
        'ambulance' => 'boolean',
        'ambulance_amount' => 'integer',
        'van_shuttle' => 'boolean',
        'special_medical_equipment' => 'string',
        'total_staff' => 'integer',
        'total_fulltime_nurse' => 'integer',
        'total_parttime_nurse' => 'integer',
        'total_nursing_assistant' => 'integer',
        'total_regular_doctor' => 'integer',
        'total_physical_therapist' => 'integer',
        'total_pharmacist' => 'integer',
        'total_nutritionist' => 'integer',
        'total_social_worker' => 'integer',
        'total_general_employees' => 'integer',
        'total_security_officer' => 'integer',
        'cost_per_day' => 'double',
        'cost_per_month' => 'double',
        'deposit' => 'double',
        'registration_fee' => 'double',
        'special_food_expenses' => 'double',
        'physical_therapy_fee' => 'double',
        'delivery_fee' => 'double',
        'laundry_service' => 'double',
        'social_security' => 'boolean',
        'private_health_insurance' => 'boolean',
        'installment' => 'boolean',
        'payment_methods' => 'string',
        'center_highlights' => 'string',
        'patients_target' => 'string',
        'visiting_time' => 'string',
        'patient_admission_policy' => 'string',
        'emergency_contact_information' => 'string',
        'additional_notes' => 'string',
        'province_id' => 'integer',
        'district_id' => 'integer',
        'sub_district_id' => 'integer',
        'zipcode' => 'string',
        'certified' => 'boolean',
        'youtube_url' => 'string',
        'map' => 'string',
        'map_embed'=> 'string',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\User
     */
    // Relation กับเจ้าของบ้าน
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Relation province/district/subDistrict
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function subDistrict()
    {
        return $this->belongsTo(SubDistrict::class, 'sub_district_id', 'id');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable')->where('is_cover', false);
    }

    public function coverImage()
    {
        return $this->morphOne(Image::class, 'imageable')->where('is_cover', true);
    }

    public function staffs()
    {
        return $this->hasMany(NursingHomeStaff::class, 'user_id', 'id');
    }

    // Rates
    public function rates()
    {
        return $this->morphMany(Rate::class, 'rateable');
    }

    public function rooms()
    {
        return $this->hasMany(NursingHomeRoom::class, 'user_id', 'id');
    }

    public function licenses()
    {
        return $this->hasmany(NursingHomeLicenseImage::class, 'profile_id', 'id');
    }
}
