<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Receipt\ReceiptInterface;
use App\Http\Requests\Receipt\StoreReceiptRequest;
use App\Http\Requests\Receipt\UpdateReceiptRequest;

class ReceiptController extends BaseCrudController
{
    protected string $viewPath      = 'backend.accounting.receipt';
    protected string $redirectRoute = 'acc.receipt.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Receipt', 'group' => null, 'sum' => 'amount', 'label' => 'Receipts', 'sumLabel' => 'Total Received'];

    public function __construct(ReceiptInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreReceiptRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateReceiptRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
