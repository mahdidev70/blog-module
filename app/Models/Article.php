<?php

namespace TechStudio\Blog\app\Models;

use TechStudio\Core\app\Models\Category;
use TechStudio\Core\app\Models\Comment;
use TechStudio\Core\app\Models\Traits\taggeable;
use TechStudio\Core\app\Helper\PageContent;
use TechStudio\Core\app\Helper\HtmlContent;
use TechStudio\Core\app\Models\Traits\Bookmarkable;
use TechStudio\Core\app\Models\Traits\Likeable;

use Exception;
use Laravel\Scout\Searchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use TechStudio\Core\app\Models\UserProfile;

class Article extends Model
{
    use HasFactory, Bookmarkable, Likeable, taggeable;

    protected $table = 'blog_articles';

    protected $guarded = ['id'];

    protected $casts = [
        'content' => 'json',
    ];

    protected static function boot()
    {
        parent::boot();

        if (!request()->is(['*/api/article_editor/*', '*/api/panel/*'])) {
            static::addGlobalScope('publiclyVisible', function (Builder $builder) {
                $builder->where('status', 'published');
            });
        }

        $request = request();
        $excludedRoutes = ['api/home/common'];

        if (!in_array($request->path(), $excludedRoutes)) {
            static::addGlobalScope('postType', function (Builder $builder) use ($request) {
                $builder->when($request->has('type'), function ($query) use ($request) {
                    $query->where('type', $request->get('type'));
                });
            });

        }
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function author()
    {
        return $this->belongsTo(UserProfile::class, 'author_id', 'user_id');
    }

    // // TODO check (remove?)
    public function user()
    {
        return $this->belongsTo(UserProfile::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')
            ->where('table_type', get_class($this))->select(['id', 'slug', 'title']);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->whereNull('parent_id')->where('status', 'approved');
    }

    public function getSummary()
    {
        if (is_null($this->summary)) {
            $this->updateSummary();
        }
        return $this->summary;
    }

    public function updateSummary()
    {
        foreach ($this->content as $item) {
            if ($item['type'] == 'html') {
                try{
                    $content = is_array($item['content']) ? $item['content']['model'] ?? [] : $item['content'];
                    $this->summary = HtmlContent::autoGenerateSummary($content) ?? "";
                }catch(Exception $e){
                    Log::error($this->id.' error in content '.$e);
                }
                break;
            }
        }
        $this->save(); // cache
    }

    public function getAllImageUrls()
    {
        return HtmlContent::getImageUrls($this->content);
    }

    public function minutesToRead()
    {
        return (new PageContent($this->content))->getEstimatedTotalTime();
    }

    public static function restoreArticle($oldArticle)
    {
        $article = new static();
        $article->exists = true;

        $article->id = $oldArticle->id;
        $article->slug = $oldArticle->slug;
        $article->title = $oldArticle->title;
        $article->publicationDate = $oldArticle->publicationDate;
        $article->viewsCount = $oldArticle->viewsCount;
        $article->content = $oldArticle->content;
        $article->bannerUrl = $oldArticle->bannerUrl;
        $article->summary = $oldArticle->summary;

        return $article;
    }
}
