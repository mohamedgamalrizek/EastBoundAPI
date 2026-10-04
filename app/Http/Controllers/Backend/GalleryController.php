<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Gallery\GalleryInterface;
use App\Http\Requests\Gallery\StoreGalleryRequest;
use App\Http\Requests\Gallery\UpdateGalleryRequest;

class GalleryController extends BaseCrudController
{
    protected string $viewPath      = 'backend.cms.gallery';
    protected string $redirectRoute = 'cms.gallery.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Gallery', 'group' => 'category', 'label' => 'Items'];

    public function __construct(GalleryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreGalleryRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateGalleryRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
