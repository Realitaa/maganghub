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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nim')->unique()->nullable();
            $table->string('major')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('role')->default('student');
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->boolean('is_active')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nim', 'major', 'phone', 'address', 'role', 'gender', 'is_active']);
        });
    }
};
