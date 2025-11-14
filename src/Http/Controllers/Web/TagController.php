<?php

namespace Newnet\Tag\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Newnet\Tag\Models\Tag;

class TagController extends Controller
{
    public function index($any)
    {
        $id = intval(substr($any, strrpos($any, '-') + 1));
        $slug = substr($any, 0, strrpos($any, '-'));

        if ($id <= 0) {
            abort(404);
        }

        return $this->detail($slug, $id);
    }

    public function detail($slug, $id)
    {
        $tag = Tag::findorFail($id);

        return view('tag::web.tag.detail', compact('tag'));
    }
}
