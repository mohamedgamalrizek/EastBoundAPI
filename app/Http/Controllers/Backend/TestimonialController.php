<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Testimonial\TestimonialInterface;
use App\Http\Requests\Testimonial\StoreTestimonialRequest;
use App\Http\Requests\Testimonial\UpdateTestimonialRequest;

class TestimonialController extends BaseCrudController
{
    protected string $viewPath      = 'backend.cms.testimonial';
    protected string $redirectRoute = 'cms.testimonial.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Testimonial', 'group' => 'status', 'label' => 'Testimonials'];

    public function __construct(TestimonialInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreTestimonialRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateTestimonialRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
