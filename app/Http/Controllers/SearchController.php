<?php

namespace App\Http\Controllers;

use App\Services\ViewDataService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(protected ViewDataService $data) {}

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $results = $q === ''
            ? ['tours' => [], 'destinations' => [], 'articles' => [], 'cruises' => []]
            : $this->data->search($q);

        return view('pages.search', [
            'q' => $q,
            'results' => $results,
            'total' => count($results['tours'])
                + count($results['destinations'])
                + count($results['articles'])
                + count($results['cruises']),
            'destinations' => $this->data->countries(),
            'keywords' => array_slice($this->data->popularKeywords(), 0, 8),
        ]);
    }
}
