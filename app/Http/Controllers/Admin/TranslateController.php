<?php

namespace App\Http\Controllers\Admin;

use App\Fio;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TranslateController extends Controller
{
  /**
   * Страница переводов фамилий с русс. на укр.
   * @return mixed
   */
  public function fioPage()
  {
    return view('admin.translate.fio');
  }

  /**
   * Переводим ФИО с русс. на укр.
   * @param Request $request
   * @return mixed
   */
  public function fio(Request $request)
  {
    $firstName  = $request->get('firstName');
    $lastName   = $request->get('lastName');
    $middleName = $request->get('middleName');
    
    return response()->json(Fio::translate($firstName, $lastName, $middleName));
  }
}