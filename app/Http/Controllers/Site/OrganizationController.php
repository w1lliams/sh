<?php

namespace App\Http\Controllers\Site;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Organization;
use App\Snapshot;
use App\Worker;

class OrganizationController extends Controller
{
    /**
     * @param Organization $organization
     */
    public function organizationPage(Organization $organization)
    {
        $workers  = null;
        $snapshot = Snapshot::where('organization_id', $organization->id)->orderBy('id', 'desc')->first();
        if(!is_null($snapshot)) {
          $workers = Worker::where('snapshot_id', $snapshot->id)->get();
          $workers = Worker::structure($workers);
        }

        return view('site.organization', [
          'organization' => $organization,
          'snapshot' => $snapshot,
          'workers' => $workers,
          'fixed' => true
        ]);
    }
}
