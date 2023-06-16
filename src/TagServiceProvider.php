<?php

namespace Newnet\Tag;

use Illuminate\Support\Facades\Blade;
use Newnet\Module\Support\BaseModuleServiceProvider;
use Newnet\Tag\Models\Tag;
use Newnet\Tag\Repositories\Eloquent\TagRepository;
use Newnet\Tag\Repositories\TagRepositoryInterface;

class TagServiceProvider extends BaseModuleServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->singleton(TagRepositoryInterface::class, function () {
            return new TagRepository(new Tag());
        });
    }

    public function boot()
    {
        parent::boot();

        Blade::include('tag::form.tags', 'tags');
    }
}
