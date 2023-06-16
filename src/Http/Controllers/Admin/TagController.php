<?php
namespace Newnet\Tag\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Newnet\AdminUi\Facades\AdminMenu;
use Newnet\Tag\Http\Requests\TagRequest;
use Newnet\Tag\Models\Tag;
use Newnet\Tag\Repositories\Eloquent\TagRepository;
use Newnet\Tag\Repositories\TagRepositoryInterface;
use Newnet\Tag\TagAdminMenuKey;

class TagController extends Controller
{
    /**
     * @var TagRepositoryInterface|TagRepository
     */
    private $tagRepository;

    public function __construct(TagRepositoryInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function index(Request $request)
    {
        $items = $this->tagRepository->paginate($request->input('max', 20));

        return view('tag::admin.tag.index', compact('items'));
    }

    public function create()
    {
        AdminMenu::activeMenu(TagAdminMenuKey::TAG);

        return view('tag::admin.tag.create');
    }

    public function store(TagRequest $request)
    {
        /** @var Tag $tag */
        $tag = $this->tagRepository->create($request->all());

        return redirect()
            ->route('tag.admin.tag.edit', $tag->id)
            ->with('success', __('tag::tag.notification.created'));
    }

    public function edit($id)
    {
        AdminMenu::activeMenu(TagAdminMenuKey::TAG);

        $item = $this->tagRepository->find($id);

        return view('tag::admin.tag.edit', compact('item'));
    }

    public function update($id, TagRequest $request)
    {
        $this->tagRepository->updateById($request->all(), $id);

        return back()->with('success', __('tag::tag.notification.created'));
    }

    public function destroy($id, Request $request)
    {
        $this->tagRepository->delete($id);

        if ($request->wantsJson()) {
            Session::flash('success', __('tag::tag.notification.deleted'));
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()
            ->route('tag.admin.tag.index')
            ->with('success', __('tag::tag.notification.deleted'));
    }

    public function search(Request $request)
    {
        $keyword = $request->input('q');
        $keywords = array_filter(explode(' ', trim($keyword)));

        $tags = Tag::query()->where(function($q) use ($keywords){
            foreach ($keywords as $keyword) {
                $q->where('name', 'like', "%{$keyword}%");
            }
        })->orWhere(function($q) use ($keywords){
            foreach ($keywords as $keyword) {
                $q->where('slug', 'like', "%{$keyword}%");
            }
        })->paginate();

        $items = [];
        foreach ($tags as $tag) {
            $items[] = [
                'id' => $tag->id,
                'text' => $tag->name,
            ];
        }

        return [
            'items' => $items,
            'hasMore' => $tags->hasMorePages(),
        ];
    }
}
