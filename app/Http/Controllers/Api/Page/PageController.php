<?php

namespace App\Http\Controllers\Api\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Pages
 *
 * Handles actions related to managing and retrieving pages within the application.
 */
class PageController extends Controller
{

    /**
     * All Page
     *
     * Retrieves all pages from the database and returns them as a collection of PageResource objects.
     *
     * @return AnonymousResourceCollection The collection of PageResource objects.
     */
    public function index()
    {
        $pages = Page::all();
        return PageResource::collection($pages);
    }

    /**
     * Detail Page
     *
     * Retrieves a specific page from the database based on its slug and returns it as a PageResource object.
     *
     * @param string $slug The slug of the page to retrieve.
     * @return PageResource The PageResource object representing the retrieved page.
     */
    public function view(string $slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        return new PageResource($page);
    }
}
