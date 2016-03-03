<?php

namespace App\Http\Controllers\Admin;

use App\Fio;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class WorkerController extends Controller
{
  /**
   * Проверяем ФИО работников на валидность
   * @return \Illuminate\Http\JsonResponse
   */
  public function checkNewWorkers()
  {
    $result = [];
    $workers = json_decode(Input::get('workers'), true);

    foreach(array_chunk($workers, 200) as $chunk) {
      Fio::checkFIO($chunk, $result);
    }

    return response()->json($result);
  }
}