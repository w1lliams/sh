<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Status;
use App\Opf;
use App\Type;
use App\City;
use App\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class OrganizationController extends Controller
{
    /**
     * Список организаций
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function fetch()
    {
        $organizations = Organization::with('status', 'city')->where('parent_id', 0)->paginate(15);
        return view('admin.organization.list', [
            'organizations' => $organizations
        ]);
    }

    /**
     * Страница создания организации
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createPage()
    {
        return view('admin.organization.create', [
            'statuses' => Status::all(),
            'opfs'     => Opf::all(),
            'types'    => Type::all(),
            'cities'   => City::all()
        ]);
    }

    /**
     * Создание новой организации
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'status'    => 'required|numeric',
            'city'      => 'numeric',
            'edrpou'    => 'required|numeric',
            'opf'       => 'required|numeric',
            'type'      => 'required|numeric',
            'fullName'  => 'required|string|max:512',
            'shortName' => 'string|max:255',
            'postCode'  => 'required|numeric',
            'address'   => 'required',
            'email.*'   => 'email'
        ]);

        $oraganization = new Organization($request->all());
        $oraganization->opf()->associate($request->opf);
        $oraganization->type()->associate($request->type);
        $oraganization->city()->associate($request->city);
        $oraganization->status()->associate($request->status);
        $oraganization->save();

        return redirect()->route('admin::organization');
    }
}