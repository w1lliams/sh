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
use Illuminate\Support\Facades\Input;

class OrganizationController extends Controller
{
    /**
     * Список организаций
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function fetch(Request $request)
    {
        $organizations = Organization::filter($request->all())
          ->with('status', 'city', 'opf', 'type')
          ->orderBy('id', 'desc')
          ->get();

        return view('admin.organization.list', [
            'organizations' => $organizations,
            'statuses' => Status::lists('name', 'id'),
            'opfs'     => Opf::lists('name', 'id'),
            'types'    => Type::lists('name', 'id'),
            'cities'   => City::lists('name', 'id'),
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
     * Страница с сотрудниками
     * @param Organization $organization
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function workersPage(Organization $organization)
    {
        return view('admin.organization.workers');
    }

    /**
     * Создание новой организации
     * @param Request $request
     * @param Organization $organization
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request, Organization $organization = null)
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

        $organization->fill($request->all());
        $organization->opf()->associate($request->opf);
        $organization->type()->associate($request->type);
        $organization->city()->associate($request->city);
        $organization->status()->associate($request->status);
        $organization->save();

        return redirect()->route('admin::organization');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $organizations = Organization::filter($request->all())->limit(10)->get();
        return response()->json($organizations);
    }
}