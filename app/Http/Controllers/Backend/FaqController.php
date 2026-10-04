<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Faq\FaqInterface;
use App\Http\Requests\Faq\StoreFaqRequest;
use App\Http\Requests\Faq\UpdateFaqRequest;

class FaqController extends BaseCrudController
{
    protected string $viewPath      = 'backend.cms.faq';
    protected string $redirectRoute = 'cms.faq.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Faq', 'group' => 'category', 'label' => 'FAQs'];

    public function __construct(FaqInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreFaqRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateFaqRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
