<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Snapshot;
use App\Status;
use App\Opf;
use App\Type;
use App\City;
use App\Organization;
use App\Worker;
use Collective\Html\FormFacade;
use Illuminate\Http\Request;

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
          ->with('status', 'city', 'opf', 'type', 'chief', 'organizations', 'organizations.city',
            'organizations.type', 'organizations.chief', 'organizations.status', 'organizations.opf')
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
            'cities'   => City::lists('name', 'id'),
            'type'     => 1, // Юр лицо

            'phone' => $request->old('phone', []),
            'email' => $request->old('email', []),
        ]);
    }

    /**
     * Страница добавления подразделения
     * @param Request $request
     * @param Organization $organization
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addDepartmentPage(Request $request, Organization $organization)
    {
        return view('admin.organization.create', [
          'statuses' => Status::lists('name', 'id'),
          'opfs'     => Opf::lists('name', 'id'),
          'cities'   => City::lists('name', 'id'),
          'type'     => 2, // Подразделение
          'parent'   => $organization->id,

          'organization' => $organization,
          'phone' => $request->old('phone', []),
          'email' => $request->old('email', []),
          'menu' => 'add_department'
        ]);
    }

    /**
     * Страница редактирования организации
     * @param Organization $organization
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editPage(Organization $organization)
    {
        $organization->load('chief');
        FormFacade::model($organization);

        return view('admin.organization.create', [
          'statuses' => Status::lists('name', 'id'),
          'opfs'     => Opf::lists('name', 'id'),
          'cities'   => City::lists('name', 'id'),
          'type'     => $organization->type_id,

          'organization' => $organization,
          'phone' => $organization->phone,
          'email' => $organization->email,
          'menu' => 'edit'
        ]);
    }

    /**
     * Создание новой организации
     * @param Request $request
     * @param Organization $organization
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request, Organization $organization = null)
    {
        $rules = [
          'status'    => 'required|numeric',
          'city'      => 'numeric',
          'opf'       => 'required|numeric',
          'type'      => 'required|numeric',
          'fullName'  => 'required|string|max:512',
          'shortName' => 'string|max:255',
          'postCode'  => 'required|numeric',
          'address'   => 'required',
          'email.*'   => 'email'
        ];

        // для подразделений edrpou необязательное поле
        if(!empty($request->parent) || (!is_null($organization) && !empty($organization->parent_id)))
            $rules['edrpou'] = 'numeric';
        else $rules['edrpou'] = 'required|numeric';

        $this->validate($request, $rules);

        $organization->fill($request->all());
        $organization->opf()->associate($request->opf);
        $organization->type()->associate($request->type);
        $organization->city()->associate($request->city);
        $organization->status()->associate($request->status);

        // если передали родительскую организацию, сохраняем связь
        if(!empty($request->parent))
            $organization->parent_id = $request->parent;

        // редактирование существующего начальника
        if(isset($request->chief['id']) && !is_null($chief = Worker::find($request->chief['id']))) {
            $chief->fio = $request->chief['fio'];
            $organization->chief()->associate($chief);
            $chief->save();
        }
        // создание нового сотрудника "руководитель"
        elseif (!empty($request->chief['fio'])) {
            $chief = new Worker;
            $chief->fio = $request->chief['fio'];
            $chief->position = 'Керівник';
            $chief->save();
            $organization->chief()->associate($chief);
        }
        $organization->save();

        $request->session()->flash('save', 'done');
        return redirect()->route('admin::edit_organization', $organization->id);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $organizations = Organization::filter($request->all(), true)
          ->limit(10)
          ->get();
        return response()->json($organizations);
    }
}