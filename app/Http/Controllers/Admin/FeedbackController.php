<?php

namespace App\Http\Controllers\Admin;

use App\Feedback;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
  /**
   * Главная страница админки городов
   *
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
   */
  public function main()
  {
    return view('admin.feedback.main', [
      'feedbacks' => Feedback::with('worker')->get()
    ]);
  }

  /**
   * Удаление города
   *
   * @param Feedback $feedback
   * @return \Illuminate\Http\RedirectResponse
   */
  public function delete(Feedback $feedback)
  {
    $feedback->delete();
    return redirect()->route('admin::feedback');
  }
}