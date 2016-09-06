<?php

namespace App\Http\Controllers\Site;

use \App\Search;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
  /**
   * Быстрый поиск
   * @param  Search $search
   * @param  Request $request
   * @return \Illuminate\Http\JsonResponse
   */

  public function searchApi(Search $search, Request $request)
  {
    $workers = $search->searchWorkers($request->q, 10);

    // Смотрим, есть ли для этого работника файлы недвижимости, авто или предпринимательства. Плюсуем их к notes_count
    $resultworkers = $workers->toArray(); for ($i=0;$i<count($resultworkers);$i++) { $resultworkers[$i]['notes_count'] += get_additional_notes_count($resultworkers[$i]['fio']); }

    return response()->json([
      'organizations' => $search->searchOrganizations($request->q, 10),
      'workers'       => $resultworkers
    ]);
  }
}
