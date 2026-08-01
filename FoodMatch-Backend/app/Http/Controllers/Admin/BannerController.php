<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Banner;
use App\Model\Category;
use App\Model\Plan;
use App\Model\Product;
use App\Traits\UploadSizeHelperTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use UploadSizeHelperTrait;
    public function __construct(
        private Banner   $banner,
        private Product  $product,
        private Category $category,
        private Plan     $plan,
    )
    {}

    /**
     * @return Renderable
     */
    function index(): Renderable
    {
        $products   = $this->product->orderBy('name')->get();
        $categories = $this->category->where(['parent_id' => 0])->orderBy('name')->get();
        $plans      = $this->plan->active()->orderBy('title')->get();

        return view('admin-views.banner.index', compact('products', 'categories', 'plans'));
    }

    /**
     * @param Request $request
     * @return Renderable
     */
    function list(Request $request): Renderable
    {
        $search = $request->search;
        $queryParam = ['search' => $search];

        $banners = $this->banner
            ->when($search, function ($query) use ($search, $queryParam) {
                $keywords = explode(' ', $search);
                foreach ($keywords as $keyword) {
                    $query->orWhere('title', 'LIKE', "%$keyword%")
                        ->orwhere('id', 'LIKE', "%$keyword%");
                }
            })
            ->latest()
            ->paginate(Helpers::getPagination())
            ->appends($queryParam);

        return view('admin-views.banner.list', compact('banners', 'search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'title' => 'required|max:255',
            'image' => 'required|image|max:'. $this->maxImageSizeKB .'|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'item_type' => 'required|in:product,category,plan',
            'product_id' => 'required_if:item_type,product|exists:products,id',
            'category_id' => 'required_if:item_type,category|exists:categories,id',
            'plan_id' => 'required_if:item_type,plan|exists:plans,id',
        ], [
            'title.max' => translate('Title is too long'),
        ]);

        $banner = $this->banner;
        $banner->title = $request->title;

        if ($request['item_type'] == 'product') {
            $banner->product_id  = $request->product_id;
        } elseif ($request['item_type'] == 'category') {
            $banner->category_id = $request->category_id;
        } elseif ($request['item_type'] == 'plan') {
            $banner->plan_id     = $request->plan_id;
        }

        try {
            $banner->image = Helpers::upload('banner/', APPLICATION_IMAGE_FORMAT, $request->file('image'));
            $banner->save();
        } catch (\Exception $e) {
            Toastr::error(translate('Failed to save banner: ') . $e->getMessage());
            return back()->withInput();
        }

        Toastr::success(translate('Banner added successfully!'));
        return redirect()->route('admin.banner.list');
    }

    /**
     * @param $id
     * @return Renderable
     */
    public function edit($id): Renderable
    {
        $products   = $this->product->orderBy('name')->get();
        $banner     = $this->banner->find($id);
        $categories = $this->category->where(['parent_id' => 0])->orderBy('name')->get();
        $plans      = $this->plan->active()->orderBy('title')->get();

        return view('admin-views.banner.edit', compact('banner', 'products', 'categories', 'plans'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $banner = $this->banner->find($request->id);
        $banner->status = $request->status;
        $banner->save();

        Toastr::success(translate('Banner status updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'title' => 'required|max:255',
            'item_type' => 'required|in:product,category,plan',
            'product_id' => 'required_if:item_type,product|exists:products,id',
            'category_id' => 'required_if:item_type,category|exists:categories,id',
            'plan_id' => 'required_if:item_type,plan|exists:plans,id',
            'image' => 'image|max:'. $this->maxImageSizeKB .'|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
        ], [
            'title.max' => translate('Title is too long!'),
        ]);

        $banner = $this->banner->find($id);
        $banner->title = $request->title;

        if ($request['item_type'] == 'product') {
            $banner->product_id  = $request->product_id;
            $banner->category_id = null;
            $banner->plan_id     = null;
        } elseif ($request['item_type'] == 'category') {
            $banner->product_id  = null;
            $banner->category_id = $request->category_id;
            $banner->plan_id     = null;
        } elseif ($request['item_type'] == 'plan') {
            $banner->product_id  = null;
            $banner->category_id = null;
            $banner->plan_id     = $request->plan_id;
        }

        try {
            $banner->image = $request->has('image') ? Helpers::update('banner/', $banner->image, APPLICATION_IMAGE_FORMAT, $request->file('image')) : $banner->image;
            $banner->save();
        } catch (\Exception $e) {
            Toastr::error(translate('Failed to update banner: ') . $e->getMessage());
            return back()->withInput();
        }

        Toastr::success(translate('Banner updated successfully!'));
        return redirect()->route('admin.banner.list');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $banner = $this->banner->find($request->id);
        Helpers::delete('banner/' . $banner['image']);
        $banner->delete();

        Toastr::success(translate('Banner removed!'));
        return back();
    }
}
