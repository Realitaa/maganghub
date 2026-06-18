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
        Schema::create('internship_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('internship_groups')->cascadeOnDelete();
            $table->string('company_name');
            $table->text('company_address');
            $table->string('company_contact');
            $table->string('division');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('supporting_document')->nullable();
            $table->string('status')->default('submitted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_submissions');
    }
};
