<?php

use Newnet\Cms\CmsAdminMenuKey;
use Newnet\Tag\TagAdminMenuKey;

AdminMenu::addItem(__('tag::menu.tag.index'), [
    'id' => TagAdminMenuKey::TAG,
    'parent' => CmsAdminMenuKey::CONTENT,
    'route' => 'tag.admin.tag.index',
    'icon' => 'fas fa-tags',
    'order' => 100,
]);
