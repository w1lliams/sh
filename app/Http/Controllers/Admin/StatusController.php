<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
  /**
   * Главная страница админки статусов организаций
   *
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
   */
  public function main()
  {
    return view('admin.status.main', [
      'statuses' => Status::all()
    ]);
  }

  public function create(Request $request)
  {
    $this->validate($request, [
      'name' => 'required|max:255'
    ]);

    Status::create($request->all());
    return redirect()->route('admin::status');
  }

  /**
   * Удаление статуса
   *
   * @param Status $status
   * @return \Illuminate\Http\RedirectResponse
   * @throws \Exception
   */
  public function delete(Status $status)
  {
    $status->delete();
    return redirect()->route('admin::status');
  }
}