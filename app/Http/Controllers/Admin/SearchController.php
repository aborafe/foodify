<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(private GlobalSearchService $searchService) {}

    public function index(Request $request): View
    {
        $query = $request->string('q')->toString();

        return view('admin.search', [
            'query' => $query,
            'groups' => $this->searchService->search($query, 20),
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        return response()->json([
            'groups' => $this->searchService->search($request->string('q')->toString(), 4),
            'all_url' => route('admin.search', ['q' => $request->string('q')->toString()]),
        ]);
    }
}
