<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Review\UpdateReviewRequest;
use App\Repositories\Review\ReviewInterface;

class ReviewController extends BaseCrudController
{
    protected string $viewPath      = 'backend.review';
    protected string $redirectRoute = 'review.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Review', 'group' => 'status', 'label' => 'Reviews'];

    public function __construct(ReviewInterface $repo)
    {
        $this->repo = $repo;
    }

    public function update(UpdateReviewRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }

    /** One-click approve / reject from the list. */
    public function approve($id)
    {
        return $this->persist($this->repo->setStatus($id, \App\Models\Review::APPROVED));
    }

    public function reject($id)
    {
        return $this->persist($this->repo->setStatus($id, \App\Models\Review::REJECTED));
    }
}
