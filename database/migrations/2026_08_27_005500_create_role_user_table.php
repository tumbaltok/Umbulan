<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->boolean('is_primary')->default(false);
                $table->timestamps();

                $table->unique(['user_id', 'role_id']);
            });
        }

        // Otomatis migrasikan data role lama dari kolom users.role_id ke pivot role_user (Fallback safety)
        if (Schema::hasColumn('users', 'role_id')) {
            $users = DB::table('users')->whereNotNull('role_id')->get();
            foreach ($users as $user) {
                DB::table('role_user')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'role_id' => $user->role_id,
                    ],
                    [
                        'is_primary' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
