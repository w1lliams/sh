<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Opf;
use Illuminate\Http\Request;

class OpfController extends Controller
{
    /**
     * Главная страница админки ОПФ
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function main()
    {
        return view('admin.opf.main', [
          'opfs' => Opf::all()
        ]);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
          'name' => 'required|max:255'
        ]);

        Opf::create($request->all());
        return redirect()->route('admin::opf');
    }

    /**
     * Удаление ОПФ
     *
     * @param Opf $opf
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function delete(Opf $opf)
    {
        $opf->delete();
        return redirect()->route('admin::opf');
    }
}