<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\KbArticle\KbArticleInterface;
use App\Http\Requests\KbArticle\StoreKbArticleRequest;
use App\Http\Requests\KbArticle\UpdateKbArticleRequest;

class KbArticleController extends BaseCrudController
{
    protected string $viewPath      = 'backend.support.kb';
    protected string $redirectRoute = 'support.kb.index';

    protected ?array $listAnalyticsConfig = ['model' => 'KbArticle', 'group' => 'category', 'label' => 'Articles'];

    public function __construct(KbArticleInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreKbArticleRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateKbArticleRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
