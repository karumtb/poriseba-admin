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
        Schema::create('enrolments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vendor_id');


            $table->string('first_name');

            $table->string('last_name');

            $table->string('email');

            $table->string('phone');

            $table->text('address');

            $table->string('city');

            $table->string('state');

            $table->string('pin_code', 10);

            $table->string('highest_qualification')->nullable();

            $table->string('designation')->nullable();

            $table->text('specialization')->nullable();

            $table->string('experience')->nullable();

            $table->string('profile_photo')->nullable();

            $table->string('adhar_pdf')->nullable();

            $table->string('certificate_pdf')->nullable();

            $table->enum('status', ['vendor', 'customer', 'admin'])->default('vendor');


            $table->softDeletes(); // deleted_at

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrolments');
    }
};
