<?php

namespace TechStudio\Blog\app\Models;

use Exception;
use App\Helper\HtmlContent;
use App\Helper\PageContent;
use Laravel\Scout\Searchable;
use App\Models\Traits\Likeable;
use App\Models\Traits\taggeable;
use App\Models\Traits\Bookmarkable;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    // use HasFactory, taggeable, Likeable, Bookmarkable;

    protected $table = 'blog_articles';

    protected $guarded = ['id'];

    // protected $casts = [
    //     'content' => 'json',
    // ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     if (!request()->is(['api/article_editor/*', 'api/panel/*'])) {
    //         static::addGlobalScope('publiclyVisible', function (Builder $builder) {
    //             $builder->where('status', 'published');
    //         });
    //     }
    // }

    // public function getRouteKeyName()
    // {
    //     return 'slug';
    // }

    // public function author()
    // {
    //     return $this->morphTo();
    // }

    // // TODO check (remove?)
    // public function user()
    // {
    //     return $this->belongsTo(UserProfile::class);
    // }
    // public function category()
    // {
    //     return $this->belongsTo(Category::class, 'category_id')
    //         ->where('table_type', get_class($this))->select(['id', 'slug', 'title']);
    // }

    // public function comments(): MorphMany
    // {
    //     return $this->morphMany(Comment::class, 'commentable')
    //         ->whereNull('parent_id')->where('status', 'approved');
    // }

    // public function getSummary()
    // {
    //     if (is_null($this->summary)) {
    //         $this->updateSummary();
    //     }
    //     return $this->summary;
    // }
    
    // public function updateSummary()
    // {
    //     foreach ($this->content as $item) {
    //         if ($item['type'] == 'html') {
    //             try{
    //                 $this->summary = HtmlContent::autoGenerateSummary($item['content']) ?? "";
    //             }catch(Exception $e){
    //                 Log::error($this->id.' error in content '.$e);
    //             }
    //             break;
    //         }
    //     }
    //     $this->save(); // cache
    // }

    // public function getAllImageUrls()
    // {
    //     return HtmlContent::getImageUrls($this->content);
    // }

    // public function minutesToRead()
    // {
    //     return (new PageContent($this->content))->getEstimatedTotalTime();
    // }

    // public static function restoreArticle($oldArticle)
    // {
    //     $article = new static();
    //     $article->exists = true;

    //     $article->id = $oldArticle->id;
    //     $article->slug = $oldArticle->slug;
    //     $article->title = $oldArticle->title;
    //     $article->publicationDate = $oldArticle->publicationDate;
    //     $article->viewsCount = $oldArticle->viewsCount;
    //     $article->content = $oldArticle->content;
    //     $article->bannerUrl = $oldArticle->bannerUrl;
    //     $article->summary = $oldArticle->summary;

    //     return $article;
    // }
}
