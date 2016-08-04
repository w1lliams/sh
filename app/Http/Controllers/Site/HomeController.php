<?php

namespace App\Http\Controllers\Site;

use App\Feedback;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $this->getCounters();
        return view('site.home');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function help()
    {
        $this->getCounters();
        return view('site.help');
    }

  /**
   * Страница добавления отзыва
   * @param Request $request
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
   */
    public function feedbackPage(Request $request)
    {
      $this->getCounters();
      return view('site.feedback', [
        'workerId' => $request->w
      ]);
    }

  /**
   * Сохдраняем в БД отзыв
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   */
    public function saveFeedback(Request $request)
    {
      $this->validate($request, [
        'text' => 'required',
        'worker' => 'integer',
        'file' => 'file'
      ]);

      $feedback = new Feedback();
      $feedback->text = $request->text;
      $feedback->worker_id = $request->worker;

      if ($request->hasFile('file')) {
        $file = uniqid() . '.' . $request->file('file')->getClientOriginalExtension();
        $request->file('file')->move(public_path('upload/'), $file);
        $feedback->file = $file;
      }

      $feedback->save();
      $request->session()->flash('done', '1');
      return redirect()->route('feedback');
    }
}
