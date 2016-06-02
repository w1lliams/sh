<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\City;
use App\Organization;
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
    if(Organization::where('city_id', $city->id)->exists()) {
      dd('Есть организации с таким городом');
    }

    $city->delete();
    return redirect()->route('admin::city');
  }

  /**
   * @param Request $request
   * @return mixed
   */
  public function search(Request $request)
  {
    $cities = City::where('name', 'like', "%{$request->name}%")->limit(10)->get();
    return response()->json($cities);
  }
}