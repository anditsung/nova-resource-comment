<?php

namespace Anditsung\NovaResourceComment\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Nova;

class Comment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nova_comments';

    /**
     * The "booting" method of the model.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(
            function ($comment) {
                if (auth()->check()) {
                    $comment->commenter_id = auth()->id();
                }
            }
        );
    }

    public static function allNovaResources()
    {
        return Nova::$resources;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function commenter()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'commenter_id');
    }
}
