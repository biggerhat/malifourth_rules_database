<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageListResource;
use App\Models\Page;
use Illuminate\Http\Request;

class PageOrderController extends Controller
{
    public function index(Request $request)
    {
        return inertia('Admin/Pages/Order', [
            'pages' => PageListResource::collection(
                Page::published()->orderBy('page_number', 'ASC')->get()
            )->toArray($request),
        ]);
    }

    public function update(Request $request)
    {
        $collection = Collect($request->all())->keyBy('id');
        $ids = $collection->pluck('id');
        Page::whereIn('id', $ids->toArray())->get()->each(function (Page $page) use ($collection) {
            $page->page_number = $collection[$page->id]['page_number'];
            $page->save();
        });

        return to_route('admin.pages.index')->withMessage('Page Order Updated Successfully!');
    }
}
