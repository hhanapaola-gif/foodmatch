<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Branch;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;

class LocationSettingsController extends Controller
{
    public function __construct(
        private Branch $branch
    ){}

    /**
     * @return Renderable
     */
    public function locationIndex(): Renderable
    {
        return view('admin-views.business-settings.location-index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function locationSetup(Request $request): RedirectResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'coverage' => 'nullable|numeric|min:0',
        ]);

        $this->branch->updateOrInsert(['id' => 1], [
            'longitude' => $request['longitude'],
            'latitude' => $request['latitude'],
            'coverage' => $request['coverage'] ? $request['coverage'] : 0,
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }
}
