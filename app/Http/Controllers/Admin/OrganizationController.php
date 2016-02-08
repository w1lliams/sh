<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Status;
use App\Opf;
use App\Type;
use App\City;
use App\Organization;
use Collective\Html\FormFacade;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    /**
     * Список организаций
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function fetch()
    {
        $organizations = Organization::with('status', 'city', 'opf')->where('parent_id', 0)->paginate(15);
        return view('admin.organization.list', [
            'organizations' => $organizations
        ]);
    }

    /**
     * Страница создания организации
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createPage(Request $request)
    {
        return view('admin.organization.create', [
            'statuses' => Status::lists('name', 'id'),
            'opfs'     => Opf::lists('name', 'id'),
            'types'    => Type::lists('name', 'id'),
            'cities'   => City::lists('name', 'id'),

            'phone' => $request->old('phone', []),
            'email' => $request->old('email', []),
        ]);
    }

    /**
     * Страница редактирования организации
     * @param Organization $organization
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editPage(Organization $organization)
    {
        FormFacade::model($organization);
        return view('admin.organization.create', [
          'statuses' => Status::lists('name', 'id'),
          'opfs'     => Opf::lists('name', 'id'),
          'types'    => Type::lists('name', 'id'),
          'cities'   => City::lists('name', 'id'),

          'organization' => $organization,
          'phone' => $organization->phone,
          'email' => $organization->email,
        ]);
    }

    /**
     * Создание новой организации
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        $this->_validateOrganization($request);

        $oraganization = new Organization($request->all());
        $oraganization->opf()->associate($request->opf);
        $oraganization->type()->associate($request->type);
        $oraganization->city()->associate($request->city);
        $oraganization->status()->associate($request->status);
        $oraganization->save();

        return redirect()->route('admin::organization');
    }

    /**
     * @param Request $request
     */
    private function _validateOrganization(Request $request)
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
    }
}