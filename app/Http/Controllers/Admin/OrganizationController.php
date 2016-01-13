<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Status;
use Illuminate\Support\Facades\App;

class OrganizationController extends Controller
{
    public function fetch()
    {

    }

    public function create()
    {
        return view('admin.organization.create', [
            'statuses' => Status::all()
        ]);
    }
}