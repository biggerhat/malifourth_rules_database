<?php

namespace App\Http\Controllers;

use App\Services\WyrdNews\WyrdNews;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        return Inertia::render('Index', [
            'wyrd_news' => WyrdNews::fetchLatest(),
        ]);
    }
}
