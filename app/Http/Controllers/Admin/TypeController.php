<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Organization;
use App\Type;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    /**
     * Главная страница админки Типов оргонизаций
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function main()
    {
        return view('admin.type.main', [
          'types' => Type::all()
        ]);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
          'name' => 'required|max:255'
        ]);

        Type::create($request->all());
        return redirect()->route('admin::type');
    }

    /**
     * Удаление типа
     *
     * @param Type $type
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function delete(Type $type)
    {
        if(Organization::where('type_id', $type->id)->exists()) {
            dd('Есть организации с таким типом');
        }

        $type->delete();
        return redirect()->route('admin::type');
    }
}
