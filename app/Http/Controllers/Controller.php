<?php

namespace App\Http\Controllers;

use App\Organization;
use App\Worker;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\View;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Счетчики огранизаций и работников
     */
    protected function getCounters()
    {
        View::share('organizationCount', Organization::where('snapshot_id', '>', 0)->count());
        View::share('workersCount', Worker::count());
    }
}
