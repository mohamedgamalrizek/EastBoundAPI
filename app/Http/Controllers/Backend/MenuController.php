<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Menu\MenuInterface;
use App\Http\Requests\Menu\StoreMenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;

class MenuController extends BaseCrudController
{
    protected string $viewPath      = 'backend.cms.menu';
    protected string $redirectRoute = 'cms.menu.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Menu', 'group' => 'status', 'label' => 'Menus'];

    public function __construct(MenuInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreMenuRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateMenuRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
