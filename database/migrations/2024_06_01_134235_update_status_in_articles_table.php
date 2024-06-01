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
        Schema::table('blog_articles', function (Blueprint $table) {
            $table->drop('status');
        });

        Schema::table('blog_articles', function (Blueprint $table) {
            $table->enum('status', ['published', 'draft', 'ready_to_publish', 'hidden', 'deleted'])->default('published')->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_articles');
    }
};
