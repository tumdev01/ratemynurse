<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nursing_home_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // make nullable
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
                
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('main-phone')->nullable();
            $table->string('res-phone')->nullable();
            $table->string('facebook')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();

            // Nursing Home license info
            $table->string('license_no')->nullable();
            $table->date('license_start_date')->nullable();
            $table->date('license_exp_date')->nullable();
            $table->string('license_by')->nullable();
            $table->string('certificates')->nullable();
            $table->string('hospital_no')->nullable();

            // Additional Info
            $table->string('manager_name')->nullable();
            $table->string('graduated')->nullable();
            $table->string('graduated_paper')->nullable();
            $table->integer('exp_year')->nullable();
            $table->string('manager_phone')->nullable();
            $table->string('manager_email')->nullable();

            // In house Doctor / Assistance
            $table->string('assist_name')->nullable();
            $table->string('assist_no')->nullable();
            $table->string('assist_expert')->nullable();
            $table->string('assist_phone')->nullable();

            // Home Accommodation
            $table->integer('building_no')->default(0);
            $table->integer('total_room')->default(0);
            $table->integer('private_room_no')->default(0);
            $table->integer('duo_room_no')->default(0);
            $table->integer('shared_room_three_beds')->default(0);
            $table->integer('max_serve_no')->default(0);
            $table->double('area')->default(0.00);

            // ห้องพิเศษและสิ่งอำนวยความสะดวก
            $table->boolean('nurse_station')->default(0);
            $table->boolean('emergency_room')->default(0);
            $table->boolean('examination_room')->default(0);
            $table->boolean('medicine_room')->default(0);
            $table->boolean('kitchen_cafeteria')->default(0);
            $table->boolean('dining_room')->default(0);
            $table->boolean('activity_room')->default(0);
            $table->boolean('physical_therapy_room')->default(0);
            $table->boolean('meeting_room')->default(0);
            $table->boolean('office_room')->default(0);
            $table->boolean('laundry_room')->default(0);

            // สิ่งอำนวยความสะดวกทั่วไป
            $table->boolean('elevator')->default(0);
            $table->boolean('wheelchair_ramp')->default(0);
            $table->boolean('bathroom_grab_bar')->default(0);
            $table->boolean('emergency_bell')->default(0);
            $table->boolean('camera')->default(0);
            $table->boolean('fire_extinguishing_system')->default(0);
            $table->boolean('backup_generator')->default(0);
            $table->boolean('air_conditioner')->default(0);
            $table->boolean('garden_area')->default(0);
            $table->boolean('parking')->default(0);
            $table->boolean('wifi_internet')->default(0);
            $table->boolean('central_television')->default(0);

            // ยานพาหนะและอุปกรณ์พิเศษ
            $table->boolean('ambulance')->default(0);
            $table->integer('ambulance_amount')->default(0);
            $table->boolean('van_shuttle')->default(0);
            $table->string('special_medical_equipment')->nullable();

            // In house staff ข้อมูลบุคลากร
            $table->integer('total_staff')->default(0);
            $table->integer('total_fulltime_nurse')->default(0);
            $table->integer('total_parttime_nurse')->default(0);
            $table->integer('total_nursing_assistant')->default(0);
            $table->integer('total_regular_doctor')->default(0);
            $table->integer('total_physical_therapist')->default(0);
            $table->integer('total_pharmacist')->default(0);
            $table->integer('total_nutritionist')->default(0);
            $table->integer('total_social_worker')->default(0);
            $table->integer('total_general_employees')->default(0);
            $table->integer('total_security_officer')->default(0);

            
            //ค่าบริการพื้นฐาน
            $table->double('cost_per_day')->default(0);
            $table->double('cost_per_month')->default(0);
            $table->double('deposit')->default(0);
            $table->double('registration_fee')->default(0);

            // ค่าบริการเพิ่มเติม
            $table->double('special_food_expenses')->default(0);
            $table->double('physical_therapy_fee')->default(0);
            $table->double('delivery_fee')->default(0);
            $table->double('laundry_service')->default(0);

            // การรับประกันและการเงิน
            $table->boolean('social_security')->default(0);
            $table->boolean('private_health_insurance')->default(0);
            $table->boolean('installment')->default(0);
            $table->string('payment_methods')->nullable();

            // ข้อมูลเพิ่มเติม
            $table->string('center_highlights')->nullable();
            $table->string('patients_target')->nullable();
            $table->string('visiting_time')->nullable();
            $table->string('patient_admission_policy')->nullable();
            $table->string('emergency_contact_information')->nullable();
            $table->string('additional_notes')->nullable();

            $table->foreignId('sub_district_id')->nullable()
                ->references('id')->on('sub_districts')
                ->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('district_id')->nullable()
                ->references('id')->on('districts')
                ->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('province_id')->nullable()
                ->references('id')->on('provinces')
                ->cascadeOnUpdate()->nullOnDelete();
            $table->string('zipcode');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nursing_home_profiles');
    }
};
