<?php

namespace TechStudio\Blog\database\Migrations;

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
        Schema::create('blog_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('link_url');
            $table->string('image_url');
            $table->enum('variant',['1/4','1/1'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_banners');
    }
};
