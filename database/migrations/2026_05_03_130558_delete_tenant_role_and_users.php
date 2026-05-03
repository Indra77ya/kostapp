<?php

use App\Models\User;
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
        // Check if role exists before deleting users
        if (Role::where('name', 'tenant')->exists()) {
            User::role('tenant')->delete();
            Role::where('name', 'tenant')->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Role::where('name', 'tenant')->exists()) {
            Role::create(['name' => 'tenant']);
        }
    }
};
