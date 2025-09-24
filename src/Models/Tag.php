<?php

namespace Newnet\Tag\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Newnet\Seo\Traits\SeoableTrait;
use Newnet\Core\Support\Traits\TranslatableTrait;
use Newnet\Tag\Traits\SlugOptions;
use Newnet\Tag\Traits\TaggableTrait;

/**
 * Newnet\Tag\Models\Tag
 *
 * @property int $id
 * @property string $slug
 * @property array $name
 * @property array|null $description
 * @property int $sort_order
 * @property string|null $group
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property mixed $url
 * @property \Newnet\Seo\Models\Meta|null $seometa
 * @property \Newnet\Seo\Models\Url|null $seourl
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Newnet\Seo\Models\Url> $seourls
 * @property-read int|null $seourls_count
 * @method static Builder|Tag newModelQuery()
 * @method static Builder|Tag newQuery()
 * @method static Builder|Tag onlyTrashed()
 * @method static Builder|Tag query()
 * @method static Builder|Tag whereCreatedAt($value)
 * @method static Builder|Tag whereDeletedAt($value)
 * @method static Builder|Tag whereDescription($value)
 * @method static Builder|Tag whereGroup($value)
 * @method static Builder|Tag whereId($value)
 * @method static Builder|Tag whereName($value)
 * @method static Builder|Tag whereSlug($value)
 * @method static Builder|Tag whereSortOrder($value)
 * @method static Builder|Tag whereUpdatedAt($value)
 * @method static Builder|Tag withTrashed()
 * @method static Builder|Tag withoutTrashed()
 * @mixin \Eloquent
 */
class Tag extends Model
{
    use SeoableTrait;
    use TranslatableTrait;
    use SoftDeletes;

    protected $table = 'tags';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'sort_order',
        'group',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'slug' => 'string',
        'sort_order' => 'integer',
        'group' => 'string',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = [
        'name',
        'description',
    ];

    /**
     * Get all attached models of the given class to the tag.
     *
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function entries(string $class): MorphToMany
    {
        return $this->morphedByMany($class, 'taggable', 'taggables', 'tag_id', 'taggable_id');
    }

    /**
     * Enforce clean slugs.
     *
     * @param string $value
     *
     * @return void
     */
    public function setSlugAttribute($value): void
    {
        $slug = get_safe_slug($value);

        $this->attributes['slug'] = $slug;
    }

    public function getSlugOptions()
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Enforce clean groups.
     *
     * @param string $value
     *
     * @return void
     */
    public function setGroupAttribute($value): void
    {
        $this->attributes['group'] = Str::slug($value);
    }

    /**
     * Get first tag(s) by name or create if not exists.
     *
     * @param mixed       $tags
     * @param string|null $group
     * @param string|null $locale
     *
     * @return \Illuminate\Support\Collection
     */
    public static function findByNameOrCreate($tags, string $group = null, string $locale = null): Collection
    {
        $locale = $locale ?? app()->getLocale();

        return collect(TaggableTrait::parseDelimitedTags($tags))->map(function (string $tag) use ($group, $locale) {
            return static::firstByName($tag, $group, $locale) ?: static::createByName($tag, $group, $locale);
        });
    }

    /**
     * Find tag by name.
     *
     * @param mixed       $tags
     * @param string|null $group
     * @param string|null $locale
     *
     * @return \Illuminate\Support\Collection
     */
    public static function findByName($tags, string $group = null, string $locale = null): Collection
    {
        $locale = $locale ?? app()->getLocale();

        return collect(TaggableTrait::parseDelimitedTags($tags))->map(function (string $tag) use ($group, $locale) {
            return ($exists = static::firstByName($tag, $group, $locale)) ? $exists->getKey() : null;
        })->filter()->unique();
    }

    /**
     * Get first tag by name.
     *
     * @param string      $tag
     * @param string|null $group
     * @param string|null $locale
     *
     * @return static|null
     */
    public static function firstByName(string $tag, string $group = null, string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return static::query()->where("name->{$locale}", $tag)->when($group, function (Builder $builder) use ($group) {
            return $builder->where('group', $group);
        })->first();
    }

    /**
     * Create tag by name.
     *
     * @param string      $tag
     * @param string|null $locale
     * @param string|null $group
     *
     * @return static
     */
    public static function createByName(string $tag, string $group = null, string $locale = null): self
    {
        $locale = $locale ?? app()->getLocale();

        return static::create([
            'name' => [$locale => $tag],
            'group' => $group,
            'slug' => $tag,
        ]);
    }

    public function getUrl()
    {
        return route('tag.web.tag.detail', $this->slug);
    }
}
