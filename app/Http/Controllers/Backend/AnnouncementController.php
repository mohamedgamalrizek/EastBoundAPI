<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Announcement\AnnouncementInterface;
use App\Http\Requests\Announcement\StoreAnnouncementRequest;
use App\Http\Requests\Announcement\UpdateAnnouncementRequest;

class AnnouncementController extends BaseCrudController
{
    protected string $viewPath      = 'backend.support.announcement';
    protected string $redirectRoute = 'support.announcement.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Announcement', 'group' => 'status', 'label' => 'Announcements'];

    public function __construct(AnnouncementInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreAnnouncementRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateAnnouncementRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
