<?php

namespace Newnet\Tag\Traits;

use Newnet\Tag\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait TaggableTrait
{
    public static function bootTaggableTrait()
    {
        static::saved(function (self $model) {
            if (request()->has('tags')){
                $model->syncTags(request()->input('tags'));
            }
        });
    }

    public function getTagLinks($glue = ', ')
    {
        return $this->tags
            ->map(function ($tag) {
                return "<a href=\"{$tag->getUrl()}\">{$tag->name}</a>";
            })
            ->implode($glue);
    }

    /**
     * The tags delimiter.
     *
     * @var string
     */
    protected static $tagsDelimiter = ',';

    /**
     * Register a saved model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    abstract public static function saved($callback);

    /**
     * Register a deleted model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    abstract public static function deleted($callback);

    /**
     * Define a polymorphic many-to-many relationship.
     *
     * @param string $related
     * @param string $name
     * @param string $table
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey
     * @param string $relatedKey
     * @param bool   $inverse
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    abstract public function morphToMany(
        $related,
        $name,
        $table = null,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null,
        $inverse = false
    );

    /**
     * Get all attached tags to the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables', 'taggable_id', 'tag_id')
            ->orderBy('sort_order')
            ->withTimestamps();
    }

    /**
     * Get tags delimiter.
     *
     * @return string
     */
    public static function getTagsDelimiter()
    {
        return static::$tagsDelimiter;
    }

    /**
     * Set tags delimiter.
     *
     * @param string $delimiter
     *
     * @return void
     */
    public static function setTagsDelimiter(string $delimiter)
    {
        static::$tagsDelimiter = $delimiter;
    }

    /**
     * Boot the taggable trait for the model.
     *
     * @return void
     */
    public static function bootTaggable()
    {
        static::deleted(function (self $model) {
            $model->tags()->detach();
        });
    }

    /**
     * Attach the given tag(s) to the model.
     *
     * @param mixed $tags
     *
     * @return void
     */
    public function setTagsAttribute($tags): void
    {
        static::saved(function (self $model) use ($tags) {
            $model->syncTags($tags);
        });
    }

    /**
     * Scope query with all the given tags.
     *
     * @param Builder $builder
     * @param mixed $tags
     * @param string|null $group
     * @param string|null $locale
     *
     * @return Builder
     */
    public function scopeWithAllTags(Builder $builder, $tags, string $group = null, string $locale = null): Builder
    {
        $tags = $this->parseTags($tags, $group, $locale);

        collect($tags)->each(function ($tag) use ($builder, $group) {
            $builder->whereHas('tags', function (Builder $builder) use ($tag, $group) {
                return $builder->where('id', $tag)->when($group, function (Builder $builder) use ($group) {
                    return $builder->where('group', $group);
                });
            });
        });

        return $builder;
    }

    /**
     * Scope query with any of the given tags.
     *
     * @param Builder $builder
     * @param mixed $tags
     * @param string|null $group
     * @param string|null $locale
     *
     * @return Builder
     */
    public function scopeWithAnyTags(Builder $builder, $tags, string $group = null, string $locale = null): Builder
    {
        $tags = $this->parseTags($tags, $group, $locale);

        return $builder->whereHas('tags', function (Builder $builder) use ($tags, $group) {
            $builder->whereIn('id', $tags)->when($group, function (Builder $builder) use ($group) {
                return $builder->where('group', $group);
            });
        });
    }

    /**
     * Scope query without any of the given tags.
     *
     * @param Builder $builder
     * @param mixed $tags
     * @param string|null $group
     * @param string|null $locale
     *
     * @return Builder
     */
    public function scopeWithoutTags(Builder $builder, $tags, string $group = null, string $locale = null): Builder
    {
        $tags = $this->parseTags($tags, $group, $locale);

        return $builder->whereDoesntHave('tags', function (Builder $builder) use ($tags, $group) {
            $builder->whereIn('id', $tags)->when($group, function (Builder $builder) use ($group) {
                return $builder->where('group', $group);
            });
        });
    }

    /**
     * Scope query without any tags.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeWithoutAnyTags(Builder $builder): Builder
    {
        return $builder->doesntHave('tags');
    }

    /**
     * Determine if the model has any of the given tags.
     *
     * @param mixed $tags
     * @param string|null $group
     * @param string|null $locale
     *
     * @return bool
     */
    public function hasAnyTags($tags, string $group = null, string $locale = null): bool
    {
        $tags = $this->parseTags($tags, $group, $locale);

        return ! $this->tags->pluck('id')->intersect($tags)->isEmpty();
    }

    /**
     * Determine if the model has all of the given tags.
     *
     * @param mixed  $tags
     * @param string $group
     * @param string $locale
     *
     * @return bool
     */
    public function hasAllTags($tags, string $group = null, string $locale = null): bool
    {
        $tags = $this->parseTags($tags, $group, $locale);

        return collect($tags)->diff($this->tags->pluck('id'))->isEmpty();
    }

    /**
     * Parse delimited tags.
     *
     * @param mixed $tags
     *
     * @return array
     */
    public static function parseDelimitedTags($tags): array
    {
        if (is_string($tags) && mb_strpos($tags, static::$tagsDelimiter) !== false) {
            $delimiter = preg_quote(static::$tagsDelimiter, '#');
            $tags = array_map('trim', preg_split("#[{$delimiter}]#", $tags, -1, PREG_SPLIT_NO_EMPTY));
        }

        return array_unique(array_filter((array) $tags));
    }

    /**
     * Attach model tags.
     *
     * @param mixed $tags
     *
     * @return $this
     */
    public function attachTags($tags)
    {
        // Use 'sync' not 'attach' to avoid Integrity constraint violation
        $this->tags()->sync($this->parseTags($tags), false);

        return $this;
    }

    /**
     * Sync model tags.
     *
     * @param mixed $tags
     * @param bool  $detaching
     *
     * @return $this
     */
    public function syncTags($tags, bool $detaching = true)
    {
        $this->tags()->sync($this->parseTags($tags, null, null, true), $detaching);

        return $this;
    }

    /**
     * Detach model tags.
     *
     * @param mixed $tags
     *
     * @return $this
     */
    public function detachTags($tags = null)
    {
        ! $tags || $tags = $this->parseTags($tags);

        $this->tags()->detach($tags);

        return $this;
    }

    /**
     * Parse tags.
     *
     * @param mixed $rawTags
     * @param string|null $group
     * @param string|null $locale
     * @param bool $create
     *
     * @return array
     */
    protected function parseTags($rawTags, string $group = null, string $locale = null, $create = false): array
    {
        (is_iterable($rawTags) || is_null($rawTags)) || $rawTags = [$rawTags];

        [$strings, $tags] = collect($rawTags)->map(function ($tag) {
            ! is_numeric($tag) || $tag = (int) $tag;

            ! $tag instanceof Model || $tag = [$tag->getKey()];
            ! $tag instanceof Collection || $tag = $tag->modelKeys();
            ! $tag instanceof BaseCollection || $tag = $tag->toArray();

            return $tag;
        })->partition(function ($item) {
            return is_string($item);
        });

        return $tags->merge(Tag::{$create ? 'findByNameOrCreate' : 'findByName'}($strings->toArray(), $group, $locale)->pluck('id'))->toArray();
    }
}
