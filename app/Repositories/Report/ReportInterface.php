<?php

namespace App\Repositories\Report;

interface ReportInterface
{
    public function sales(array $filters = []);

    public function visa(array $filters = []);

    public function package(array $filters = []);

    public function flight(array $filters = []);

    public function hotel(array $filters = []);

    public function agent(array $filters = []);

    public function customer(array $filters = []);

    public function financial(array $filters = []);

    public function custom(array $filters = []);

    public function export(string $type, array $filters = []): array;
}
