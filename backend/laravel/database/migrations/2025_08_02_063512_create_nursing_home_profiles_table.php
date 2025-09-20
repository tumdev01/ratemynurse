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
            $table->string('main_phone')->nullable();
            $table->string('res_phone')->nullable();
            $table->text('facebook')->nullable();
            $table->text('website')->nullable();
            $table->string('address')->nullable();

            // Nursing Home license info ข้อมูลใบอนุญาตและการรับรอง
            $table->string('license_no')->nullable();
            $table->date('license_start_date')->nullable();
            $table->date('license_exp_date')->nullable();
            $table->string('license_by')->nullable();
            $table->string('certificates')->nullable();
            $table->string('hospital_no')->nullable();

            // Additional Info ข้อมูลผู้รับผิดชอบ
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

            // บริการและการดูแล
            // ประเภทบริการ 
            $table->json('home_service_type')->nullable();
            $table->string('etc_service')->nullable();

            // บริการเสริม
            $table->json('additional_service_type')->nullable();

            // Home Accommodation สิ่งอำนวยความสะดวกและอุปกรณ์
            $table->integer('building_no')->default(0);
            $table->integer('total_room')->default(0);
            $table->integer('private_room_no')->default(0);
            $table->integer('duo_room_no')->default(0);
            $table->integer('shared_room_three_beds')->default(0);
            $table->integer('max_serve_no')->default(0);
            $table->double('area')->default(0.00);

            // ห้องพิเศษและสิ่งอำนวยความสะดวก
            $table->json('special_facilities')->nullable();

            // สิ่งอำนวยความสะดวกทั่วไป
            $table->json('facilities')->nullable();

            // ยานพาหนะและอุปกรณ์พิเศษ
            $table->boolean('ambulance')->default(0);
            $table->integer('ambulance_amount')->nullable()->default(0);
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

            $table->boolean('certified')->default(0);
            $table->string('youtube_url')->nullable();
            $table->string('map')->nullable();
            $table->text('map_embed')->nullable();
            $table->string('coords')->nullable();
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
