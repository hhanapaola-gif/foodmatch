<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Model\Plan;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlanController extends Controller
{
    public function __construct(private Plan $plan) {}

    /**
     * List the authenticated branch's own plans.
     */
    public function list(): Renderable
    {
        $plans = $this->plan
            ->with('category')
            ->where('restaurant_id', auth('branch')->id())
            ->latest()
            ->paginate(20);

        return view('branch-views.plan.list', compact('plans'));
    }

    /**
     * Show the menu-PDF upload form for one of the branch's own plans.
     */
    public function menu(int $id): Renderable
    {
        $plan = $this->plan
            ->where('restaurant_id', auth('branch')->id())
            ->findOrFail($id);

        return view('branch-views.plan.menu', compact('plan'));
    }

    /**
     * Store/replace the menu PDF for one of the branch's own plans.
     */
    public function menuUpdate(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'menu_pdf' => 'required|file|mimes:pdf|max:10240',
        ]);

        $plan = $this->plan
            ->where('restaurant_id', auth('branch')->id())
            ->findOrFail($id);

        if ($plan->menu_pdf) {
            Storage::disk('public')->delete('menus/' . basename($plan->menu_pdf));
        }

        $filename = Carbon::now()->format('Y-m-d') . '-' . uniqid() . '.pdf';
        Storage::disk('public')->putFileAs('menus', $request->file('menu_pdf'), $filename);
        $plan->menu_pdf = 'storage/menus/' . $filename;
        $plan->save();

        Toastr::success(translate('Menu PDF uploaded successfully!'));
        return back();
    }
}
