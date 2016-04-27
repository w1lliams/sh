<?php

namespace App\Http\Controllers\Site;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Worker;

class WorkerController extends Controller
{
    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function workerPage(Worker $worker)
    {
        return view('site.worker', [
          'worker' => $worker
        ]);
    }
}
