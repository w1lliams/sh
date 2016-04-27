<?php

namespace App\Http\Controllers\Site;

use \App\Search;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
  /**
   * Быстрый поиск
   * @param  Search  $search
   * @param  Request $request
   */
  public function searchApi(Search $search, Request $request)
  {
    return response()->json([
      'organizations' => $search->searchOrganizations($request->q, 5),
      'workers'       => $search->searchWorkers($request->q, 5)
    ]);
  }
}
