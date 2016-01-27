<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
  /**
   * Главная страница админки городов
   *
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
   */
  public function main()
  {
    return view('admin.city.main', [
      'cities' => City::all()
    ]);
  }

  /**
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function create(Request $request)
  {
    $this->validate($request, [
      'name' => 'required|max:255'
    ]);

    City::create($request->all());
    return redirect()->route('admin::city');
  }

  /**
   * Удаление города
   *
   * @param City $city
   * @return \Illuminate\Http\RedirectResponse
   * @throws \Exception
   */
  public function delete(City $city)
  {
    $city->delete();
    return redirect()->route('admin::city');
  }
}