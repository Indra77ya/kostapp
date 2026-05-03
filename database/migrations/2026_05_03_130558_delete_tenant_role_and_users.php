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
        // Delete all users with tenant role
        User::role('tenant')->delete();

        // Delete the tenant role
        Role::where('name', 'tenant')->delete();
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
