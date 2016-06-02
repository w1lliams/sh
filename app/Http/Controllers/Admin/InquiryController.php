<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Inquiry;
use App\OrganizationInquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    /**
     * Главная страница админки статусов запросов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function main()
    {
        return view('admin.inquiry.main', [
          'inquiries' => Inquiry::all()
        ]);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
          'name' => 'required|max:255'
        ]);

        Inquiry::create($request->all());
        return redirect()->route('admin::inquiry');
    }

    /**
     * @param Inquiry $inquiry
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function delete(Inquiry $inquiry)
    {
        $exists = OrganizationInquiry::where('inquiry_id', $inquiry->id)->pluck('organization_id');
        if(count($exists) == 0) {
            $inquiry->delete();
            return redirect()->route('admin::inquiry');
        } else {
            echo('in organizations: '. implode(', ', array_unique($exists->all())));
        }
    }
}
