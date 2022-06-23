<?php

namespace Anditsung\NovaResourceComment\Traits;


use Anditsung\NovaResourceComment\Models\Comment;

trait Commentable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
