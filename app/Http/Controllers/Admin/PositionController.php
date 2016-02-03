<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
  /**
   * Главная страница админки должностей
   *
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
   */
  public function main()
  {
    return view('admin.position.main', [
      'positions' => Position::all()
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

    Position::create($request->all());
    return redirect()->route('admin::position');
  }

  /**
   * Удаление должности
   *
   * @param Position $position
   * @return \Illuminate\Http\RedirectResponse
   * @throws \Exception
   */
  public function delete(Position $position)
  {
    $position->delete();
    return redirect()->route('admin::position');
  }
}