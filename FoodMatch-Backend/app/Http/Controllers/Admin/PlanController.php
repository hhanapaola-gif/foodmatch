<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\Plan;
use App\Model\PlanCategory;
use App\Model\PlanDay;
use App\Traits\UploadSizeHelperTrait;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlanController extends Controller
{
    use UploadSizeHelperTrait;

    public function __construct(
        private Plan         $plan,
        private PlanCategory $planCategory,
        private PlanDay      $planDay,
        private Branch       $branch
    ) {}

    /**
     * List all plans.
     */
    public function index(Request $request): Renderable
    {
        $search     = $request->search;
        $queryParam = ['search' => $search];

        $plans = $this->plan
            ->with('category')
            ->when($search, function ($query) use ($search) {
                $keywords = explode(' ', $search);
                foreach ($keywords as $keyword) {
                    $query->orWhere('title', 'LIKE', "%$keyword%")
                          ->orWhere('id',    'LIKE', "%$keyword%");
                }
            })
            ->latest()
            ->paginate(Helpers::getPagination())
            ->appends($queryParam);

        return view('admin-views.plan.list', compact('plans', 'search'));
    }

    /**
     * Show the create form.
     */
    public function create(): Renderable
    {
        $categories = $this->planCategory->orderBy('name')->get();
        $branches   = $this->branch->orderBy('name')->get();

        return view('admin-views.plan.create', compact('categories', 'branches'));
    }

    /**
     * Store a new plan.
     */
    public function store(Request $request): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $rules = [
            'title'            => 'required|max:255',
            'category_id'      => 'required|integer',
            'type'             => 'required|in:weekly,monthly',
            'meals_per_day'    => 'required|integer|min:1',
            'breakfast_price'  => 'required|numeric|min:0',
            'lunch_price'      => 'required|numeric|min:0',
            'dinner_price'     => 'required|numeric|min:0',
            'service_days'     => 'nullable|array',
            'service_days.*'   => 'in:mon,tue,wed,thu,fri,sat,sun',
            'image'            => 'nullable|image|max:' . $this->maxImageSizeKB . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'menu_pdf'         => 'nullable|file|mimes:pdf|max:10240',
        ];
        if ($request->type === 'monthly') {
            // For monthly plans the selected service days drive the total: days × 4 weeks
            $rules['service_days'] = 'required|array|min:1';
        } else {
            $rules['min_days'] = 'required|integer|min:1';
        }
        $request->validate($rules);

        $serviceDays = $request->service_days ?? [];
        $minDays     = $request->type === 'monthly'
            ? count($serviceDays) * 4
            : (int) $request->min_days;

        $plan                  = $this->plan;
        $plan->title           = $request->title;
        $plan->description     = $request->description;
        $plan->category_id     = $request->category_id;
        $plan->type            = $request->type;
        $plan->min_days        = $minDays;
        $plan->meals_per_day   = $request->meals_per_day;
        $plan->breakfast_price = $request->breakfast_price;
        $plan->lunch_price     = $request->lunch_price;
        $plan->dinner_price    = $request->dinner_price;
        $plan->is_active       = $request->has('is_active') ? 1 : 0;
        $plan->restaurant_id   = $request->restaurant_id ?: null;

        if ($request->hasFile('image')) {
            $plan->image = Helpers::upload('plan/', APPLICATION_IMAGE_FORMAT, $request->file('image'));
        }

        if ($request->hasFile('menu_pdf')) {
            $plan->menu_pdf = $this->storePdf($request->file('menu_pdf'));
        }

        $plan->save();

        // Save service days (string abbreviations → integer day_of_week)
        $dayMap = ['mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 7];
        foreach ($serviceDays as $abbr) {
            if (isset($dayMap[$abbr])) {
                $this->planDay->create([
                    'plan_id'     => $plan->id,
                    'day_of_week' => $dayMap[$abbr],
                ]);
            }
        }

        Toastr::success(translate('Plan added successfully!'));
        return redirect('admin/plan/list');
    }

    /**
     * Show the edit form.
     */
    public function edit(int $id): Renderable
    {
        $plan       = $this->plan->with('days')->findOrFail($id);
        $categories = $this->planCategory->orderBy('name')->get();
        $branches   = $this->branch->orderBy('name')->get();
        $planDays   = $plan->days->pluck('day_of_week')->toArray();

        return view('admin-views.plan.edit', compact('plan', 'categories', 'branches', 'planDays'));
    }

    /**
     * Update an existing plan.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $rules = [
            'title'            => 'required|max:255',
            'category_id'      => 'required|integer',
            'type'             => 'required|in:weekly,monthly',
            'meals_per_day'    => 'required|integer|min:1',
            'breakfast_price'  => 'required|numeric|min:0',
            'lunch_price'      => 'required|numeric|min:0',
            'dinner_price'     => 'required|numeric|min:0',
            'service_days'     => 'nullable|array',
            'service_days.*'   => 'in:mon,tue,wed,thu,fri,sat,sun',
            'image'            => 'nullable|image|max:' . $this->maxImageSizeKB . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'menu_pdf'         => 'nullable|file|mimes:pdf|max:10240',
        ];
        if ($request->type === 'monthly') {
            $rules['service_days'] = 'required|array|min:1';
        } else {
            $rules['min_days'] = 'required|integer|min:1';
        }
        $request->validate($rules);

        $serviceDays = $request->service_days ?? [];
        $minDays     = $request->type === 'monthly'
            ? count($serviceDays) * 4
            : (int) $request->min_days;

        $plan                  = $this->plan->findOrFail($id);
        $plan->title           = $request->title;
        $plan->description     = $request->description;
        $plan->category_id     = $request->category_id;
        $plan->type            = $request->type;
        $plan->min_days        = $minDays;
        $plan->meals_per_day   = $request->meals_per_day;
        $plan->breakfast_price = $request->breakfast_price;
        $plan->lunch_price     = $request->lunch_price;
        $plan->dinner_price    = $request->dinner_price;
        $plan->is_active       = $request->has('is_active') ? 1 : 0;
        $plan->restaurant_id   = $request->restaurant_id ?: null;

        if ($request->hasFile('image')) {
            $plan->image = Helpers::update('plan/', $plan->image, APPLICATION_IMAGE_FORMAT, $request->file('image'));
        }

        if ($request->hasFile('menu_pdf')) {
            if ($plan->menu_pdf) {
                Storage::disk('public')->delete('menus/' . basename($plan->menu_pdf));
            }
            $plan->menu_pdf = $this->storePdf($request->file('menu_pdf'));
        }

        $plan->save();

        // Sync service days (string abbreviations → integer day_of_week)
        $dayMap = ['mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 7];
        $this->planDay->where('plan_id', $plan->id)->delete();
        foreach ($serviceDays as $abbr) {
            if (isset($dayMap[$abbr])) {
                $this->planDay->create([
                    'plan_id'     => $plan->id,
                    'day_of_week' => $dayMap[$abbr],
                ]);
            }
        }

        Toastr::success(translate('Plan updated successfully!'));
        return redirect('admin/plan/list');
    }

    /**
     * Toggle plan status.
     */
    public function status(Request $request, int $id): RedirectResponse
    {
        $plan            = $this->plan->findOrFail($id);
        $plan->is_active = $request->status;
        $plan->save();

        Toastr::success(translate('Plan status updated!'));
        return back();
    }

    /**
     * Store a PDF in storage/menus/ and return the web-relative path
     * (e.g. "storage/menus/2024-01-01-abc.pdf") so the API can return it directly
     * and clients just prepend the base URL to build a working link.
     *
     * Requires: php artisan storage:link (public/storage → storage/app/public)
     */
    private function storePdf(\Illuminate\Http\UploadedFile $file): string
    {
        $filename = Carbon::now()->format('Y-m-d') . '-' . uniqid() . '.pdf';
        Storage::disk('public')->putFileAs('menus', $file, $filename);
        return 'storage/menus/' . $filename;
    }

    /**
     * Delete a plan.
     */
    public function delete(Request $request): RedirectResponse
    {
        $plan = $this->plan->findOrFail($request->id);

        if ($plan->image) {
            Helpers::delete('plan/' . $plan->image);
        }
        if ($plan->menu_pdf) {
            Storage::disk('public')->delete('menus/' . basename($plan->menu_pdf));
        }

        $this->planDay->where('plan_id', $plan->id)->delete();
        $plan->delete();

        Toastr::success(translate('Plan removed!'));
        return back();
    }
}
