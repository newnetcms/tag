<?php

if (!function_exists('get_another_tag')) {
    function get_another_tag($except) {
        return \Newnet\Tag\Models\Tag::whereNotIn('id', $except)->get();
    }
}
