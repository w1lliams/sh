<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Status;
use App\Opf;
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
            'opfs' => Opf::all()
        ]);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'status'    => 'required|numeric',
            'edrpou'    => 'required|numeric',
            'opf'       => 'required|numeric',
            'fullName'  => 'required|string|max:512',
            'name'      => 'required|string|max:256',
            'email.*'   => 'required|email',
            'phone.*'   => 'required'
        ]);

        exit('dgdf');
    }
}