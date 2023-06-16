<?php

namespace Newnet\Tag\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Newnet\Tag\Models\Tag;

class TagController extends Controller
{
    public function detail($slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        return view('tag::web.tag.detail', compact('tag'));
    }
}
