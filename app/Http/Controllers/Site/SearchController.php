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
    $resultworkers = $workers->toArray();
    include_once resource_path().'/classes/base32.php';
    for ($i=0;$i<count($resultworkers);$i++)
    {
				$WorkerRealty = array(); $WorkerCars = array(); $WorkerBusiness = array();
				$Base32fio = $base32->encode($resultworkers[$i]['fio']);
				$WorkerDataDirectory = public_path().'/data/'.$Base32fio; 
				if(is_dir($WorkerDataDirectory)) {
					$WorkerDataFiles = scandir($WorkerDataDirectory);
					foreach ($WorkerDataFiles as $file) {if (stripos($file, 'neruh_') === 0) array_push($WorkerRealty, $file); elseif (stripos($file, 'avto_') === 0) array_push($WorkerCars, $file); elseif (stripos($file, 'pidpr_') === 0) array_push($WorkerBusiness, $file);}
					if (count($WorkerRealty)>0)	$resultworkers[$i]['notes_count']++;
					if (count($WorkerCars)>0)	$resultworkers[$i]['notes_count']++;
					if (count($WorkerBusiness)>0)	$resultworkers[$i]['notes_count']++;
					}

    }



    return response()->json([
      'organizations' => $search->searchOrganizations($request->q, 10),
      'workers'       => $resultworkers
    ]);
  }
}
