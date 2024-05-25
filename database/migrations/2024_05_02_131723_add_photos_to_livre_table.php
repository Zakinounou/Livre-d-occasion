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
        Schema::table('livres', function (Blueprint $table) {
            $table->string('photo1')->nullable(); // Add photo1 column
            $table->string('photo2')->nullable(); // Add photo2 column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livres', function (Blueprint $table) {
            $table->dropColumn('photo1'); // Remove photo1 column
            $table->dropColumn('photo2'); // Remove photo2 column
        });
    }
};
