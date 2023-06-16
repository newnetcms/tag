<?php

return [
    'model_name' => 'Tag',

    'name'        => 'Tag',
    'description' => 'Description',
    'created_at'  => 'Created At',

    'index' => [
        'page_title'    => 'Tags',
        'page_subtitle' => 'Tags',
        'breadcrumb'    => 'Tags',
    ],

    'create' => [
        'page_title'    => 'Add Tag',
        'page_subtitle' => 'Add Tag',
        'breadcrumb'    => 'Add',
    ],

    'edit' => [
        'page_title'    => 'Edit Tag',
        'page_subtitle' => 'Edit Tag',
        'breadcrumb'    => 'Edit',
    ],

    'notification' => [
        'created' => 'Tag successfully created!',
        'updated' => 'Tag successfully updated!',
        'deleted' => 'Tag successfully deleted!',
    ],

    'tabs' => [
        'info' => 'Information',
        'seo'  => 'SEO',
    ],
];
