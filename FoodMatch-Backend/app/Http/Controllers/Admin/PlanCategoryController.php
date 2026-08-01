<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\PlanCategory;
use App\Traits\UploadSizeHelperTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanCategoryController extends Controller
{
    use UploadSizeHelperTrait;

    public function __construct(private PlanCategory $planCategory) {}

    /**
     * Show create form + paginated list.
     */
    public function index(Request $request): Renderable
    {
        $search     = $request->search;
        $queryParam = ['search' => $search];

        $categories = $this->planCategory
            ->when($search, function ($query) use ($search) {
                $keywords = explode(' ', $search);
                foreach ($keywords as $keyword) {
                    $query->orWhere('name', 'LIKE', "%$keyword%")
                          ->orWhere('id',   'LIKE', "%$keyword%");
                }
            })
            ->orderBy('priority')
            ->latest()
            ->paginate(Helpers::getPagination())
            ->appends($queryParam);

        return view('admin-views.plan-category.index', compact('categories', 'search'));
    }

    /**
     * Store a new category.
     */
    public function store(Request $request): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'name'  => 'required|max:255',
            'image' => 'nullable|image|max:' . $this->maxImageSizeKB . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
        ]);

        $category            = $this->planCategory;
        $category->name      = $request->name;
        $category->priority  = $request->priority ?? 0;
        $category->is_active = $request->has('is_active') ? 1 : 0;

        if ($request->hasFile('image')) {
            $category->image = Helpers::upload('plan-category/', APPLICATION_IMAGE_FORMAT, $request->file('image'));
        }

        $category->save();

        Toastr::success(translate('Plan category added successfully!'));
        return redirect()->route('admin.plan-category.index');
    }

    /**
     * Show edit form.
     */
    public function edit(int $id): Renderable
    {
        $category = $this->planCategory->findOrFail($id);

        return view('admin-views.plan-category.edit', compact('category'));
    }

    /**
     * Update existing category.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'name'  => 'required|max:255',
            'image' => 'nullable|image|max:' . $this->maxImageSizeKB . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
        ]);

        $category            = $this->planCategory->findOrFail($id);
        $category->name      = $request->name;
        $category->priority  = $request->priority ?? $category->priority;
        $category->is_active = $request->has('is_active') ? 1 : 0;

        if ($request->hasFile('image')) {
            $category->image = Helpers::update('plan-category/', $category->image, APPLICATION_IMAGE_FORMAT, $request->file('image'));
        }

        $category->save();

        Toastr::success(translate('Plan category updated successfully!'));
        return redirect()->route('admin.plan-category.index');
    }

    /**
     * Toggle active status.
     */
    public function status(Request $request, int $id, int $status): RedirectResponse
    {
        $category            = $this->planCategory->findOrFail($id);
        $category->is_active = $status;
        $category->save();

        Toastr::success(translate('Plan category status updated!'));
        return back();
    }

    /**
     * Delete category.
     */
    public function delete(Request $request): RedirectResponse
    {
        $category = $this->planCategory->findOrFail($request->id);

        if ($category->image) {
            Helpers::delete('plan-category/' . $category->image);
        }

        $category->delete();

        Toastr::success(translate('Plan category removed!'));
        return back();
    }
}
