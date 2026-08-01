<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Admin;
use App\Model\AdminRole;
use App\Traits\UploadSizeHelperTrait;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;
use Symfony\Component\HttpFoundation\StreamedResponse;


class EmployeeController extends Controller
{
    use UploadSizeHelperTrait;

    public function __construct(
        private Admin     $admin,
        private AdminRole $admin_role
    ){}

    /**
     * @return Renderable
     */
    public function index(): Renderable
    {
        $roles = $this->admin_role->whereNotIn('id', [1])->get();
        return view('admin-views.employee.add-new', compact('roles'));
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
            'name' => 'required',
            'role_id' => 'required',
            'image' => 'required|image|max:'. $this->maxImageSizeKB .'|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'email' => 'required|email|unique:admins',
            'password' => 'required',
            'phone' => 'required',
            'identity_image' => 'required',
            'identity_type' => 'required|string|max:30',
            'identity_number' => 'required|string|max:50|regex:/^[A-Za-z0-9\-]+$/',
            'confirm_password' => 'same:password'
        ], [
            'name.required' => translate('Role name is required!'),
            'role_name.required' => translate('Role id is Required'),
            'email.required' => translate('Email id is Required'),
            'image.required' => translate('Image is Required'),

        ]);

        if ($request->role_id == 1) {
            Toastr::warning(translate('Access Denied!'));
            return back();
        }

        $identityImageNames = [];
        if (!empty($request->file('identity_image'))) {
            foreach ($request->identity_image as $img) {
                $identityImageNames[] = Helpers::upload('admin/', APPLICATION_IMAGE_FORMAT, $img);
            }
            $identityImage = json_encode($identityImageNames);
        } else {
            $identityImage = json_encode([]);
        }

        // Assigned attribute-by-attribute (not ->insert([...])) so Eloquent's
        // `encrypted` cast on `identity_number` (see Admin::$casts) actually
        // runs — the query builder's insert() bypasses model casts entirely.
        $admin = new Admin();
        $admin->f_name = $request->name;
        $admin->phone = $request->phone;
        $admin->email = $request->email;
        $admin->admin_role_id = $request->role_id;
        $admin->identity_number = $request->identity_number;
        $admin->identity_type = $request->identity_type;
        $admin->identity_image = $identityImage;
        $admin->password = bcrypt($request->password);
        $admin->status = 1;
        $admin->image = Helpers::upload('admin/', APPLICATION_IMAGE_FORMAT, $request->file('image'));
        $admin->save();

        Toastr::success(translate('Employee added successfully!'));
        return redirect()->route('admin.employee.list');
    }

    /**
     * @param Request $request
     * @return Renderable
     */
    function list(Request $request): Renderable
    {
        $search = $request['search'];
        $key = explode(' ', $request['search']);

        $query = $this->admin->with(['role'])
            ->when($search != null, function ($query) use ($key) {
                $query->whereNotIn('id', [1])->where(function ($query) use ($key) {
                    foreach ($key as $value) {
                        $query->where('f_name', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%");
                    }
                });
            }, function ($query) {
                $query->whereNotIn('id', [1]);
            });

        $employees = $query->paginate(Helpers::getPagination());

        return view('admin-views.employee.list', compact('employees', 'search'));
    }

    /**
     * @param $id
     * @return Renderable
     */
    public function edit($id): Renderable
    {
        $employee = $this->admin->where(['id' => $id])->first();
        $roles = $this->admin_role->whereNotIn('id', [1])->get();
        return view('admin-views.employee.edit', compact('roles', 'employee'));
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
            'name' => 'required',
            'image' => 'image|max:'. $this->maxImageSizeKB .'|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'role_id' => 'required',
            'email' => 'required|email|unique:admins,email,' . $id,
            'phone' => 'required',
            'identity_type' => 'required|string|max:30',
            'identity_number' => 'required|string|max:50|regex:/^[A-Za-z0-9\-]+$/',
        ], [
            'name.required' => translate('Role name is required!'),
        ]);

        if ($request->role_id == 1) {
            Toastr::warning(translate('Access Denied!'));
            return back();
        }

        $employee = $this->admin->find($id);
        $identityImage = $employee['identity_image'];

        if ($request['password'] == null) {
            $password = $employee['password'];
        } else {
            $request->validate([
                'confirm_password' => 'same:password'
            ]);
            if (strlen($request['password']) < 7) {
                Toastr::warning(translate('Password length must be 8 character.'));
                return back();
            }
            $password = bcrypt($request['password']);
        }

        if ($request->has('image')) {
            $employee['image'] = Helpers::update('admin/', $employee['image'], APPLICATION_IMAGE_FORMAT, $request->file('image'));
        }

        $identityImageNames = [];
        if (!empty($request->file('identity_image'))) {
            foreach ($request->identity_image as $img) {
                $identityImageNames[] = Helpers::upload('admin/', APPLICATION_IMAGE_FORMAT, $img);
            }
            $identityImage = json_encode($identityImageNames);
        }

        // Assigned attribute-by-attribute + save() (not ->update([...])) so
        // the `encrypted` cast on `identity_number` actually runs — see the
        // matching comment in store() above.
        $employee->f_name = $request->name;
        $employee->phone = $request->phone;
        $employee->email = $request->email;
        $employee->admin_role_id = $request->role_id;
        $employee->password = $password;
        $employee->identity_number = $request->identity_number;
        $employee->identity_type = $request->identity_type;
        $employee->identity_image = $identityImage;
        $employee->save();

        Toastr::success(translate('Employee updated successfully!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $employee = $this->admin->find($request->id);
        $employee->status = $request->status;
        $employee->save();

        Toastr::success(translate('Employee status updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        if ($request->id == 1) {
            Toastr::warning(translate('Master_Admin_can_not_be_deleted'));

        } else {
            $action = $this->admin->destroy($request->id);
            if ($action) {
                Toastr::success(translate('employee_deleted_successfully'));
            } else {
                Toastr::error(translate('employee_is_not_deleted'));
            }
        }
        return back();
    }

    /**
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function exportExcel(): StreamedResponse|string
    {
        $employees = $this->admin
            ->whereNotIn('id', [1])
            ->get(['id', 'f_name', 'l_name', 'email', 'admin_role_id', 'status']);

        return (new FastExcel($employees))->download('employees.xlsx');
    }
}
