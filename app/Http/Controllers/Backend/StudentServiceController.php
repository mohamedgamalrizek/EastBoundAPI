<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\StudentService\StudentServiceInterface;
use App\Http\Requests\StudentService\StoreStudentServiceRequest;
use App\Http\Requests\StudentService\UpdateStudentServiceRequest;

class StudentServiceController extends BaseCrudController
{
    protected string $viewPath      = 'backend.student-service';
    protected string $redirectRoute = 'student-service.index';

    protected ?array $listAnalyticsConfig = ['model' => 'StudentService', 'group' => 'status', 'label' => 'Applications'];

    public function __construct(StudentServiceInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreStudentServiceRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateStudentServiceRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
