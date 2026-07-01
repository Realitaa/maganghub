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
        Schema::table('group_memberships', function (Blueprint $table) {
            // Drop the old unique constraint on user_id
            $table->dropUnique('group_memberships_user_id_unique');
            
            // Add a status column to track interning_elsewhere, active, etc.
            $table->string('status')->default('active')->after('user_id');
            
            // Add composite unique constraint
            $table->unique(['group_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_memberships', function (Blueprint $table) {
            $table->dropUnique(['group_id', 'user_id']);
            $table->dropColumn('status');
            $table->unique('user_id', 'group_memberships_user_id_unique');
        });
    }
};
