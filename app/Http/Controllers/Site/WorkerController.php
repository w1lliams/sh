<?php

namespace App\Http\Controllers\Site;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Worker;

class WorkerController extends Controller
{
    /**
     * @param Worker $worker
     * @return mixed
     */
    public function workerPage(Worker $worker)
    {
        $this->getCounters();
        return view('site.worker', [
          'worker' => $worker
        ]);
    }
}
