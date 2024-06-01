<?php

namespace TechStudio\Blog\database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This table stores blog articles.
 *
 * - "content": This field contains a JSON array of *blocks* that are supported by a page builder.
 */

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rejected_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->unsignedBigInteger('reporter');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rejected_articles');
    }
};
