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
        Schema::table('internship_submissions', function (Blueprint $table) {
            $table->dropColumn('letter_path');
        });

        Schema::table('submission_memberships', function (Blueprint $table) {
            $table->string('letter_path')->nullable()->after('rejection_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_memberships', function (Blueprint $table) {
            $table->dropColumn('letter_path');
        });

        Schema::table('internship_submissions', function (Blueprint $table) {
            $table->string('letter_path')->nullable()->after('rejection_note');
        });
    }
};
