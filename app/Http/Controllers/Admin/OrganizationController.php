<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Snapshot;
use App\Status;
use App\Opf;
use App\Type;
use App\City;
use App\Organization;
use App\Inquiry;
use App\OrganizationInquiry;
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
          ->with('status', 'city', 'opf', 'type', 'organizations', 'organizations.city',
            'organizations.type', 'organizations.status', 'organizations.opf')
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
          'parent'   => $organization,

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
        FormFacade::model($organization);

        return view('admin.organization.create', [
          'statuses' => Status::lists('name', 'id'),
          'opfs'     => Opf::lists('name', 'id'),
          'cities'   => City::lists('name', 'id'),
          'type'     => $organization->type_id,
          'parent'   => $organization->parent,

          'organization' => $organization,
          'phone' => $organization->phone,
          'email' => $organization->email,
          'menu' => 'edit'
        ]);
    }


    public function inquiryPage(Organization $organization)
    {
      $inquiryHistory = OrganizationInquiry::where('organization_id', $organization->id)->with('inquiry')->get();
      return view('admin.organization.inquiries', [
        'inquiries'             => Inquiry::all(),
        'organization'          => $organization,
        'organizationInquiries' => $inquiryHistory,
        'menu'                  => 'inquiries'
      ]);
    }

  /**
   * Привязываем к огранизации новый запрос
   * @param Request $request
   * @param Organization $organization
   * @return \Illuminate\Http\RedirectResponse
   */
    public function addInquiry(Request $request, Organization $organization)
    {
      $inquiry = new OrganizationInquiry();
      $inquiry->organization()->associate($organization);
      $inquiry->inquiry()->associate($request->inquiry);
      $inquiry->note = $request->note;
      $inquiry->save();

      return redirect()->route('admin::organization_inquiries', $organization->id);
    }

  /**
   * Удаление запроса к организации
   * @param  Organization $organization
   * @param OrganizationInquiry $inquiry
   * @return \Illuminate\Http\RedirectResponse
   */
    public function removeInquiry(Organization $organization, OrganizationInquiry $inquiry)
    {
      $inquiry->delete();
      return redirect()->route('admin::organization_inquiries', $organization->id);
    }

    /**
     * Редактирование примечания к запросу
     * @param  Request             $request
     * @param  OrganizationInquiry $inquiry
     */
    public function editInquiry(Request $request, OrganizationInquiry $inquiry)
    {
      $inquiry->note = $request->note;
      $inquiry->save();
    }


    /**
     * Создание новой организации
     * @param Request $request
     * @param Organization $organization
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request, Organization $organization = null)
    {
      dd('wtf');
        $rules = [
          'city'      => 'numeric',
          'type'      => 'required|numeric',
          'fullName'  => 'required|string|max:512',
          'shortName' => 'string|max:255',
          'postCode'  => 'required|numeric',
          'address'   => 'required',
          'email.*'   => 'email'
        ];

        // для подразделений edrpou необязательное поле
        if(!empty($request->parent) || (!is_null($organization) && !empty($organization->parent_id))) {
            $rules['edrpou'] = 'numeric';
            $rules['status'] = 'numeric';
            $rules['opf']    = 'numeric';
        }
        else  {
            $rules['edrpou'] = 'required|numeric';
            $rules['status'] = 'required|numeric';
            $rules['opf']    = 'required|numeric';
        }

        $this->validate($request, $rules);

        $organization->fill($request->all());
        $organization->opf()->associate($request->opf);
        $organization->type()->associate($request->type);
        $organization->city()->associate($request->city);
        $organization->status()->associate($request->status);

        // если передали родительскую организацию, сохраняем связь
        if(!empty($request->parent)) {
            $organization->parent_id = $request->parent;
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

  /**
   * Удаление организации
   * @param Organization $organization
   * @return \Illuminate\Http\RedirectResponse
   * @throws \Exception
   */
    public function delete(Organization $organization)
    {
        // удаляем подразделения
        Organization::where('parent_id', $organization->id)->delete();
        // удаляем сотрудников
        Worker::where('organization_id', $organization->id)->delete();
        Snapshot::where('organization_id', $organization->id)->delete();
        // удаляем саму организацию
        $organization->delete();
        return redirect()->route('admin::organization');
    }
}
