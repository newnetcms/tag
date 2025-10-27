<?php

namespace Newnet\Tag\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Newnet\Tag\Models\Tag;

class TagController extends Controller
{
    public function detail($slug, $id)
    {
        $tag = Tag::findorFail($id);

        return view('tag::web.tag.detail', compact('tag'));
    }
}
