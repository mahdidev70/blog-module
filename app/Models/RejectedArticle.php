<?php

namespace TechStudio\Blog\app\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use TechStudio\Core\app\Models\UserProfile;

class RejectedArticle extends Model
{
    use HasFactory;

    protected $table = 'rejected_articles';

    protected $guarded = ['id'];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class, 'reporter_id', 'user_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
