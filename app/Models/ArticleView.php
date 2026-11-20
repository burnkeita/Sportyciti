<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleView extends Model
{
    public $timestamps = false;

    protected $fillable = ['article_id', 'date', 'views'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
