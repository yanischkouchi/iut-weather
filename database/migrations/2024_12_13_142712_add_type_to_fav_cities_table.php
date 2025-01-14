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
        Schema::table('fav_cities', function (Blueprint $table) {
            $table->string('type')->default('favorite'); // "favorite" for fav city, "list" for lists cities
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fav_cities', function (Blueprint $table) {
            //
        });
    }
};
