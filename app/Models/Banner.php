<?php

namespace TechStudio\Blog\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory , SoftDeletes;

    protected $table = 'blog_banners';

    protected $fillable = ['title','link_url','image_url','variant'];

}
