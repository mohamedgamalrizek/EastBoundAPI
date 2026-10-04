<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Cms\CmsInterface;
use App\Http\Requests\Cms\StoreCmsRequest;
use App\Http\Requests\Cms\UpdateCmsRequest;
use App\Repositories\Settings\SettingsInterface;
use Illuminate\Http\Request;

class CmsController extends BaseCrudController
{
    protected string $viewPath      = 'backend.cms';
    protected string $redirectRoute = 'cms.index';
    protected SettingsInterface $settingsRepo;

    public function __construct(CmsInterface $repo, SettingsInterface $settingsRepo)
    {
        $this->repo = $repo;
        $this->settingsRepo = $settingsRepo;
    }

    public function store(StoreCmsRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateCmsRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    public function seo()
    {
        return view('backend.cms.seo', $this->repo->seo());
    }

    public function seoUpdate(Request $request)
    {
        $request->validate([
            'seo_meta_title'       => ['required', 'string', 'max:255'],
            'seo_meta_description' => ['required', 'string', 'max:500'],
            'seo_meta_keywords'    => ['nullable', 'string', 'max:500'],
            'google_analytics_id'  => ['nullable', 'string', 'max:50'],
            'og_image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $result = $this->settingsRepo->UpdateSettings($request);

        if ($result['status']) {
            return redirect()->route('cms.seo')->with('success', $result['message']);
        }

        return back()->with('danger', $result['message'])->withInput();
    }
}
