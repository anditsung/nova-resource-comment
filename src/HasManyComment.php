<?php

namespace Anditsung\NovaResourceComment;

use Anditsung\NovaResourceComment\Nova\Comment;
use Illuminate\Http\Request;
use Laravel\Nova\Contracts\ListableField;
use Laravel\Nova\Contracts\RelatableField;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Panel;

class HasManyComment extends Field implements ListableField, RelatableField
{

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'has-many-comment-field';

    /**
     * The class name of the related resource.
     *
     * @var string
     */
    public $resourceClass;

    /**
     * The URI key of the related resource.
     *
     * @var string
     */
    public $resourceName;

    /**
     * The name of the Eloquent "has many" relationship.
     *
     * @var string
     */
    public $hasManyRelationship;

    /**
     * The displayable singular label of the relation.
     *
     * @var string
     */
    public $singularLabel;

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  string|null  $resource
     * @return void
     */
    public function __construct($name = "Comments", $attribute = 'comments', $resource = Comment::class)
    {
        parent::__construct($name, $attribute);

        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->hasManyRelationship = $this->attribute = $attribute ?? ResourceRelationshipGuesser::guessRelation($name);
    }

    /**
     * Get the relationship name.
     *
     * @return string
     */
    public function relationshipName()
    {
        return $this->hasManyRelationship;
    }

    /**
     * Get the relationship type.
     *
     * @return string
     */
    public function relationshipType()
    {
        return 'hasMany';
    }

    /*
     * set max comment that will be show
     *
     */
    public function maxComment($count = 5)
    {
        $this->resourceClass::$perPageViaRelationship = $count;

        return $this;
    }

    /**
     * Determine if the field should be displayed for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        return call_user_func(
                [$this->resourceClass, 'authorizedToViewAny'], $request
            ) && parent::authorize($request);
    }

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {
        //
    }

    /**
     * Set the displayable singular label of the resource.
     *
     * @return $this
     */
    public function singularLabel($singularLabel)
    {
        $this->singularLabel = $singularLabel;

        return $this;
    }

    /**
     * Make current field behaves as panel.
     *
     * @return \Laravel\Nova\Panel
     */
    public function asPanel()
    {
        return Panel::make($this->name)
            ->withMeta([
                'fields' => [$this],
                'prefixComponent' => true,
            ])->withComponent('relationship-panel');
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge([
            'hasManyRelationship' => $this->hasManyRelationship,
            'relatable' => true,
            'perPage'=> $this->resourceClass::$perPageViaRelationship,
            'resourceName' => $this->resourceName,
            'singularLabel' => $this->singularLabel ?? $this->resourceClass::singularLabel(),
        ], parent::jsonSerialize());
    }
}
