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
        Schema::create('blog_articles', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('slug');
            $table->string('author_type');
            $table->integer('author_id');
            $table->foreignId('category_id')->nullable();
            $table->enum('status', ['published', 'draft', 'hidden', 'deleted'])->default('published');
            $table->integer('viewsCount')->default(0);
            $table->json('content');
            $table->text('bannerUrl')->nullable();
            $table->text('bannerUrlMobile')->nullable();
            $table->text('summary')->nullable();
            $table->text('seoDescription')->nullable();
            $table->string('seoTitle')->nullable();
            $table->string('seoKeyword')->nullable();
            $table->string('publicationDate')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_articles');
    }
};
