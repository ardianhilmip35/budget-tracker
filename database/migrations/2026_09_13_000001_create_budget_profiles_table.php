<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budget_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedBigInteger('thp');
            $table->unsignedBigInteger('kos');
            $table->unsignedBigInteger('operasional');
            $table->unsignedBigInteger('allianz');
            $table->unsignedBigInteger('dana_darurat');
            $table->unsignedBigInteger('bmri');
            $table->unsignedBigInteger('emas');
            $table->unsignedBigInteger('uang_bebas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_profiles');
    }
};
