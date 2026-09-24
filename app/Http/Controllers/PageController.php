<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(Page $page): View
    {
        if (! $page->is_active) {
            abort(404);
        }

        return view('page', ['page' => $page]);
    }
}
