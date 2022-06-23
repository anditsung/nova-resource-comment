<?php

namespace Anditsung\NovaResourceComment\Nova;


use App\Nova\Resource;
use App\Nova\System\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use Anditsung\NovaResourceComment\Models\Comment as CommentModel;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class Comment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = CommentModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'comment',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            $this->when(! $request->viaResource, function () {
                return MorphTo::make(__('Commentable'), 'commentable')
                    ->types(CommentModel::allNovaResources());
            }),

            Textarea::make(__('Comment'), 'comment')
                ->alwaysShow()
                ->hideFromIndex(),

            Text::make(__('Comment'), 'comment')
                ->displayUsing(function ($comment) {
                    return Str::limit($comment, config('nova-comment.index-limit-length'));
                })
                ->onlyOnIndex(),

            BelongsTo::make(__('Commenter'), 'commenter', config('nova-comment.commenter'))
                ->exceptOnForms(),

            DateTime::make(__('Created'), 'created_at')
                ->exceptOnForms()
                ->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     *
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return bool
     */
    public static function availableForNavigation(Request $request)
    {
        return config('nova-comment.available-for-navigation');
    }
}
