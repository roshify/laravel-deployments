<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('class');
            $table->string('checksum')->nullable();
            $table->unsignedInteger('batch');
            $table->timestamp('executed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
};