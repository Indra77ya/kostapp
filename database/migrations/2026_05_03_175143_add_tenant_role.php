<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::firstOrCreate(['name' => 'tenant']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We might not want to delete the role if it has users,
        // but for migration purposes:
        // Role::where('name', 'tenant')->delete();
    }
};
