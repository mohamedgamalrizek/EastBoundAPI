<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Slider\SliderInterface;
use App\Http\Requests\Slider\StoreSliderRequest;
use App\Http\Requests\Slider\UpdateSliderRequest;

class SliderController extends BaseCrudController
{
    protected string $viewPath      = 'backend.cms.slider';
    protected string $redirectRoute = 'cms.slider.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Slider', 'group' => 'status', 'label' => 'Sliders'];

    public function __construct(SliderInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreSliderRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateSliderRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
