<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class OrganizationController extends Controller
{
    public function fetch()
    {

    }

    public function create()
    {
        return view('admin.organization.create');
    }
}