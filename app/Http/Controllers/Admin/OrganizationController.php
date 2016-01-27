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
    public function fetch()
    {

    }

    public function createPage()
    {
        return view('admin.organization.create', [
            'statuses' => Status::all(),
            'opfs'     => Opf::all(),
            'types'    => Type::all(),
            'cities'   => City::all()
        ]);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'status'    => 'required|numeric',
            'city'      => 'numeric',
            'edrpou'    => 'required|numeric',
            'opf'       => 'required|numeric',
            'type'      => 'required|numeric',
            'fullName'  => 'required|string|max:512',
            'name'      => 'required|string|max:256',
            'postCode'  => 'required|numeric',
            'address'   => 'required',
            'email.*'   => 'required|email',
            'phone.*'   => 'required'
        ]);

        $oraganization = new Organization($request->all());
        $oraganization->opf()->associate($request->opf);
        $oraganization->type()->associate($request->type);
        $oraganization->city()->associate($request->city);
        $oraganization->status()->associate($request->status);
        $oraganization->save();

        exit('dgdf');
    }
}