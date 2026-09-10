<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan index eksplisit pada kolom owner_id di tabel projects.
     *
     * Mempercepat query: SELECT * FROM projects WHERE owner_id = ?
     * yang sering dijalankan saat user membuka dashboard project-nya.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->index('owner_id', 'projects_owner_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_owner_id_index');
        });
    }
};
