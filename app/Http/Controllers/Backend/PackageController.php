<?php

namespace App\Http\Controllers\Backend;

use App\Models\PackageCategory;

use App\Http\Controllers\Controller;
use App\Repositories\Package\PackageInterface;
use App\Http\Requests\Package\StorePackageRequest;
use App\Http\Requests\Package\UpdatePackageRequest;

class PackageController extends Controller
{
    protected $repo;

    public function __construct(PackageInterface $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $packages = $this->repo->all();

        return view('backend.package.index', compact('packages'));
    }

    public function create()
    {
        $categories = $this->categoryOptions();

        return view('backend.package.create', compact('categories'));
    }

    public function store(StorePackageRequest $request)
    {
        $result = $this->repo->store($request);

        if ($result['status']) {
            return redirect()->route('package.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function edit($id)
    {
        $package    = $this->repo->get($id);
        $categories = $this->categoryOptions($package?->category_id);

        return view('backend.package.edit', compact('package', 'categories'));

    }

    /**
     * Active categories, plus the one already on the package so editing an old
     * package whose category was deactivated does not blank the field.
     */
    private function categoryOptions($keepId = null)
    {
        return PackageCategory::where('status', 'active')
            ->when($keepId, fn ($q) => $q->orWhere('id', $keepId))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function update(UpdatePackageRequest $request, $id)
    {
        $result = $this->repo->update($request, $id);

        if ($result['status']) {
            return redirect()->route('package.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function delete($id)
    {
        $result = $this->repo->delete($id);

        return response()->json($result, $result['status_code']);
    }
}
