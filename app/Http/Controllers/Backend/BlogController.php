<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Blog\BlogInterface;
use App\Http\Requests\Blog\StoreBlogRequest;
use App\Http\Requests\Blog\UpdateBlogRequest;

class BlogController extends BaseCrudController
{
    protected string $viewPath      = 'backend.cms.blog';
    protected string $redirectRoute = 'cms.blog.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Blog', 'group' => 'status', 'label' => 'Posts'];

    public function __construct(BlogInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreBlogRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateBlogRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
